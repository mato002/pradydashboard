<?php

namespace App\Http\Controllers\Concerns;

use App\Domain\Rbac\RbacScopeFilter;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;

trait AuthorizesRbacScope
{
    protected function rbacScope(): RbacScopeFilter
    {
        return app(RbacScopeFilter::class);
    }

    protected function authorizeTenantRbac(Tenant $tenant, string $ability = 'view'): void
    {
        $this->authorize($ability, $tenant);
        $this->rbacScope()->assertCanAccessTenant($tenant);
    }

    protected function authorizeTenantSubscriptionRbac(
        Tenant $tenant,
        TenantProjectSubscription $subscription,
        string $ability = 'update',
    ): void {
        $this->authorize($ability, $tenant);
        abort_unless($subscription->tenant_id === $tenant->id, 404);
        $this->rbacScope()->assertCanAccessSubscription($subscription);
    }
}
