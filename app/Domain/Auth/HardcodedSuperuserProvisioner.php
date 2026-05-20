<?php

namespace App\Domain\Auth;

use App\Domain\Rbac\ActiveRoleService;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Support\Facades\Hash;

class HardcodedSuperuserProvisioner
{
    public function __construct(
        private readonly ActiveRoleService $activeRoleService,
    ) {}

    public function provision(): User
    {
        $email = (string) config('superuser.email');
        $password = (string) config('superuser.password');

        $user = User::query()->firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->name = (string) config('superuser.name', 'Super User');

        if ($isNew || ! Hash::check($password, (string) $user->password)) {
            $user->password = Hash::make($password);
        }

        if ($isNew) {
            $user->email_verified_at = now();
            $user->password_changed_at = now();
        }

        $user->save();

        $superAdmin = Role::query()
            ->where('code', config('rbac.super_admin_role_code') ?: 'super_admin')
            ->first();

        if (! $superAdmin) {
            return $user;
        }

        $assignment = UserRoleAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $superAdmin->id,
                'scope_type' => RoleScopeType::Global,
                'tenant_id' => null,
                'project_id' => null,
                'server_id' => null,
            ],
            [
                'status' => UserRoleAssignmentStatus::Active,
                'assignment_reason' => 'Hardcoded Super Admin',
            ]
        );

        $this->activeRoleService->setActive($user, $assignment, null, now());

        return $user->fresh();
    }

    public function ensureActiveRole(User $user): void
    {
        if (! $user->isHardcodedSuperuser()) {
            return;
        }

        $this->provision();
    }
}
