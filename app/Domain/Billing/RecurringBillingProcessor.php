<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\BillingAutomationRule;
use App\Models\InvoiceRecurringSchedule;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Jobs\Billing\SendFinancialDocumentEmailJob;
use App\Support\ActivityLogCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\Cache\OperationalCache;

class RecurringBillingProcessor
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly DocumentFinalizer $documentFinalizer,
        private readonly InvoiceEmailDelivery $emailDelivery,
        private readonly ActivityLogger $activityLogger,
        private readonly OperationalCache $operationalCache,
    ) {}

    /**
     * @return Collection<int, TenantInvoice>
     */
    public function processDueSchedules(?Carbon $asOf = null): Collection
    {
        if (! BillingAutomationRule::platform()->recurring_enabled) {
            return collect();
        }

        $asOf ??= now();
        $generated = collect();

        $schedules = InvoiceRecurringSchedule::query()
            ->where('enabled', true)
            ->where('next_run_at', '<=', $asOf)
            ->with('tenant')
            ->get();

        foreach ($schedules as $schedule) {
            $invoice = $this->operationalCache->lock(
                'billing:recurring:'.$schedule->id,
                config('redis_cache.locks.recurring_schedule', 120),
                fn () => $this->generateFromSchedule($schedule),
            );

            if ($invoice) {
                $generated->push($invoice);
            }
        }

        return $generated;
    }

    public function generateFromSchedule(InvoiceRecurringSchedule $schedule): ?TenantInvoice
    {
        $tenant = $schedule->tenant;
        if (! $tenant) {
            return null;
        }

        return DB::transaction(function () use ($schedule, $tenant): TenantInvoice {
            $taxRate = (float) $schedule->tax_rate;
            $subtotal = (float) $schedule->amount;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = round($subtotal + $taxAmount, 2);

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $tenant->id,
                'invoice_number' => $this->numberGenerator->next(),
                'document_type' => 'invoice',
                'currency' => $tenant->billing_preferred_currency ?? $tenant->tenant_currency ?? 'KES',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_due' => $total,
                'amount_paid' => 0,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'issued_at' => now(),
                'due_date' => now()->addDays(30)->toDateString(),
                'product_name' => $schedule->product_name,
                'is_recurring' => true,
                'generated_by' => $schedule->generated_by ?? 'recurring-scheduler',
            ]);

            TenantInvoiceLineItem::query()->create([
                'tenant_invoice_id' => $invoice->id,
                'item_type' => 'subscription',
                'description' => $schedule->name.' — '.$schedule->frequencyLabel(),
                'quantity' => 1,
                'unit_price' => $subtotal,
                'discount' => 0,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $total,
            ]);

            $invoice->update(['status' => 'sent']);

            $document = $this->documentFinalizer->finalize($invoice);

            if ($schedule->auto_email || BillingAutomationRule::platform()->auto_send_invoices) {
                $recipient = trim((string) ($tenant->billing_email ?? ''));
                if ($recipient !== '') {
                    if (config('queue.default') !== 'sync') {
                        SendFinancialDocumentEmailJob::dispatch($invoice->id, $document->id, $recipient, false);
                    } else {
                        $this->emailDelivery->send($invoice, $document, $recipient, false);
                    }
                }
            }

            $schedule->update(['next_run_at' => $this->nextRunAt($schedule)]);

            $this->activityLogger->log(
                'invoice.recurring_generated',
                ActivityLogCategory::BILLING,
                __('Recurring invoice :number generated for :tenant', [
                    'number' => $invoice->invoice_number,
                    'tenant' => $tenant->company_name,
                ]),
                $invoice,
            );

            return $invoice->fresh();
        });
    }

    private function nextRunAt(InvoiceRecurringSchedule $schedule): Carbon
    {
        $base = $schedule->next_run_at ?? now();

        if ($schedule->cycle === 'custom' && $schedule->custom_interval_days) {
            return $base->copy()->addDays($schedule->custom_interval_days);
        }

        return match ($schedule->frequency) {
            'quarterly' => $base->copy()->addMonths(3),
            'annual' => $base->copy()->addYear(),
            default => $base->copy()->addMonth(),
        };
    }
}
