<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Jobs\Billing\ActivatePaymentsGatewayTenantJob;
use App\Models\Tenant;
use App\Models\TenantAccessControl;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Support\ActivityLogCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TenantBillingActivationService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly TenantProjectProvisioner $projectProvisioner,
    ) {}

    public function activateFromPaidInvoice(TenantInvoice $invoice): bool
    {
        $invoice->loadMissing(['tenant', 'lineItems']);
        $invoice->tenant?->loadMissing('latestAccessControl');

        if ($invoice->status !== 'paid' || $invoice->document_type !== 'invoice' || ! $invoice->tenant_id) {
            return false;
        }

        $tenant = $invoice->tenant;
        if (! $tenant || in_array($tenant->status, ['cancelled', 'terminated'], true)) {
            return false;
        }

        $subscription = $this->resolveSubscription($invoice);
        if (! $this->shouldActivate($tenant, $invoice, $subscription)) {
            return false;
        }

        return DB::transaction(function () use ($invoice, $tenant, $subscription): bool {
            $periodEnd = $this->resolveNextPeriodEnd($tenant, $subscription);

            $tenant->update([
                'status' => 'active',
                'renewal_date' => $periodEnd,
                'start_date' => $tenant->start_date ?? now()->toDateString(),
            ]);

            if ($subscription) {
                $periodStart = now()->toDateString();
                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'auto_renew' => true,
                ]);
            } else {
                TenantSubscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereIn('status', ['overdue', 'suspended', 'grace_period', 'grace', 'trial'])
                    ->update([
                        'status' => 'active',
                        'current_period_end' => $periodEnd,
                    ]);
            }

            $this->restoreAccessPolicy($tenant);
            $this->projectProvisioner->syncPrimarySubscription($tenant->fresh());

            $this->activityLogger->log(
                'tenant.activated_from_payment',
                ActivityLogCategory::BILLING,
                __('Tenant :name activated after invoice :number was paid.', [
                    'name' => $tenant->company_name,
                    'number' => $invoice->invoice_number,
                ]),
                $tenant,
                null,
                [
                    'invoice_id' => $invoice->id,
                    'renewal_date' => $periodEnd->toDateString(),
                    'subscription_id' => $subscription?->id,
                ],
            );

            if (filled($tenant->payments_gateway_tenant_uuid)) {
                ActivatePaymentsGatewayTenantJob::dispatch($tenant->id);
            }

            return true;
        });
    }

    private function shouldActivate(Tenant $tenant, TenantInvoice $invoice, ?TenantSubscription $subscription): bool
    {
        if ($subscription !== null) {
            return true;
        }

        if (in_array($tenant->status, ['overdue', 'suspended', 'restricted', 'warning', 'trial', 'prospect', 'onboarding'], true)) {
            return true;
        }

        if ($tenant->renewal_date && $tenant->renewal_date->isBefore(today())) {
            return true;
        }

        if ($tenant->latestAccessControl?->level === 'restricted') {
            return true;
        }

        if ($invoice->is_recurring === true) {
            return true;
        }

        return false;
    }

    private function resolveSubscription(TenantInvoice $invoice): ?TenantSubscription
    {
        $line = $invoice->lineItems
            ->first(fn ($item) => $item->related_model_type === TenantSubscription::class && $item->related_model_id);

        if ($line) {
            return TenantSubscription::query()->find($line->related_model_id);
        }

        return TenantSubscription::query()
            ->where('tenant_id', $invoice->tenant_id)
            ->whereIn('status', ['active', 'trial', 'overdue', 'grace_period', 'grace', 'suspended'])
            ->orderByDesc('current_period_end')
            ->first();
    }

    private function resolveNextPeriodEnd(Tenant $tenant, ?TenantSubscription $subscription): Carbon
    {
        $cycle = $subscription?->billing_cycle ?? $tenant->billing_cycle ?? 'monthly';
        $anchor = $subscription?->current_period_end ?? $tenant->renewal_date ?? now();
        $base = $anchor instanceof Carbon ? $anchor->copy() : Carbon::parse($anchor);

        if ($base->isPast()) {
            $base = now();
        }

        return $cycle === 'annual'
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();
    }

    private function restoreAccessPolicy(Tenant $tenant): void
    {
        TenantAccessControl::query()->create([
            'tenant_id' => $tenant->id,
            'level' => 'soft_reminder',
            'restrict_login' => false,
            'disabled_modules' => [],
            'effective_from' => now(),
            'effective_until' => null,
            'notes' => __('Access restored automatically after invoice payment.'),
        ]);
    }
}
