<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\BillingAutomationRule;
use App\Models\CollectionNote;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Collection;

class OverdueBillingProcessor
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly CollectionReminderService $reminderService,
    ) {}

    /**
     * @return array{reminders: int, penalties: int, suspensions: int}
     */
    public function process(): array
    {
        $rules = BillingAutomationRule::platform();
        $counts = ['reminders' => 0, 'penalties' => 0, 'suspensions' => 0];

        $open = TenantInvoice::query()
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'draft'])
            ->whereNotNull('due_date')
            ->with('tenant')
            ->get();

        foreach ($open as $invoice) {
            if ($invoice->due_date->isFuture()) {
                continue;
            }

            $daysPastDue = $invoice->due_date->diffInDays(now()->startOfDay());

            $invoice->syncPaymentStatus();
            if ($invoice->isDirty('status')) {
                $invoice->save();
            }

            if ($daysPastDue >= $rules->reminder_after_days
                && $this->reminderService->shouldSendAutomatedReminder($invoice, $rules)) {
                $result = $this->reminderService->sendReminder($invoice);
                if ($result['success']) {
                    $counts['reminders']++;
                }
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

    private function applyPenalty(TenantInvoice $invoice, BillingAutomationRule $rules): void
    {
        $penalty = round($invoice->balanceDue() * ((float) $rules->penalty_percent / 100), 2);

        $invoice->update([
            'penalty_amount' => $penalty,
            'status' => 'overdue',
        ]);

        CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'note_type' => 'penalty',
            'body' => __('Penalty of :amount applied (:percent%).', [
                'amount' => TenantInvoice::formatMoney($penalty, $invoice->currency),
                'percent' => $rules->penalty_percent,
            ]),
            'note' => __('Penalty of :amount applied (:percent%).', [
                'amount' => TenantInvoice::formatMoney($penalty, $invoice->currency),
                'percent' => $rules->penalty_percent,
            ]),
            'status' => 'open',
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

        CollectionNote::query()->create([
            'tenant_invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'note_type' => 'suspension',
            'body' => __('Tenant suspended due to prolonged overdue balance.'),
            'note' => __('Tenant suspended due to prolonged overdue balance.'),
            'status' => 'open',
            'outcome' => 'suspended',
        ]);

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
            ->where('document_type', BillingDocumentType::INVOICE)
            ->where(function ($q): void {
                $q->where('status', 'overdue')
                    ->orWhere(function ($q2): void {
                        $q2->whereIn('status', ['sent', 'pending', 'partial', 'partially_paid'])
                            ->where('due_date', '<', now()->startOfDay());
                    });
            })
            ->with(['tenant', 'projectSubscription.project'])
            ->orderBy('due_date')
            ->get()
            ->filter(fn (TenantInvoice $i) => $i->balanceDue() > 0.009);
    }
}
