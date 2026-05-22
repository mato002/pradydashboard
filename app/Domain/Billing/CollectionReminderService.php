<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Mail\PaymentReminderMail;
use App\Models\BillingAutomationRule;
use App\Models\CollectionNote;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CollectionReminderService
{
    public function __construct(
        private readonly DocumentDeliveryService $documentDelivery,
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @return array{success: bool, message: string}
     */
    public function sendReminder(TenantInvoice $invoice, ?string $recipientOverride = null): array
    {
        $invoice->loadMissing(['tenant', 'lineItems']);

        if ($invoice->balanceDue() <= 0.009) {
            return ['success' => false, 'message' => __('Invoice has no outstanding balance.')];
        }

        $recipient = trim((string) ($recipientOverride ?? ''));
        if ($recipient === '') {
            $recipient = $this->documentDelivery->defaultRecipientEmail($invoice) ?? '';
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => __('No valid recipient email for this invoice.')];
        }

        $pdfPath = null;
        try {
            $document = $this->documentDelivery->ensurePdf($invoice);
            $pdfPath = $document->pdf_path;
        } catch (\Throwable) {
            // PDF optional for reminders.
        }

        try {
            Mail::to($recipient)->send(new PaymentReminderMail($invoice, $pdfPath));

            $invoice->update([
                'last_reminder_at' => now(),
                'reminder_count' => (int) $invoice->reminder_count + 1,
            ]);

            CollectionNote::query()->create([
                'tenant_invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'user_id' => auth()->id(),
                'note_type' => 'reminder',
                'body' => __('Payment reminder emailed to :email.', ['email' => $recipient]),
                'note' => __('Payment reminder emailed to :email.', ['email' => $recipient]),
                'reminder_sent_at' => now(),
                'status' => 'open',
                'outcome' => null,
            ]);

            $this->activityLogger->log(
                'collection.reminder_sent',
                ActivityLogCategory::BILLING,
                __('Reminder sent for :number to :email', [
                    'number' => $invoice->invoice_number,
                    'email' => $recipient,
                ]),
                $invoice,
                null,
                ['recipient' => $recipient],
            );

            return ['success' => true, 'message' => __('Reminder sent to :email.', ['email' => $recipient])];
        } catch (\Throwable $e) {
            Log::warning('Collection reminder email failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => __('Reminder failed: :error', ['error' => $e->getMessage()])];
        }
    }

    /**
     * @return array{reminders: int, skipped: int}
     */
    public function processAutomatedReminders(): array
    {
        $rules = BillingAutomationRule::platform();
        $counts = ['reminders' => 0, 'skipped' => 0];

        $candidates = TenantInvoice::query()
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'draft'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->with(['tenant', 'lineItems'])
            ->get()
            ->filter(fn (TenantInvoice $inv) => $inv->balanceDue() > 0.009);

        foreach ($candidates as $invoice) {
            $daysPastDue = $invoice->due_date->diffInDays(now()->startOfDay());

            if ($daysPastDue < $rules->reminder_after_days) {
                $counts['skipped']++;

                continue;
            }

            if (! $this->shouldSendAutomatedReminder($invoice, $rules)) {
                $counts['skipped']++;

                continue;
            }

            $result = $this->sendReminder($invoice);
            if ($result['success']) {
                $counts['reminders']++;
            } else {
                $counts['skipped']++;
            }
        }

        return $counts;
    }

    public function shouldSendAutomatedReminder(TenantInvoice $invoice, ?BillingAutomationRule $rules = null): bool
    {
        $rules ??= BillingAutomationRule::platform();

        if ($invoice->last_reminder_at && $invoice->last_reminder_at->gt(now()->subDays(max(3, (int) $rules->reminder_after_days)))) {
            return false;
        }

        return in_array($invoice->status, ['overdue', 'partial', 'partially_paid', 'sent', 'pending'], true);
    }
}
