<?php

namespace App\Domain\Rbac;

use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;

class RbacGuard
{
    public function __construct(
        private readonly RolePermissionResolver $resolver,
        private readonly PermissionMatcher $matcher,
        private readonly ActiveRoleService $activeRoleService,
    ) {}

    public function can(User $user, string $permission, array $scope = []): bool
    {
        if (config('rbac.legacy_open_access')) {
            return true;
        }

        $activeRecord = $this->activeRoleService->getActiveRecord($user);
        $assignment = $this->activeRoleService->getActiveAssignment($user);

        if (! $assignment || ! $activeRecord) {
            return false;
        }

        if (! $this->sessionMatches($activeRecord)) {
            $this->activeRoleService->clearActive($user);

            return false;
        }

        if ($this->isActiveSuperAdmin($assignment)) {
            if ($user->isHardcodedSuperuser() && config('superuser.bypass_elevation', true)) {
                return true;
            }

            return $this->hasValidElevationForRole($activeRecord, $assignment);
        }

        if (! $assignment->isActivatable()) {
            return false;
        }

        if (! $this->hasValidElevationForRole($activeRecord, $assignment)) {
            return false;
        }

        $codes = $this->resolver->resolvePermissionCodes($assignment->role);

        if (! $this->matcher->anyMatches($codes, $permission)) {
            return false;
        }

        if ($assignment->scope_type !== RoleScopeType::Global && $this->isGlobalOnlyPermission($permission)) {
            return false;
        }

        return $this->permissionAllowedInScope($permission, $assignment, $scope);
    }

    public function isActiveSuperAdmin(?UserRoleAssignment $assignment = null): bool
    {
        $user = auth()->user();
        if (! $assignment && $user instanceof User) {
            $assignment = $this->activeRoleService->getActiveAssignment($user);
        }

        return $assignment
            && $assignment->role?->isSuperAdmin()
            && $assignment->isActivatable();
    }

    private function hasValidElevationForRole(UserActiveRole $activeRecord, UserRoleAssignment $assignment): bool
    {
        if (! $assignment->role?->requires_elevation) {
            return true;
        }

        return $activeRecord->hasValidElevation();
    }

    private function isGlobalOnlyPermission(string $permission): bool
    {
        foreach (config('rbac.global_only_permissions', []) as $prefix) {
            if (str_starts_with($permission, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function permissionAllowedInScope(string $permission, UserRoleAssignment $assignment, array $scope): bool
    {
        if ($assignment->scope_type === RoleScopeType::Global) {
            return true;
        }

        $groups = config('rbac.permission_groups', []);

        foreach ($groups as $scopeType => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($permission, $prefix)) {
                    if ($this->scopeContextEmpty($scope)) {
                        return $this->assignmentMatchesScopeType($assignment, $scopeType);
                    }

                    return $this->scopeMatches($assignment, $scopeType, $scope);
                }
            }
        }

        return false;
    }

    private function sessionMatches(UserActiveRole $activeRecord): bool
    {
        if (! filled($activeRecord->session_id)) {
            return true;
        }

        $current = session()->getId();

        return filled($current) && hash_equals($activeRecord->session_id, $current);
    }

    /** @param  array<string, mixed>  $scope */
    private function scopeContextEmpty(array $scope): bool
    {
        return collect($scope)->filter(fn ($v) => $v !== null && $v !== '')->isEmpty();
    }

    private function assignmentMatchesScopeType(UserRoleAssignment $assignment, string $scopeType): bool
    {
        return match ($scopeType) {
            'tenant' => $assignment->scope_type === RoleScopeType::Tenant,
            'project' => $assignment->scope_type === RoleScopeType::Project,
            'server' => $assignment->scope_type === RoleScopeType::Server,
            default => false,
        };
    }

    private function scopeMatches(UserRoleAssignment $assignment, string $expectedScopeType, array $scope): bool
    {
        return match ($expectedScopeType) {
            'tenant' => $assignment->scope_type === RoleScopeType::Tenant
                && isset($scope['tenant_id'])
                && (int) $scope['tenant_id'] === (int) $assignment->tenant_id,
            'project' => $assignment->scope_type === RoleScopeType::Project
                && isset($scope['project_id'])
                && (int) $scope['project_id'] === (int) $assignment->project_id,
            'server' => $assignment->scope_type === RoleScopeType::Server
                && isset($scope['server_id'])
                && (int) $scope['server_id'] === (int) $assignment->server_id,
            default => false,
        };
    }
}
