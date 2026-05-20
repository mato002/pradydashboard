<?php

namespace App\Domain\Rbac;

use App\Domain\Activity\ActivityLogger;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\ActivityLogCategory;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserRoleAssignmentService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly RbacGuard $rbacGuard,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function assign(User $actor, User $target, Role $role, array $data): UserRoleAssignment
    {
        if ($role->isSuperAdmin() && ! $this->rbacGuard->isActiveSuperAdmin($actor->activeRoleAssignment)) {
            throw ValidationException::withMessages([
                'role_id' => __('Only a Super Admin can assign the Super Admin role.'),
            ]);
        }

        if ($actor->id === $target->id && $role->isSuperAdmin() && ! $this->rbacGuard->isActiveSuperAdmin()) {
            throw ValidationException::withMessages([
                'role_id' => __('You cannot assign Super Admin to yourself.'),
            ]);
        }

        $scopeType = $data['scope_type'] ?? RoleScopeType::Global->value;
        $tenantId = $data['tenant_id'] ?? null;
        $projectId = $data['project_id'] ?? null;
        $serverId = $data['server_id'] ?? null;

        if ($this->duplicateActiveAssignmentExists($target->id, $role->id, $scopeType, $tenantId, $projectId, $serverId)) {
            throw ValidationException::withMessages([
                'role_id' => __('This user already has an active assignment for this role and scope.'),
            ]);
        }

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $target->id,
            'role_id' => $role->id,
            'scope_type' => $data['scope_type'] ?? RoleScopeType::Global->value,
            'tenant_id' => $data['tenant_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'server_id' => $data['server_id'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => UserRoleAssignmentStatus::Active,
            'assigned_by' => $actor->id,
            'assignment_reason' => $data['assignment_reason'] ?? null,
        ]);

        $this->activityLogger->log(
            'rbac.assignment.created',
            ActivityLogCategory::SYSTEM,
            __('Assigned role :role to :user', ['role' => $role->name, 'user' => $target->name]),
            $assignment,
            null,
            $assignment->only(['scope_type', 'tenant_id', 'project_id', 'server_id', 'expires_at']),
        );

        return $assignment;
    }

    public function revoke(User $actor, UserRoleAssignment $assignment, ?string $reason = null): void
    {
        if ($assignment->role?->isSuperAdmin()) {
            $activeSuperAdmins = UserRoleAssignment::query()
                ->where('role_id', $assignment->role_id)
                ->where('status', UserRoleAssignmentStatus::Active)
                ->where('id', '!=', $assignment->id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();

            if ($activeSuperAdmins === 0) {
                throw ValidationException::withMessages([
                    'assignment' => __('Cannot revoke the last Super Admin assignment.'),
                ]);
            }
        }

        DB::transaction(function () use ($actor, $assignment, $reason) {
            $assignment->update([
                'status' => UserRoleAssignmentStatus::Revoked,
                'revoked_by' => $actor->id,
                'revoked_at' => now(),
                'revoke_reason' => $reason,
            ]);

            if ($assignment->user?->activeRoleRecord?->user_role_assignment_id === $assignment->id) {
                $assignment->user->activeRoleRecord?->delete();
            }
        });

        $this->activityLogger->log(
            'rbac.assignment.revoked',
            ActivityLogCategory::SYSTEM,
            __('Revoked role assignment for :user', ['user' => $assignment->user?->name]),
            $assignment,
            ['status' => UserRoleAssignmentStatus::Active->value],
            ['status' => UserRoleAssignmentStatus::Revoked->value, 'reason' => $reason],
        );
    }

    private function duplicateActiveAssignmentExists(
        int $userId,
        int $roleId,
        string $scopeType,
        mixed $tenantId,
        mixed $projectId,
        mixed $serverId,
    ): bool {
        return UserRoleAssignment::query()
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('scope_type', $scopeType)
            ->where('status', UserRoleAssignmentStatus::Active)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when(
                $scopeType === RoleScopeType::Global->value,
                fn ($q) => $q->whereNull('tenant_id')->whereNull('project_id')->whereNull('server_id'),
                fn ($q) => $q
                    ->when($tenantId !== null, fn ($q2) => $q2->where('tenant_id', $tenantId), fn ($q2) => $q2->whereNull('tenant_id'))
                    ->when($projectId !== null, fn ($q2) => $q2->where('project_id', $projectId), fn ($q2) => $q2->whereNull('project_id'))
                    ->when($serverId !== null, fn ($q2) => $q2->where('server_id', $serverId), fn ($q2) => $q2->whereNull('server_id'))
            )
            ->exists();
    }
}
