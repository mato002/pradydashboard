<?php

namespace Database\Seeders;

use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\PermissionRegistry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\RoleStatus;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Database\Seeder;

class RbacBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionRegistry::definitions() as $definition) {
            Permission::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'group' => $definition['group'],
                ]
            );
        }

        $superAdminCode = config('rbac.super_admin_role_code') ?: 'super_admin';

        $superAdmin = Role::query()->updateOrCreate(
            ['code' => $superAdminCode],
            [
                'name' => 'Super Admin',
                'description' => 'System owner with full internal dashboard access.',
                'status' => RoleStatus::Active,
                'is_system' => true,
                'requires_elevation' => true,
                'elevation_methods' => ['password'],
                'notes' => 'Bootstrap system role. Do not delete.',
            ]
        );

        $superAdmin->permissions()->sync(Permission::query()->pluck('id'));

        $this->grantSuperAdminToExistingUsers($superAdmin);
    }

    private function grantSuperAdminToExistingUsers(Role $superAdmin): void
    {
        $activeRoleService = app(ActiveRoleService::class);
        $grantElevation = (bool) config('rbac.bootstrap_grant_elevation', true);

        foreach (User::query()->orderBy('id')->get() as $user) {
            $hasAssignment = UserRoleAssignment::query()
                ->where('user_id', $user->id)
                ->where('role_id', $superAdmin->id)
                ->where('scope_type', RoleScopeType::Global)
                ->where('status', UserRoleAssignmentStatus::Active)
                ->exists();

            if ($hasAssignment) {
                continue;
            }

            $assignment = UserRoleAssignment::query()->create([
                'user_id' => $user->id,
                'role_id' => $superAdmin->id,
                'scope_type' => RoleScopeType::Global,
                'tenant_id' => null,
                'project_id' => null,
                'server_id' => null,
                'status' => UserRoleAssignmentStatus::Active,
                'assignment_reason' => 'Bootstrap Super Admin assignment',
            ]);

            if (! $activeRoleService->getActiveRecord($user)) {
                $activeRoleService->setActive(
                    $user,
                    $assignment,
                    null,
                    $grantElevation ? now() : null,
                );
            }
        }
    }
}
