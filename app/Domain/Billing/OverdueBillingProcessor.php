<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\BillingAutomationRule;
use App\Models\CollectionNote;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Collection;

class OverdueBillingProcessor
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @return array{reminders: int, penalties: int, suspensions: int}
     */
    public function process(): array
    {
        $rules = BillingAutomationRule::platform();
        $counts = ['reminders' => 0, 'penalties' => 0, 'suspensions' => 0];

        $open = TenantInvoice::query()
            ->where('document_type', 'invoice')
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'draft'])
            ->whereNotNull('due_date')
            ->with('tenant')
            ->get();

        foreach ($open as $invoice) {
            $daysPastDue = $invoice->due_date->diffInDays(now(), false);
            if ($daysPastDue < 0) {
                continue;
            }

            $invoice->syncPaymentStatus();
            if ($invoice->isDirty('status')) {
                $invoice->save();
            }

            if ($daysPastDue >= $rules->reminder_after_days && $this->shouldSendReminder($invoice, $rules)) {
                $this->sendReminder($invoice);
                $counts['reminders']++;
            }

            if ($daysPastDue >= $rules->penalty_after_days && (float) $invoice->penalty_amount <= 0) {
                $this->applyPenalty($invoice, $rules);
                $counts['penalties']++;
            }

            if ($daysPastDue >= $rules->suspension_after_days + $rules->grace_period_days) {
                $this->suspendTenant($invoice);
                $counts['suspensions']++;
            }
        }

        return $counts;
    }

    private function shouldSendReminder(TenantInvoice $invoice, BillingAutomationRule $rules): bool
    {
        if ($invoice->last_reminder_at && $invoice->last_reminder_at->gt(now()->subDays(3))) {
            return false;
        }

        return in_array($invoice->status, ['overdue', 'partial', 'partially_paid', 'sent', 'pending'], true);
    }

    private function sendReminder(TenantInvoice $invoice): void
    {
        CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'note_type' => 'reminder',
            'body' => __('Automated payment reminder sent.'),
            'reminder_sent_at' => now(),
        ]);

        $invoice->update([
            'last_reminder_at' => now(),
            'reminder_count' => (int) $invoice->reminder_count + 1,
        ]);

        $this->activityLogger->log(
            'invoice.reminder_sent',
            ActivityLogCategory::BILLING,
            __('Reminder sent for invoice :number', ['number' => $invoice->invoice_number]),
            $invoice,
        );
    }

    private function applyPenalty(TenantInvoice $invoice, BillingAutomationRule $rules): void
    {
        $penalty = round($invoice->balanceDue() * ((float) $rules->penalty_percent / 100), 2);

        $invoice->update([
            'penalty_amount' => $penalty,
            'status' => 'overdue',
        ]);

        CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'note_type' => 'penalty',
            'body' => __('Penalty of :amount applied (:percent%).', [
                'amount' => TenantInvoice::formatMoney($penalty, $invoice->currency),
                'percent' => $rules->penalty_percent,
            ]),
        ]);

        $this->activityLogger->log(
            'invoice.penalty_applied',
            ActivityLogCategory::BILLING,
            __('Penalty applied on invoice :number', ['number' => $invoice->invoice_number]),
            $invoice,
            null,
            ['penalty_amount' => $penalty],
        );
    }

    private function suspendTenant(TenantInvoice $invoice): void
    {
        $tenant = $invoice->tenant;
        if (! $tenant || in_array($tenant->status, ['suspended', 'terminated'], true)) {
            return;
        }

        $tenant->update(['status' => 'suspended']);

        $this->activityLogger->log(
            'tenant.suspension_triggered',
            ActivityLogCategory::BILLING,
            __('Tenant :name suspended due to overdue invoice :number', [
                'name' => $tenant->company_name,
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
        );
    }

    /**
     * @return Collection<int, TenantInvoice>
     */
    public function overdueInvoices(): Collection
    {
        return TenantInvoice::query()
            ->where('document_type', 'invoice')
            ->where(function ($q): void {
                $q->where('status', 'overdue')
                    ->orWhere(function ($q2): void {
                        $q2->whereIn('status', ['sent', 'pending', 'partial', 'partially_paid'])
                            ->where('due_date', '<', now()->startOfDay());
                    });
            })
            ->with(['tenant', 'projectSubscription.project'])
            ->orderBy('due_date')
            ->get();
    }
}
