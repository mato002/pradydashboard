<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantSubscription;
use App\Support\ActivityLogCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionBillingService
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly DocumentFinalizer $documentFinalizer,
        private readonly BillingSettings $billingSettings,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function renew(TenantSubscription $subscription): TenantSubscription
    {
        $subscription->loadMissing('tenant');

        $periodStart = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end->copy()
            : now();

        if ($periodStart->isPast()) {
            $periodStart = now();
        }

        $periodEnd = $subscription->billing_cycle === 'annual'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        $subscription->update([
            'current_period_start' => $periodStart->toDateString(),
            'current_period_end' => $periodEnd->toDateString(),
            'status' => in_array($subscription->status, ['overdue', 'grace_period', 'grace'], true)
                ? 'active'
                : $subscription->status,
        ]);

        if ($subscription->tenant) {
            $subscription->tenant->update([
                'renewal_date' => $periodEnd->toDateString(),
                'start_date' => $periodStart->toDateString(),
                'status' => 'active',
            ]);
        }

        $this->activityLogger->log(
            'subscription.renewed',
            ActivityLogCategory::BILLING,
            __('Subscription :plan renewed through :date.', [
                'plan' => $subscription->plan_name,
                'date' => $periodEnd->format('M j, Y'),
            ]),
            $subscription->tenant,
            null,
            ['subscription_id' => $subscription->id],
        );

        return $subscription->fresh(['tenant', 'saasPlan']);
    }

    public function renewFleet(): int
    {
        $count = 0;

        TenantSubscription::query()
            ->with('tenant')
            ->where('auto_renew', true)
            ->whereIn('status', ['active', 'trial', 'grace_period', 'grace', 'overdue'])
            ->whereDate('current_period_end', '<=', now()->addDays(14))
            ->each(function (TenantSubscription $subscription) use (&$count): void {
                $this->renew($subscription);
                $count++;
            });

        return $count;
    }

    public function suspend(TenantSubscription $subscription, ?string $reason = null): TenantSubscription
    {
        $subscription->update([
            'status' => 'suspended',
            'auto_renew' => false,
        ]);

        if ($subscription->tenant) {
            $subscription->tenant->update(['status' => 'suspended']);
        }

        $this->activityLogger->log(
            'subscription.suspended',
            ActivityLogCategory::BILLING,
            $reason ?? __('Subscription :plan suspended from billing center.', ['plan' => $subscription->plan_name]),
            $subscription->tenant,
            null,
            ['subscription_id' => $subscription->id],
        );

        return $subscription->fresh(['tenant']);
    }

    public function generateInvoice(TenantSubscription $subscription): ?TenantInvoice
    {
        $subscription->loadMissing('tenant', 'saasPlan');

        $tenant = $subscription->tenant;
        if (! $tenant) {
            return null;
        }

        if ($this->invoiceExistsForCurrentPeriod($subscription)) {
            return null;
        }

        return DB::transaction(function () use ($subscription, $tenant): TenantInvoice {
            $amount = (float) $subscription->amount;
            $taxRate = $tenant->billing_tax_exempt || ! $this->billingSettings->vatRegistered()
                ? 0.0
                : $this->billingSettings->vatRate();
            $taxAmount = round($amount * ($taxRate / 100), 2);
            $total = round($amount + $taxAmount, 2);
            $currency = $tenant->billing_preferred_currency ?? $tenant->tenant_currency ?? $this->billingSettings->defaultCurrency();

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $tenant->id,
                'invoice_number' => $this->numberGenerator->next(),
                'document_type' => 'invoice',
                'currency' => $currency,
                'subtotal' => $amount,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_due' => $total,
                'amount_paid' => 0,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'issued_at' => now(),
                'due_date' => now()->addDays(30)->toDateString(),
                'product_name' => $subscription->product_name ?? $subscription->plan_name,
                'is_recurring' => true,
                'generated_by' => 'subscription-billing',
            ]);

            TenantInvoiceLineItem::query()->create([
                'tenant_invoice_id' => $invoice->id,
                'item_type' => 'subscription',
                'description' => __(':plan subscription — :cycle', [
                    'plan' => $subscription->plan_name,
                    'cycle' => ucfirst($subscription->billing_cycle),
                ]),
                'quantity' => 1,
                'unit_price' => $amount,
                'discount' => 0,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $total,
                'related_model_type' => TenantSubscription::class,
                'related_model_id' => $subscription->id,
            ]);

            $invoice->update(['status' => 'sent']);
            $this->documentFinalizer->finalize($invoice);

            $this->activityLogger->log(
                'subscription.invoice_generated',
                ActivityLogCategory::BILLING,
                __('Invoice :number generated for :plan.', [
                    'number' => $invoice->invoice_number,
                    'plan' => $subscription->plan_name,
                ]),
                $tenant,
                null,
                ['subscription_id' => $subscription->id, 'invoice_id' => $invoice->id],
            );

            return $invoice->fresh(['lineItems']);
        });
    }

    public function generateFleetInvoices(): int
    {
        $count = 0;

        TenantSubscription::query()
            ->with('tenant')
            ->whereIn('status', ['active', 'trial', 'overdue'])
            ->each(function (TenantSubscription $subscription) use (&$count): void {
                if ($this->generateInvoice($subscription) !== null) {
                    $count++;
                }
            });

        return $count;
    }

    private function invoiceExistsForCurrentPeriod(TenantSubscription $subscription): bool
    {
        if (! $subscription->current_period_start || ! $subscription->current_period_end) {
            return false;
        }

        return TenantInvoiceLineItem::query()
            ->where('item_type', 'subscription')
            ->where('related_model_type', TenantSubscription::class)
            ->where('related_model_id', $subscription->id)
            ->whereHas('invoice', function ($query) use ($subscription): void {
                $query->where('tenant_id', $subscription->tenant_id)
                    ->whereNotIn('status', ['cancelled', 'void'])
                    ->whereDate('issue_date', '>=', $subscription->current_period_start)
                    ->whereDate('issue_date', '<=', $subscription->current_period_end);
            })
            ->exists();
    }
}
