<?php

namespace App\Domain\Tenancy;

use App\Models\ProjectModule;
use App\Models\Tenant;
use App\Models\TenantProjectModuleSubscription;
use App\Models\TenantProjectSubscription;
use Illuminate\Support\Collection;

class TenantProjectModuleMatrix
{
    /**
     * @return Collection<int, array{module: ProjectModule, row: ?TenantProjectModuleSubscription, enabled: bool, subscribed: bool}>
     */
    public function rows(TenantProjectSubscription $subscription): Collection
    {
        $subscription->loadMissing(['project.modules', 'moduleSubscriptions']);

        $project = $subscription->project;
        if (! $project) {
            return collect();
        }

        return $project->modules
            ->sortBy('name')
            ->values()
            ->map(function (ProjectModule $module) use ($subscription): array {
                $row = $subscription->moduleSubscriptions->firstWhere('project_module_id', $module->id);

                return [
                    'module' => $module,
                    'row' => $row,
                    'enabled' => $row ? (bool) $row->enabled : (bool) $module->default_enabled,
                    'subscribed' => $row ? (bool) $row->subscribed : false,
                ];
            });
    }

    /**
     * @return array{enabled: int, subscribed: int, billing_total: float, currency: string}
     */
    public function statsForTenant(Tenant $tenant): array
    {
        $tenant->loadMissing([
            'projectSubscriptions.moduleSubscriptions.projectModule',
            'projectSubscriptions.project',
        ]);

        $rows = $tenant->projectSubscriptions->flatMap->moduleSubscriptions;
        $currency = $tenant->tenant_currency
            ?? $tenant->projectSubscriptions->first()?->currency
            ?? 'KES';

        $billingTotal = $rows
            ->filter(fn (TenantProjectModuleSubscription $m) => $m->enabled && $m->subscribed)
            ->sum(function (TenantProjectModuleSubscription $m): float {
                if ($m->monthly_price_override !== null) {
                    return (float) $m->monthly_price_override;
                }

                return (float) ($m->projectModule?->monthly_price ?? 0);
            });

        return [
            'enabled' => $rows->where('enabled', true)->count(),
            'subscribed' => $rows->where('subscribed', true)->count(),
            'billing_total' => (float) $billingTotal,
            'currency' => $currency,
        ];
    }
}
