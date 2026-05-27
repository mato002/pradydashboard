<?php

namespace Tests\Feature;

use App\Models\HostedProject;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_status_can_be_updated_from_directory(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = $this->userWithTenantUpdate();
        $project = HostedProject::query()->create([
            'name' => 'Status App',
            'domain' => 'status.test',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Status Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'overdue',
        ]);

        $this->actingAs($user)
            ->from(route('tenants.index'))
            ->patch(route('tenants.status.update', $tenant), ['status' => 'active'])
            ->assertRedirect(route('tenants.index'))
            ->assertSessionHas('status');

        $this->assertSame('active', $tenant->fresh()->status);
    }

    public function test_unauthorized_user_cannot_update_tenant_status(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $project = HostedProject::query()->create([
            'name' => 'Locked App',
            'domain' => 'locked.test',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Locked Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'trial',
        ]);

        $this->actingAs($user)
            ->patch(route('tenants.status.update', $tenant), ['status' => 'active'])
            ->assertForbidden();
    }

    private function userWithTenantUpdate(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Tenant Admin',
            'code' => 'tenant_admin',
            'status' => 'active',
        ]);
        $permission = Permission::query()->firstOrCreate(
            ['code' => 'tenants.update'],
            ['name' => 'Update tenants', 'group' => 'tenants'],
        );
        $role->permissions()->sync([$permission->id]);
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        return $user;
    }
}
