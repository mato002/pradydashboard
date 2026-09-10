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

    /**
     * Bill the next renewal period. Does not extend entitlement — that happens only after payment.
     */
    public function renew(TenantSubscription $subscription): ?TenantInvoice
    {
        return $this->generateRenewalInvoice($subscription);
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
                if ($this->renew($subscription) !== null) {
                    $count++;
                }
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

    /**
     * Create a new subscription and cancel the one it replaces (upgrade path).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function upgradeSubscription(
        Tenant $tenant,
        TenantSubscription $replaces,
        array $attributes,
    ): TenantSubscription {
        if ((int) $replaces->tenant_id !== (int) $tenant->id) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'replaces_subscription_id' => [__('The subscription to replace must belong to the selected tenant.')],
            ]);
        }

        if (in_array($replaces->status, ['cancelled', 'suspended'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'replaces_subscription_id' => [__('That subscription is already ended and cannot be upgraded.')],
            ]);
        }

        return DB::transaction(function () use ($tenant, $replaces, $attributes): TenantSubscription {
            $replaces->update([
                'status' => 'cancelled',
                'auto_renew' => false,
            ]);

            $productName = $attributes['product_name'] ?? $replaces->product_name;
            if (filled($productName)) {
                TenantSubscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('id', '!=', $replaces->id)
                    ->whereIn('status', ['active', 'trial', 'grace_period', 'grace', 'overdue'])
                    ->where('product_name', $productName)
                    ->update([
                        'status' => 'cancelled',
                        'auto_renew' => false,
                    ]);
            }
            $subscription = TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $attributes['saas_plan_id'] ?? null,
                'plan_name' => $attributes['plan_name'],
                'product_name' => $attributes['product_name'] ?? $tenant->project?->name,
                'amount' => $attributes['amount'],
                'billing_cycle' => $attributes['billing_cycle'],
                'current_period_start' => $attributes['current_period_start'],
                'current_period_end' => $attributes['current_period_end'],
                'status' => $attributes['status'],
                'auto_renew' => (bool) ($attributes['auto_renew'] ?? false),
            ]);

            $tenant->update([
                'subscription_plan' => $attributes['plan_name'],
                'subscription_amount' => $attributes['amount'],
                'billing_cycle' => $attributes['billing_cycle'],
                'start_date' => $attributes['current_period_start'],
                'renewal_date' => $attributes['current_period_end'],
                'status' => $this->mapSubscriptionStatusToTenant((string) $attributes['status']),
            ]);

            $this->activityLogger->log(
                'subscription.upgraded',
                ActivityLogCategory::BILLING,
                __('Subscription upgraded from :old to :new for :tenant.', [
                    'old' => $replaces->plan_name,
                    'new' => $subscription->plan_name,
                    'tenant' => $tenant->company_name,
                ]),
                $tenant,
                ['subscription_id' => $replaces->id, 'plan' => $replaces->plan_name],
                [
                    'subscription_id' => $subscription->id,
                    'plan' => $subscription->plan_name,
                    'replaced_subscription_id' => $replaces->id,
                ],
            );

            return $subscription->fresh(['tenant', 'saasPlan']);
        });
    }

    private function mapSubscriptionStatusToTenant(string $status): string
    {
        return match ($status) {
            'trial' => 'trial',
            'overdue' => 'overdue',
            'suspended', 'cancelled' => 'suspended',
            'grace_period', 'grace' => 'warning',
            default => 'active',
        };
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

        $periodStart = $subscription->current_period_start instanceof Carbon
            ? $subscription->current_period_start->copy()
            : ($subscription->current_period_start ? Carbon::parse($subscription->current_period_start) : now());
        $periodEnd = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end->copy()
            : ($subscription->current_period_end ? Carbon::parse($subscription->current_period_end) : now()->addMonth());

        return $this->createSubscriptionInvoice(
            $subscription,
            $tenant,
            $periodStart,
            $periodEnd,
            __(':plan subscription — :cycle', [
                'plan' => $subscription->plan_name,
                'cycle' => ucfirst($subscription->billing_cycle),
            ]),
            'subscription.invoice_generated',
            __('Invoice :number generated for :plan.', [
                'number' => ':number',
                'plan' => $subscription->plan_name,
            ]),
        );
    }

    /**
     * Create an invoice for the upcoming renewal window without changing period or access.
     */
    public function generateRenewalInvoice(TenantSubscription $subscription): ?TenantInvoice
    {
        $subscription->loadMissing('tenant', 'saasPlan');

        $tenant = $subscription->tenant;
        if (! $tenant) {
            return null;
        }

        [$periodStart, $periodEnd] = $this->resolveNextRenewalWindow($subscription);

        // Renewal invoices are issued near period end (issue_date = today), so dedupe against
        // the renew eligibility window rather than the future period dates.
        if ($this->renewalInvoiceAlreadyExists($subscription)) {
            return null;
        }

        $description = __(':plan renewal — :start to :end', [
            'plan' => $subscription->plan_name,
            'start' => $periodStart->format('M j, Y'),
            'end' => $periodEnd->format('M j, Y'),
        ]);

        return $this->createSubscriptionInvoice(
            $subscription,
            $tenant,
            $periodStart,
            $periodEnd,
            $description,
            'subscription.renewal_invoiced',
            __('Renewal invoice :number generated for :plan (:start – :end).', [
                'number' => ':number',
                'plan' => $subscription->plan_name,
                'start' => $periodStart->format('M j, Y'),
                'end' => $periodEnd->format('M j, Y'),
            ]),
        );
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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveNextRenewalWindow(TenantSubscription $subscription): array
    {
        $periodStart = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end->copy()
            : ($subscription->current_period_end ? Carbon::parse($subscription->current_period_end) : now());

        if ($periodStart->isPast()) {
            $periodStart = now();
        }

        $periodEnd = $subscription->billing_cycle === 'annual'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        return [$periodStart, $periodEnd];
    }

    private function createSubscriptionInvoice(
        TenantSubscription $subscription,
        Tenant $tenant,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $lineDescription,
        string $activityAction,
        string $activityMessageTemplate,
    ): TenantInvoice {
        return DB::transaction(function () use (
            $subscription,
            $tenant,
            $periodStart,
            $periodEnd,
            $lineDescription,
            $activityAction,
            $activityMessageTemplate,
        ): TenantInvoice {
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
                'description' => $lineDescription,
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

            $message = str_replace(':number', $invoice->invoice_number, $activityMessageTemplate);

            $this->activityLogger->log(
                $activityAction,
                ActivityLogCategory::BILLING,
                $message,
                $tenant,
                null,
                [
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
            );

            return $invoice->fresh(['lineItems']);
        });
    }

    private function invoiceExistsForCurrentPeriod(TenantSubscription $subscription): bool
    {
        if (! $subscription->current_period_start || ! $subscription->current_period_end) {
            return false;
        }

        $start = $subscription->current_period_start instanceof Carbon
            ? $subscription->current_period_start
            : Carbon::parse($subscription->current_period_start);
        $end = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end
            : Carbon::parse($subscription->current_period_end);

        return $this->invoiceExistsForPeriodWindow($subscription, $start, $end);
    }

    private function invoiceExistsForPeriodWindow(
        TenantSubscription $subscription,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): bool {
        return TenantInvoiceLineItem::query()
            ->where('item_type', 'subscription')
            ->where('related_model_type', TenantSubscription::class)
            ->where('related_model_id', $subscription->id)
            ->whereHas('invoice', function ($query) use ($subscription, $periodStart, $periodEnd): void {
                $query->where('tenant_id', $subscription->tenant_id)
                    ->whereNotIn('status', ['cancelled', 'void'])
                    ->whereDate('issue_date', '>=', $periodStart->toDateString())
                    ->whereDate('issue_date', '<=', $periodEnd->toDateString());
            })
            ->exists();
    }

    private function renewalInvoiceAlreadyExists(TenantSubscription $subscription): bool
    {
        $periodEnd = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end->copy()
            : ($subscription->current_period_end ? Carbon::parse($subscription->current_period_end) : now());

        $windowStart = $periodEnd->copy()->subDays(14);

        return TenantInvoiceLineItem::query()
            ->where('item_type', 'subscription')
            ->where('related_model_type', TenantSubscription::class)
            ->where('related_model_id', $subscription->id)
            ->where('description', 'like', '%renewal%')
            ->whereHas('invoice', function ($query) use ($subscription, $windowStart): void {
                $query->where('tenant_id', $subscription->tenant_id)
                    ->whereNotIn('status', ['cancelled', 'void'])
                    ->where('generated_by', 'subscription-billing')
                    ->whereDate('issue_date', '>=', $windowStart->toDateString());
            })
            ->exists();
    }
}
