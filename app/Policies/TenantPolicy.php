<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Rbac\Rbac;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return Rbac::can('tenants.view');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return Rbac::can('tenants.view', ['tenant_id' => $tenant->id]);
    }

    public function create(User $user): bool
    {
        return Rbac::can('tenants.create');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return Rbac::can('tenants.update', ['tenant_id' => $tenant->id]);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return Rbac::can('tenants.update', ['tenant_id' => $tenant->id]);
    }

    public function suspend(User $user, Tenant $tenant): bool
    {
        return Rbac::can('tenants.suspend', ['tenant_id' => $tenant->id]);
    }
}
