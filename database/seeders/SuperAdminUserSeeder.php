<?php

namespace Database\Seeders;

use App\Domain\Rbac\ActiveRoleService;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Database\Seeder;

class SuperAdminUserSeeder extends Seeder
{
    private const EMAIL = 'superadmin@pradytecai.test';

    private const PASSWORD = 'dashboard@1024';

    public function run(): void
    {
        $this->call(RbacBootstrapSeeder::class);

        $superAdminCode = config('rbac.super_admin_role_code') ?: 'super_admin';
        $superAdminRole = Role::query()->where('code', $superAdminCode)->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Super Admin',
                'password' => self::PASSWORD,
                'password_changed_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        $assignment = UserRoleAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $superAdminRole->id,
                'scope_type' => RoleScopeType::Global,
            ],
            [
                'tenant_id' => null,
                'project_id' => null,
                'server_id' => null,
                'status' => UserRoleAssignmentStatus::Active,
                'assignment_reason' => 'Seeded Super Admin user',
            ]
        );

        if ($assignment->status !== UserRoleAssignmentStatus::Active) {
            $assignment->update(['status' => UserRoleAssignmentStatus::Active]);
        }

        app(ActiveRoleService::class)->setActive(
            $user,
            $assignment,
            null,
            now(),
        );
    }
}
