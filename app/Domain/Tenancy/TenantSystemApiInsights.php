<?php

namespace App\Domain\Tenancy;

use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use Illuminate\Support\Collection;

class TenantSystemApiInsights
{
    /**
     * @return array<string, mixed>
     */
    public function forSubscription(TenantProjectSubscription $subscription): array
    {
        $apis = $this->tenantSystemApis($subscription);
        $primary = $apis->first();
        $version = $subscription->versionTracking;

        return [
            'configured' => $apis->isNotEmpty() && $apis->contains(fn (TenantProjectServiceIntegration $a) => filled($a->endpoint_url)),
            'apis' => $apis,
            'primary' => $primary,
            'current_version' => $version?->current_version,
            'last_heartbeat' => $apis->where('purpose', 'heartbeat')->max('last_success_at')
                ?? $apis->max('last_success_at'),
            'last_api_check' => $apis->max('last_checked_at'),
            'health_status' => $this->resolveHealthStatus($apis),
            'contract_health' => $this->resolveContractHealth($apis),
            'contract_health_label' => $this->resolveContractHealthLabel($apis),
            'usage_summary' => $this->resolveUsageSummary($apis),
            'last_error' => $apis->pluck('last_error')->filter()->first(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $tenant->loadMissing(['projectSubscriptions.versionTracking', 'projectSubscriptions.serviceIntegrations']);

        $subscription = $tenant->projectSubscriptions->firstWhere('product_status', 'active')
            ?? $tenant->projectSubscriptions->first();

        if (! $subscription) {
            return [
                'configured' => false,
                'current_version' => null,
                'last_heartbeat' => null,
                'last_api_check' => null,
                'health_status' => __('Not configured'),
                'contract_health' => null,
                'contract_health_label' => null,
                'usage_summary' => null,
                'last_error' => null,
            ];
        }

        return $this->forSubscription($subscription);
    }

    /**
     * @return Collection<int, TenantProjectServiceIntegration>
     */
    public function tenantSystemApis(TenantProjectSubscription $subscription): Collection
    {
        return $subscription->serviceIntegrations
            ->filter(fn (TenantProjectServiceIntegration $i) => $i->isTenantSystem())
            ->values();
    }

    /**
     * @param  Collection<int, TenantProjectServiceIntegration>  $apis
     */
    private function resolveHealthStatus(Collection $apis): string
    {
        if ($apis->isEmpty()) {
            return __('Not configured');
        }

        if ($apis->contains(fn (TenantProjectServiceIntegration $a) => $a->status === 'failing')) {
            return __('Failing');
        }

        if ($apis->contains(fn (TenantProjectServiceIntegration $a) => $a->status === 'active')) {
            return __('Healthy');
        }

        return ucfirst(str_replace('_', ' ', (string) ($apis->first()->status ?? 'unknown')));
    }

    /**
     * @param  Collection<int, TenantProjectServiceIntegration>  $apis
     */
    private function resolveContractHealth(Collection $apis): ?string
    {
        foreach ($apis as $api) {
            if ($health = $api->contractHealth()) {
                return $health;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, TenantProjectServiceIntegration>  $apis
     */
    private function resolveContractHealthLabel(Collection $apis): ?string
    {
        foreach ($apis as $api) {
            if ($label = $api->contractHealthLabel()) {
                return $label;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, TenantProjectServiceIntegration>  $apis
     */
    private function resolveUsageSummary(Collection $apis): ?array
    {
        foreach ($apis as $api) {
            $usage = $api->safeUsageSummary();
            if ($usage !== null && $usage !== []) {
                return $usage;
            }
        }

        return null;
    }
}
