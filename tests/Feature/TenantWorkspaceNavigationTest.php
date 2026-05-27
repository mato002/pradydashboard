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

class TenantWorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_page_tenant_show_renders_persistent_workspace_shell(): void
    {
        $user = $this->userWithTenantAccess();
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant))
            ->assertOk()
            ->assertSee('id="tenant-workspace-root"', false)
            ->assertSee('tenant-workspace-panel', false)
            ->assertSee(__('Billing'))
            ->assertSee(__('Infrastructure'));
    }

    public function test_partial_request_returns_workspace_panel_only(): void
    {
        $user = $this->userWithTenantAccess();
        $tenant = $this->makeTenant();

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant-Workspace', '1')
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing']));

        $response->assertOk()
            ->assertHeader('X-Tenant-Workspace', 'partial')
            ->assertSee('id="tenant-workspace-panel"', false)
            ->assertDontSee('tenant-workspace-root', false)
            ->assertDontSee('prady-shell', false);
    }

    public function test_ajax_request_preserves_authorization(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'overview']))
            ->assertForbidden();
    }

    public function test_direct_tab_urls_still_render_active_workspace(): void
    {
        $user = $this->userWithTenantAccess();
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'support']))
            ->assertOk()
            ->assertSee('data-tenant-tab="support"', false);
    }

    public function test_invalid_tab_falls_back_to_overview(): void
    {
        $user = $this->userWithTenantAccess();
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->withHeader('X-Tenant-Workspace', '1')
            ->get(route('tenants.show', ['tenant' => $tenant, 'tab' => 'not-a-real-tab']))
            ->assertOk()
            ->assertSee('data-tenant-tab="overview"', false);
    }

    private function makeTenant(): Tenant
    {
        $project = HostedProject::query()->create([
            'name' => 'Workspace App',
            'domain' => 'workspace.test',
        ]);

        return Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Workspace Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
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
