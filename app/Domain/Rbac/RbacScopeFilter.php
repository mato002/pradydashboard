<?php

namespace App\Domain\Rbac;

use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RbacScopeFilter
{
    public function __construct(
        private readonly ActiveRoleService $activeRoleService,
    ) {}

    public function assignment(): ?UserRoleAssignment
    {
        $user = Auth::user();

        return $user instanceof User
            ? $this->activeRoleService->getActiveAssignment($user)
            : null;
    }

    public function isGlobalScope(): bool
    {
        $assignment = $this->assignment();

        return $assignment === null || $assignment->scope_type === RoleScopeType::Global;
    }

    public function hasScopedRestriction(): bool
    {
        return ! $this->isGlobalScope();
    }

    public function applyTenantScope(Builder $query, string $column = 'id'): Builder
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $query;
        }

        return match ($assignment->scope_type) {
            RoleScopeType::Tenant => $query->where($column, $assignment->tenant_id),
            RoleScopeType::Project => $query->where('hosted_project_id', $assignment->project_id),
            RoleScopeType::Server => $query->where('server_id', $assignment->server_id),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function applyProjectScope(Builder $query, string $column = 'id'): Builder
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $query;
        }

        return match ($assignment->scope_type) {
            RoleScopeType::Project => $query->where($column, $assignment->project_id),
            RoleScopeType::Tenant => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('hosted_project_id')
                    ->from('tenants')
                    ->where('id', $assignment->tenant_id)
                    ->whereNotNull('hosted_project_id');
            }),
            RoleScopeType::Server => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('id')
                    ->from('hosted_projects')
                    ->where('server_id', $assignment->server_id);
            }),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function applyServerScope(Builder $query, string $column = 'id'): Builder
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $query;
        }

        return match ($assignment->scope_type) {
            RoleScopeType::Server => $query->where($column, $assignment->server_id),
            RoleScopeType::Tenant => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('server_id')
                    ->from('tenants')
                    ->where('id', $assignment->tenant_id)
                    ->whereNotNull('server_id');
            }),
            RoleScopeType::Project => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('server_id')
                    ->from('hosted_projects')
                    ->where('id', $assignment->project_id)
                    ->whereNotNull('server_id');
            }),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function applyTenantForeignScope(Builder $query, string $column = 'tenant_id'): Builder
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $query;
        }

        return match ($assignment->scope_type) {
            RoleScopeType::Tenant => $query->where($column, $assignment->tenant_id),
            RoleScopeType::Project => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('id')->from('tenants')->where('hosted_project_id', $assignment->project_id);
            }),
            RoleScopeType::Server => $query->whereIn($column, function ($sub) use ($assignment) {
                $sub->select('id')->from('tenants')->where('server_id', $assignment->server_id);
            }),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function applyActivityLogScope(Builder $query): Builder
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $query;
        }

        return match ($assignment->scope_type) {
            RoleScopeType::Tenant => $query->where('tenant_id', $assignment->tenant_id),
            RoleScopeType::Project => $query->where('hosted_project_id', $assignment->project_id),
            RoleScopeType::Server => $query->where('server_id', $assignment->server_id),
            default => $query->whereRaw('0 = 1'),
        };
    }

    public function assertCanAccessTenant(Tenant|int $tenant): void
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return;
        }

        $allowed = match ($assignment->scope_type) {
            RoleScopeType::Tenant => (int) $assignment->tenant_id === (int) $tenantId,
            RoleScopeType::Project => Tenant::query()
                ->where('id', $tenantId)
                ->where('hosted_project_id', $assignment->project_id)
                ->exists(),
            RoleScopeType::Server => Tenant::query()
                ->where('id', $tenantId)
                ->where('server_id', $assignment->server_id)
                ->exists(),
            default => false,
        };

        abort_unless($allowed, 403);
    }

    public function assertCanAccessSubscription(TenantProjectSubscription $subscription): void
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return;
        }

        $subscription->loadMissing(['tenant', 'infrastructure']);

        $allowed = match ($assignment->scope_type) {
            RoleScopeType::Tenant => (int) $subscription->tenant_id === (int) $assignment->tenant_id,
            RoleScopeType::Project => (int) $subscription->tenant?->hosted_project_id === (int) $assignment->project_id,
            RoleScopeType::Server => (int) (
                $subscription->infrastructure?->server_id
                ?? $subscription->tenant?->server_id
            ) === (int) $assignment->server_id,
            default => false,
        };

        abort_unless($allowed, 403);
    }

    public function assertCanAccessServer(?int $serverId): void
    {
        if ($serverId === null) {
            return;
        }

        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return;
        }

        if ($assignment->scope_type === RoleScopeType::Server) {
            abort_unless((int) $assignment->server_id === (int) $serverId, 403);
        }
    }

    /**
     * @param  Collection<int, object|array<string, mixed>>  $risks
     * @return Collection<int, object|array<string, mixed>>
     */
    public function filterRiskCollection(Collection $risks): Collection
    {
        $assignment = $this->assignment();

        if (! $assignment || $assignment->scope_type === RoleScopeType::Global) {
            return $risks;
        }

        return $risks->filter(function ($risk) use ($assignment) {
            $row = is_array($risk) ? $risk : (array) $risk;

            return match ($assignment->scope_type) {
                RoleScopeType::Tenant => isset($row['tenant_id']) && (int) $row['tenant_id'] === (int) $assignment->tenant_id,
                RoleScopeType::Project => isset($row['project_id']) && (int) $row['project_id'] === (int) $assignment->project_id,
                RoleScopeType::Server => isset($row['server_id']) && (int) $row['server_id'] === (int) $assignment->server_id,
                default => false,
            };
        })->values();
    }
}
