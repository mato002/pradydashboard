<?php

namespace Tests\Feature;

use App\Models\HostedProject;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PradyWorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_request_returns_workspace_content_only(): void
    {
        $user = $this->userWithDashboardAccess();

        $this->actingAs($user)
            ->withHeader('X-Prady-Workspace', '1')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="prady-workspace-content"', false)
            ->assertDontSee('x-data="pradyShell()"', false);
    }

    public function test_sidebar_target_pages_support_partial_rendering(): void
    {
        $user = $this->userWithTenantAccess();

        $this->actingAs($user)
            ->withHeader('X-Prady-Workspace', '1')
            ->get(route('tenants.index'))
            ->assertOk()
            ->assertSee('id="prady-workspace-content"', false);
    }

    public function test_unauthorized_partial_request_is_rejected(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('X-Prady-Workspace', '1')
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_tenant_tab_partial_still_returns_panel_only(): void
    {
        $user = $this->userWithTenantAccess();
        $project = HostedProject::query()->create([
            'name' => 'Panel App',
            'domain' => 'panel.test',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Panel Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant-Workspace', '1')
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing']))
            ->assertOk()
            ->assertSee('id="tenant-workspace-panel"', false)
            ->assertDontSee('id="prady-workspace-content"', false);
    }

    private function userWithDashboardAccess(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Dashboard Viewer',
            'code' => 'dashboard_viewer',
            'status' => 'active',
        ]);
        $permission = Permission::query()->firstOrCreate(
            ['code' => 'dashboard.view'],
            ['name' => 'View dashboard', 'group' => 'system'],
        );
        $role->permissions()->sync([$permission->id]);
        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        return $user;
    }

    private function userWithTenantAccess(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Tenant Viewer',
            'code' => 'tenant_viewer',
            'status' => 'active',
        ]);
        $permission = Permission::query()->firstOrCreate(
            ['code' => 'tenants.view'],
            ['name' => 'View tenants', 'group' => 'tenants'],
        );
        $role->permissions()->sync([$permission->id]);
        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        return $user;
    }
}
