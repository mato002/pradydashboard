<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Jobs\Billing\SendFinancialDocumentEmailJob;
use App\Models\GeneratedDocument;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDeliveryService
{
    public function __construct(
        private readonly DocumentFinalizer $finalizer,
        private readonly InvoiceEmailDelivery $emailDelivery,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function defaultRecipientEmail(TenantInvoice $invoice): ?string
    {
        $email = trim((string) ($invoice->tenant?->billing_email ?? $invoice->manual_client_email ?? ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function ensureDocument(TenantInvoice $invoice, bool $forceFinalize = false): GeneratedDocument
    {
        $invoice->loadMissing(['lineItems', 'tenant', 'projectSubscription.project']);

        if ($forceFinalize || ! $invoice->finalized_at) {
            return $this->finalizer->finalize($invoice, null, null, $forceFinalize);
        }

        return $invoice->generatedDocuments()->latest('id')->first()
            ?? $this->finalizer->finalize($invoice);
    }

    public function ensurePdf(TenantInvoice $invoice, ?GeneratedDocument $document = null): GeneratedDocument
    {
        $document = $document ?? $this->ensureDocument($invoice);

        return $this->finalizer->ensurePdf($document, $invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']));
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function sendEmail(TenantInvoice $invoice, ?string $recipientOverride = null, bool $resend = false): array
    {
        $document = $this->ensurePdf($invoice);
        $recipient = trim((string) ($recipientOverride ?? ''));
        if ($recipient === '') {
            $recipient = $this->defaultRecipientEmail($invoice) ?? '';
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $error = __('No valid recipient email. Set tenant billing email or manual client email.');
            $this->recordFailure($invoice, $document, $error);

            return ['success' => false, 'message' => $error];
        }

        $wasSent = in_array($invoice->delivery_status, ['sent', 'resent'], true);
        $isResend = $resend || $wasSent;

        if (config('queue.default') !== 'sync') {
            SendFinancialDocumentEmailJob::dispatch(
                $invoice->id,
                $document->id,
                $recipient,
                $isResend,
            );

            return [
                'success' => true,
                'message' => __('Email queued for delivery to :email.', ['email' => $recipient]),
            ];
        }

        return $this->emailDelivery->send($invoice, $document, $recipient, $isResend);
    }

    public function downloadPdfResponse(TenantInvoice $invoice, ?int $templateId = null): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        if ($templateId) {
            $template = \App\Models\DocumentTemplate::query()
                ->whereKey($templateId)
                ->where('active', true)
                ->where('type', $invoice->document_type ?? 'invoice')
                ->first();
            if ($template) {
                $this->finalizer->regenerate($invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']), $template);
            }
        }

        $document = $this->ensurePdf($invoice->fresh());

        if (! $document->pdf_path || ! Storage::disk('local')->exists($document->pdf_path)) {
            return back()->with('error', __('PDF not available. Install dompdf/dompdf and try finalizing again.'));
        }

        $this->activityLogger->log(
            'document.downloaded',
            ActivityLogCategory::BILLING,
            __(':type :number PDF downloaded', [
                'type' => $invoice->document_type ?? 'invoice',
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            null,
            ['generated_document_id' => $document->id],
        );

        return Storage::disk('local')->download(
            $document->pdf_path,
            $invoice->invoice_number.'.pdf',
        );
    }

    public function markSent(TenantInvoice $invoice, bool $sendEmail = false): TenantInvoice
    {
        if (! in_array($invoice->status, ['draft'], true)) {
            throw new \InvalidArgumentException(__('Only draft documents can be marked as sent.'));
        }

        $old = ['status' => $invoice->status];
        $invoice->update([
            'status' => 'sent',
            'issued_at' => $invoice->issued_at ?? now(),
            'issue_date' => $invoice->issue_date ?? now()->toDateString(),
        ]);

        $document = $this->ensurePdf($invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']));

        if ($sendEmail && \App\Models\BillingAutomationRule::platform()->auto_send_invoices) {
            $this->sendEmail($invoice->fresh(), null, false);
        }

        $this->activityLogger->log(
            'document.marked_sent',
            ActivityLogCategory::BILLING,
            __(':type :number marked as sent', [
                'type' => $invoice->document_type ?? 'invoice',
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            $old,
            ['status' => 'sent', 'generated_document_id' => $document->id],
        );

        return $invoice->fresh();
    }

    public function recordFailure(TenantInvoice $invoice, GeneratedDocument $document, string $error): void
    {
        $invoice->update([
            'delivery_status' => 'failed',
            'last_delivery_error' => $error,
        ]);
        $document->update([
            'delivery_status' => 'failed',
            'last_delivery_error' => $error,
        ]);

        $this->activityLogger->log(
            'document.email_failed',
            ActivityLogCategory::BILLING,
            __(':type :number email failed: :error', [
                'type' => $invoice->document_type ?? 'invoice',
                'number' => $invoice->invoice_number,
                'error' => $error,
            ]),
            $invoice,
            null,
            ['error' => $error],
        );
    }
}
