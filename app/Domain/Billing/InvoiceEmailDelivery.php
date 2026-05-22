<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Mail\FinancialDocumentMail;
use App\Models\GeneratedDocument;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceEmailDelivery
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @return array{success: bool, message: string}
     */
    public function send(
        TenantInvoice $invoice,
        GeneratedDocument $document,
        string $recipientEmail,
        bool $isResend = false,
    ): array {
        if (! $document->pdf_path || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($document->pdf_path)) {
            $error = __('PDF attachment is missing. Finalize the document and try again.');
            $this->recordFailure($invoice, $document, $error);

            return ['success' => false, 'message' => $error];
        }

        try {
            Mail::to($recipientEmail)->send(new FinancialDocumentMail($invoice, $document->pdf_path, $isResend));

            $now = now();
            $status = $isResend ? 'resent' : 'sent';

            $invoice->update([
                'email_delivered_at' => $now,
                'email_sent_at' => $now,
                'delivery_status' => $status,
                'last_delivery_error' => null,
            ]);
            $document->update([
                'email_sent_at' => $now,
                'delivery_status' => $status,
                'last_delivery_error' => null,
            ]);

            $action = $isResend ? 'document.resent' : 'document.emailed';
            $this->activityLogger->log(
                $action,
                ActivityLogCategory::BILLING,
                $isResend
                    ? __(':type :number resent to :email', [
                        'type' => $invoice->document_type ?? 'invoice',
                        'number' => $invoice->invoice_number,
                        'email' => $recipientEmail,
                    ])
                    : __(':type :number emailed to :email', [
                        'type' => $invoice->document_type ?? 'invoice',
                        'number' => $invoice->invoice_number,
                        'email' => $recipientEmail,
                    ]),
                $invoice,
                null,
                ['email' => $recipientEmail, 'generated_document_id' => $document->id],
            );

            return [
                'success' => true,
                'message' => $isResend
                    ? __('Document resent to :email.', ['email' => $recipientEmail])
                    : __('Document emailed to :email.', ['email' => $recipientEmail]),
            ];
        } catch (\Throwable $e) {
            Log::warning('Financial document email delivery failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            $error = $e->getMessage() ?: __('Mail transport error.');
            $this->recordFailure($invoice, $document, $error);

            return ['success' => false, 'message' => __('Email delivery failed: :error', ['error' => $error])];
        }
    }

    private function recordFailure(TenantInvoice $invoice, GeneratedDocument $document, string $error): void
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
