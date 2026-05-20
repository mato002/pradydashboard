<?php

namespace App\Domain\Tenancy;

use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use Illuminate\Support\Collection;

class TenantIntegrationInsights
{
    /**
     * @return array{active: int, failing: int, not_tested: int, total: int, provider_total: int, tenant_system_total: int}
     */
    public function summaryForTenant(Tenant $tenant): array
    {
        $integrations = $this->allIntegrations($tenant);
        $providers = $integrations->filter(fn (TenantProjectServiceIntegration $i) => $i->isProvider());
        $tenantSystem = $integrations->filter(fn (TenantProjectServiceIntegration $i) => $i->isTenantSystem());

        return [
            'total' => $integrations->count(),
            'active' => $integrations->where('status', 'active')->count(),
            'failing' => $integrations->filter(fn (TenantProjectServiceIntegration $i) => $i->status === 'failing' || $i->last_test_status === 'fail')->count(),
            'not_tested' => $integrations->whereNull('last_checked_at')->count(),
            'provider_total' => $providers->count(),
            'tenant_system_total' => $tenantSystem->count(),
        ];
    }

    /**
     * @return Collection<int, TenantProjectServiceIntegration>
     */
    public function allIntegrations(Tenant $tenant): Collection
    {
        $tenant->loadMissing('projectSubscriptions.serviceIntegrations');

        return $tenant->projectSubscriptions->flatMap->serviceIntegrations;
    }
}
