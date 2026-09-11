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
            $isUpgrade = $subscription?->replaces_subscription_id !== null;
            $periodEnd = $isUpgrade && $subscription->current_period_end
                ? ($subscription->current_period_end instanceof Carbon
                    ? $subscription->current_period_end->copy()
                    : Carbon::parse($subscription->current_period_end))
                : $this->resolveNextPeriodEnd($tenant, $subscription);

            if ($subscription) {
                $periodStart = $isUpgrade && $subscription->current_period_start
                    ? ($subscription->current_period_start instanceof Carbon
                        ? $subscription->current_period_start->toDateString()
                        : Carbon::parse($subscription->current_period_start)->toDateString())
                    : now()->toDateString();

                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'auto_renew' => true,
                ]);

                $this->finalizePaidUpgrade($tenant, $subscription, $periodEnd);
            } else {
                TenantSubscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereIn('status', ['overdue', 'suspended', 'grace_period', 'grace', 'trial', 'pending'])
                    ->update([
                        'status' => 'active',
                        'current_period_end' => $periodEnd,
                    ]);
            }

            $tenant->update([
                'status' => 'active',
                'renewal_date' => $periodEnd,
                'start_date' => $tenant->start_date ?? now()->toDateString(),
            ]);

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

    public function deactivateFromUnpaidInvoice(TenantInvoice $invoice): bool
    {
        $invoice->loadMissing(['tenant', 'lineItems']);

        if ($invoice->status === 'paid' || $invoice->document_type !== 'invoice' || ! $invoice->tenant_id) {
            return false;
        }

        $tenant = $invoice->tenant;
        if (! $tenant || in_array($tenant->status, ['cancelled', 'terminated'], true)) {
            return false;
        }

        $subscription = $this->resolveSubscription($invoice);

        if ($this->hasOtherPaidCoveringInvoice($invoice, $tenant, $subscription)) {
            return false;
        }

        return DB::transaction(function () use ($invoice, $tenant, $subscription): bool {
            if ($subscription?->replaces_subscription_id) {
                return $this->rollbackPaidUpgrade($invoice, $tenant, $subscription);
            }

            $rolledPeriodEnd = null;

            if ($subscription) {
                $rolledPeriodEnd = $this->resolvePreviousPeriodEnd($subscription);
                $rolledPeriodStart = $subscription->billing_cycle === 'annual'
                    ? $rolledPeriodEnd->copy()->subYear()
                    : $rolledPeriodEnd->copy()->subMonth();

                $subscription->update([
                    'status' => 'overdue',
                    'current_period_start' => $rolledPeriodStart->toDateString(),
                    'current_period_end' => $rolledPeriodEnd->toDateString(),
                ]);
            } else {
                TenantSubscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereIn('status', ['active', 'trial', 'grace_period', 'grace'])
                    ->update(['status' => 'overdue']);
            }

            $tenant->update([
                'status' => 'overdue',
                'renewal_date' => $rolledPeriodEnd?->toDateString() ?? $tenant->renewal_date,
            ]);

            $this->restrictAccessPolicy($tenant);
            $this->projectProvisioner->syncPrimarySubscription($tenant->fresh());

            $this->activityLogger->log(
                'tenant.deactivated_from_payment_reverse',
                ActivityLogCategory::BILLING,
                __('Tenant :name access rolled back after invoice :number was unpaid.', [
                    'name' => $tenant->company_name,
                    'number' => $invoice->invoice_number,
                ]),
                $tenant,
                null,
                [
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $subscription?->id,
                    'renewal_date' => $rolledPeriodEnd?->toDateString(),
                ],
            );

            return true;
        });
    }

    private function finalizePaidUpgrade(Tenant $tenant, TenantSubscription $subscription, Carbon $periodEnd): void
    {
        $replacedId = $subscription->replaces_subscription_id;
        if (! $replacedId) {
            $tenant->update([
                'subscription_plan' => $subscription->plan_name,
                'subscription_amount' => $subscription->amount,
                'billing_cycle' => $subscription->billing_cycle,
                'start_date' => $subscription->current_period_start?->toDateString() ?? now()->toDateString(),
                'renewal_date' => $periodEnd,
            ]);

            return;
        }

        $replaced = TenantSubscription::query()->find($replacedId);
        if ($replaced && (int) $replaced->tenant_id === (int) $tenant->id) {
            $replaced->update([
                'status' => 'cancelled',
                'auto_renew' => false,
            ]);
        }

        $productName = $subscription->product_name ?? $replaced?->product_name;
        if (filled($productName)) {
            TenantSubscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('id', '!=', $subscription->id)
                ->where('id', '!=', $replacedId)
                ->whereIn('status', ['active', 'trial', 'grace_period', 'grace', 'overdue', 'pending'])
                ->where('product_name', $productName)
                ->update([
                    'status' => 'cancelled',
                    'auto_renew' => false,
                ]);
        }

        $tenant->update([
            'subscription_plan' => $subscription->plan_name,
            'subscription_amount' => $subscription->amount,
            'billing_cycle' => $subscription->billing_cycle,
            'start_date' => $subscription->current_period_start?->toDateString() ?? now()->toDateString(),
            'renewal_date' => $periodEnd,
        ]);

        $this->activityLogger->log(
            'subscription.upgraded',
            ActivityLogCategory::BILLING,
            __('Subscription upgraded from :old to :new for :tenant after payment.', [
                'old' => $replaced?->plan_name ?? __('prior plan'),
                'new' => $subscription->plan_name,
                'tenant' => $tenant->company_name,
            ]),
            $tenant,
            $replaced ? ['subscription_id' => $replaced->id, 'plan' => $replaced->plan_name] : null,
            [
                'subscription_id' => $subscription->id,
                'plan' => $subscription->plan_name,
                'replaced_subscription_id' => $replacedId,
            ],
        );
    }

    private function rollbackPaidUpgrade(TenantInvoice $invoice, Tenant $tenant, TenantSubscription $subscription): bool
    {
        $replaced = TenantSubscription::query()->find($subscription->replaces_subscription_id);

        $subscription->update([
            'status' => 'pending',
            'auto_renew' => false,
        ]);

        if ($replaced && (int) $replaced->tenant_id === (int) $tenant->id) {
            $replaced->update([
                'status' => 'active',
                'auto_renew' => true,
            ]);

            $tenant->update([
                'status' => 'active',
                'subscription_plan' => $replaced->plan_name,
                'subscription_amount' => $replaced->amount,
                'billing_cycle' => $replaced->billing_cycle,
                'start_date' => $replaced->current_period_start?->toDateString() ?? $tenant->start_date,
                'renewal_date' => $replaced->current_period_end?->toDateString() ?? $tenant->renewal_date,
            ]);
        }

        $this->restoreAccessPolicy($tenant);
        $this->projectProvisioner->syncPrimarySubscription($tenant->fresh());

        $this->activityLogger->log(
            'tenant.deactivated_from_payment_reverse',
            ActivityLogCategory::BILLING,
            __('Upgrade for :name rolled back after invoice :number was unpaid; prior plan restored.', [
                'name' => $tenant->company_name,
                'number' => $invoice->invoice_number,
            ]),
            $tenant,
            null,
            [
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscription->id,
                'replaced_subscription_id' => $replaced?->id,
            ],
        );

        return true;
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
            ->whereIn('status', ['active', 'trial', 'overdue', 'grace_period', 'grace', 'suspended', 'pending'])
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

    private function resolvePreviousPeriodEnd(TenantSubscription $subscription): Carbon
    {
        $end = $subscription->current_period_end instanceof Carbon
            ? $subscription->current_period_end->copy()
            : Carbon::parse($subscription->current_period_end ?? now());

        return $subscription->billing_cycle === 'annual'
            ? $end->copy()->subYear()
            : $end->copy()->subMonth();
    }

    private function hasOtherPaidCoveringInvoice(
        TenantInvoice $invoice,
        Tenant $tenant,
        ?TenantSubscription $subscription,
    ): bool {
        $query = TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', '!=', $invoice->id)
            ->where('document_type', 'invoice')
            ->where('status', 'paid');

        if ($subscription) {
            $query->whereHas('lineItems', function ($lineQuery) use ($subscription): void {
                $lineQuery->where('item_type', 'subscription')
                    ->where('related_model_type', TenantSubscription::class)
                    ->where('related_model_id', $subscription->id);
            });
        } else {
            $query->where(function ($inner) {
                $inner->where('is_recurring', true)
                    ->orWhereHas('lineItems', fn ($lineQuery) => $lineQuery->where('item_type', 'subscription'));
            });
        }

        return $query->exists();
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

    private function restrictAccessPolicy(Tenant $tenant): void
    {
        TenantAccessControl::query()->create([
            'tenant_id' => $tenant->id,
            'level' => 'restricted',
            'restrict_login' => true,
            'disabled_modules' => [],
            'effective_from' => now(),
            'effective_until' => null,
            'notes' => __('Access restricted automatically after payment reversal.'),
        ]);
    }
}
