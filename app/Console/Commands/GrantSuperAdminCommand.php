<?php

namespace App\Console\Commands;

use App\Domain\Rbac\ActiveRoleService;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Console\Command;

class GrantSuperAdminCommand extends Command
{
    protected $signature = 'rbac:grant-super-admin {email? : Grant only this user email; omit to grant all users missing Super Admin}';

    protected $description = 'Assign the Super Admin role to users in the database (user_role_assignments)';

    public function handle(ActiveRoleService $activeRoleService): int
    {
        $code = config('rbac.super_admin_role_code') ?: 'super_admin';
        $superAdmin = Role::query()->where('code', $code)->first();

        if (! $superAdmin) {
            $this->error(__('Run php artisan db:seed --class=RbacBootstrapSeeder first to create permissions and the super_admin role.'));

            return self::FAILURE;
        }

        $superAdmin->permissions()->sync(
            \App\Models\Permission::query()->pluck('id')
        );

        $query = User::query()->orderBy('id');
        if ($email = $this->argument('email')) {
            $query->where('email', $email);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn(__('No matching users found.'));

            return self::FAILURE;
        }

        $granted = 0;

        foreach ($users as $user) {
            $exists = UserRoleAssignment::query()
                ->where('user_id', $user->id)
                ->where('role_id', $superAdmin->id)
                ->where('scope_type', RoleScopeType::Global)
                ->where('status', UserRoleAssignmentStatus::Active)
                ->exists();

            if ($exists) {
                $this->line("  skip {$user->email} — already has Super Admin");

                continue;
            }

            $assignment = UserRoleAssignment::query()->create([
                'user_id' => $user->id,
                'role_id' => $superAdmin->id,
                'scope_type' => RoleScopeType::Global,
                'status' => UserRoleAssignmentStatus::Active,
                'assignment_reason' => 'Granted via rbac:grant-super-admin',
            ]);

            $activeRoleService->setActive($user, $assignment, null, now());
            $this->info("  granted Super Admin → {$user->email}");
            $granted++;
        }

        $this->newLine();
        $this->info(__('Done. :count user(s) updated. Log out and log in again to refresh the active role.', ['count' => $granted]));

        return self::SUCCESS;
    }
}
