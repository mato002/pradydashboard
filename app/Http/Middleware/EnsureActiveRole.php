<?php

namespace App\Http\Middleware;

use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\LoginRoleActivationService;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! app(ActiveRoleService::class)->getActiveRecord($user)) {
            $this->activatePreferredRole($user);
        }

        return $next($request);
    }

    private function activatePreferredRole(User $user): void
    {
        $superAdminCode = config('rbac.super_admin_role_code') ?: 'super_admin';

        $hasSuperAdmin = UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->where('status', UserRoleAssignmentStatus::Active)
            ->where('scope_type', RoleScopeType::Global)
            ->whereHas('role', fn ($q) => $q->where('code', $superAdminCode))
            ->exists();

        if ($hasSuperAdmin) {
            app(LoginRoleActivationService::class)->activateForSession($user, false);
        }
    }
}
