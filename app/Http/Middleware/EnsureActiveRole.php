<?php

namespace App\Http\Middleware;

use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\LoginRoleActivationService;
use App\Models\User;
use App\Models\UserActiveRole;
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

        if ($user instanceof User) {
            $record = app(ActiveRoleService::class)->getActiveRecord($user);

            if (! $record) {
                $this->activatePreferredRole($user);
            } else {
                $this->rebindSuperAdminSessionIfStale($user, $record);
            }
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

    private function rebindSuperAdminSessionIfStale(User $user, UserActiveRole $record): void
    {
        $sessionId = session()->getId();

        if (! filled($record->session_id) || ! filled($sessionId)) {
            return;
        }

        if (hash_equals($record->session_id, $sessionId)) {
            return;
        }

        $assignment = $record->assignment;

        if (! $assignment?->role?->isSuperAdmin() || ! $assignment->isActivatable()) {
            return;
        }

        app(ActiveRoleService::class)->setActive(
            $user,
            $assignment,
            $sessionId,
            $record->elevation_verified_at,
        );
    }
}
