<?php

namespace App\Domain\Billing;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantProjectModuleSubscription;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DraftInvoiceGenerator
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
        private readonly InvoiceNumberGenerator $numberGenerator,
    ) {}

    /**
     * @return array{invoice: TenantInvoice, lines: Collection<int, TenantInvoiceLineItem>}|null
     */
    public function generate(Tenant $tenant, ?int $subscriptionId = null): ?array
    {
        $tenant->loadMissing([
            'projectSubscriptions.project',
            'projectSubscriptions.moduleSubscriptions.projectModule',
            'projectSubscriptions.serviceIntegrations',
            'usageMetric',
        ]);

        $subscriptions = $tenant->projectSubscriptions;
        if ($subscriptionId) {
            $subscriptions = $subscriptions->where('id', $subscriptionId);
        }

        $linePayloads = collect();

        foreach ($subscriptions as $subscription) {
            if (! $this->isBillableSubscription($subscription)) {
                continue;
            }

            $linePayloads = $linePayloads->merge($this->linesForSubscription($tenant, $subscription));
        }

        $linePayloads = $linePayloads->merge($this->usageLines($tenant));

        if ($linePayloads->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($tenant, $subscriptionId, $subscriptions, $linePayloads): array {
            $currency = $tenant->billing_preferred_currency
                ?? $tenant->tenant_currency
                ?? $this->billingSettings->defaultCurrency();

            $taxRate = $tenant->billing_tax_exempt || ! $this->billingSettings->vatRegistered()
                ? 0.0
                : $this->billingSettings->vatRate();

            $computedLines = $linePayloads->map(function (array $line) use ($taxRate): array {
                $qty = (float) $line['quantity'];
                $unit = (float) $line['unit_price'];
                $discount = (float) ($line['discount'] ?? 0);
                $lineSubtotal = max(0, ($qty * $unit) - $discount);
                $lineTax = round($lineSubtotal * ($taxRate / 100), 2);

                return array_merge($line, [
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'line_total' => round($lineSubtotal + $lineTax, 2),
                ]);
            });

            $subtotal = round($computedLines->sum(fn (array $l) => max(0, ((float) $l['quantity'] * (float) $l['unit_price']) - (float) $l['discount'])), 2);
            $taxAmount = round($computedLines->sum('tax_amount'), 2);
            $total = round($subtotal + $taxAmount, 2);

            $primarySubscription = $subscriptionId
                ? $subscriptions->firstWhere('id', $subscriptionId)
                : $subscriptions->first();

            $paymentTerms = $tenant->billing_payment_terms ?? $this->billingSettings->defaultPaymentTerms();
            $dueDays = $this->paymentTermsToDays($paymentTerms);

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $tenant->id,
                'tenant_project_subscription_id' => $primarySubscription?->id,
                'invoice_number' => $this->numberGenerator->next(),
                'document_type' => 'invoice',
                'currency' => $currency,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_due' => $total,
                'amount_paid' => 0,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'issued_at' => now(),
                'due_date' => now()->addDays($dueDays)->toDateString(),
                'product_name' => $primarySubscription?->project?->name,
                'notes' => null,
            ]);

            $lines = $computedLines->map(function (array $line) use ($invoice): TenantInvoiceLineItem {
                return $invoice->lineItems()->create($line);
            });

            return ['invoice' => $invoice->fresh(['lineItems']), 'lines' => $lines];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function linesForSubscription(Tenant $tenant, TenantProjectSubscription $subscription): Collection
    {
        $lines = collect();
        $projectName = $subscription->project?->name ?? __('Product');

        if ((float) ($subscription->monthly_fee ?? 0) > 0) {
            $lines->push([
                'item_type' => 'subscription',
                'description' => __(':product subscription — :package', [
                    'product' => $projectName,
                    'package' => $subscription->package_name ?? __('Standard'),
                ]),
                'quantity' => 1,
                'unit_price' => (float) $subscription->monthly_fee,
                'discount' => (float) ($subscription->discount ?? 0),
                'related_model_type' => TenantProjectSubscription::class,
                'related_model_id' => $subscription->id,
            ]);
        }

        if ((float) ($subscription->setup_fee ?? 0) > 0 && ! $this->setupFeeAlreadyInvoiced($tenant, $subscription)) {
            $lines->push([
                'item_type' => 'setup_fee',
                'description' => __(':product — setup fee', ['product' => $projectName]),
                'quantity' => 1,
                'unit_price' => (float) $subscription->setup_fee,
                'discount' => 0,
                'related_model_type' => TenantProjectSubscription::class,
                'related_model_id' => $subscription->id,
            ]);
        }

        foreach ($subscription->moduleSubscriptions as $moduleSub) {
            if (! $moduleSub->enabled || ! $moduleSub->subscribed) {
                continue;
            }

            $price = $moduleSub->monthly_price_override !== null
                ? (float) $moduleSub->monthly_price_override
                : (float) ($moduleSub->projectModule?->monthly_price ?? 0);

            if ($price <= 0) {
                continue;
            }

            $lines->push([
                'item_type' => 'module',
                'description' => __(':product — module :module', [
                    'product' => $projectName,
                    'module' => $moduleSub->projectModule?->name ?? __('Module'),
                ]),
                'quantity' => 1,
                'unit_price' => $price,
                'discount' => 0,
                'related_model_type' => TenantProjectModuleSubscription::class,
                'related_model_id' => $moduleSub->id,
            ]);
        }

        foreach ($subscription->serviceIntegrations as $integration) {
            if ($integration->status !== 'active') {
                continue;
            }

            $fee = $this->integrationMonthlyFee($integration);
            if ($fee <= 0) {
                continue;
            }

            $lines->push([
                'item_type' => 'integration',
                'description' => __(':product — :service integration', [
                    'product' => $projectName,
                    'service' => $integration->display_name,
                ]),
                'quantity' => 1,
                'unit_price' => $fee,
                'discount' => 0,
                'related_model_type' => TenantProjectServiceIntegration::class,
                'related_model_id' => $integration->id,
            ]);
        }

        return $lines;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function usageLines(Tenant $tenant): Collection
    {
        $rate = $this->billingSettings->usageRatePerMb();
        $usage = $tenant->usageMetric;

        if ($rate <= 0 || ! $usage || (float) ($usage->storage_usage_mb ?? 0) <= 0) {
            return collect();
        }

        $mb = (float) $usage->storage_usage_mb;

        return collect([
            [
                'item_type' => 'usage',
                'description' => __('Storage usage (:mb MB)', ['mb' => number_format($mb, 2)]),
                'quantity' => $mb,
                'unit_price' => $rate,
                'discount' => 0,
                'related_model_type' => null,
                'related_model_id' => null,
            ],
        ]);
    }

    private function integrationMonthlyFee(TenantProjectServiceIntegration $integration): float
    {
        if ($integration->balance_credits !== null && (float) $integration->balance_credits > 0) {
            return (float) $integration->balance_credits;
        }

        if ($integration->monthly_quota && $integration->monthly_quota > 0) {
            return (float) $integration->monthly_quota;
        }

        return 0.0;
    }

    public function setupFeeAlreadyInvoiced(Tenant $tenant, TenantProjectSubscription $subscription): bool
    {
        return TenantInvoiceLineItem::query()
            ->where('item_type', 'setup_fee')
            ->where('related_model_type', TenantProjectSubscription::class)
            ->where('related_model_id', $subscription->id)
            ->whereHas('invoice', function ($q) use ($tenant): void {
                $q->where('tenant_id', $tenant->id)
                    ->whereNotIn('status', ['cancelled', 'void']);
            })
            ->exists();
    }

    public function isBillableSubscription(TenantProjectSubscription $subscription): bool
    {
        if ($subscription->product_status === 'disabled') {
            return false;
        }

        return ! in_array($subscription->license_status, ['suspended', 'expired', 'disabled'], true);
    }

    private function paymentTermsToDays(string $terms): int
    {
        if (preg_match('/(\d+)/', $terms, $matches)) {
            return max(1, (int) $matches[1]);
        }

        return 30;
    }
}
