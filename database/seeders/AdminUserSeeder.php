<?php

namespace Database\Seeders;

use App\Domain\Rbac\ActiveRoleService;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Prady Admin',
                'email' => env('ADMIN_EMAIL', 'admin@pradytecai.test'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'super_admin' => true,
            ],
            [
                'name' => 'Ops Admin',
                'email' => env('OPS_ADMIN_EMAIL', 'ops@pradytecai.test'),
                'password' => env('OPS_ADMIN_PASSWORD', 'password'),
                'super_admin' => true,
            ],
        ];

        $superAdmin = Role::query()
            ->where('code', config('rbac.super_admin_role_code', 'super_admin'))
            ->first();

        if (! $superAdmin) {
            $this->command?->warn(__('Super Admin role missing — run RbacBootstrapSeeder first.'));

            return;
        }

        $activeRoleService = app(ActiveRoleService::class);
        $grantElevation = (bool) config('rbac.bootstrap_grant_elevation', true);

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'password_changed_at' => now(),
                    'email_verified_at' => now(),
                ],
            );

            if (! $account['super_admin']) {
                continue;
            }

            $assignment = UserRoleAssignment::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $superAdmin->id,
                    'scope_type' => RoleScopeType::Global,
                ],
                [
                    'status' => UserRoleAssignmentStatus::Active,
                    'assignment_reason' => 'Bootstrap admin user',
                ],
            );

            $activeRoleService->setActive(
                $user,
                $assignment,
                null,
                $grantElevation ? now() : null,
            );

            $this->command?->info(__('Login: :email / :password (Super Admin)', [
                'email' => $account['email'],
                'password' => $account['password'],
            ]));
        }
    }
}
