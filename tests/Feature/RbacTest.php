<?php

namespace Tests\Feature;

use App\Domain\Rbac\PermissionMatcher;
use App\Domain\Rbac\RoleInheritanceValidator;
use App\Domain\Rbac\RolePermissionResolver;
use App\Domain\Rbac\RoleSwitchService;
use App\Domain\Rbac\RbacGuard;
use App\Domain\Rbac\UserRoleAssignmentService;
use App\Models\OperationalDocument;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleSwitchLog;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rbac.legacy_open_access' => false]);
        $this->seed(RbacBootstrapSeeder::class);
    }

    public function test_super_admin_bootstrap_exists(): void
    {
        $role = Role::query()->where('code', config('rbac.super_admin_role_code') ?: 'super_admin')->first();

        $this->assertNotNull($role);
        $this->assertTrue($role->is_system);
        $this->assertTrue($role->requires_elevation);
    }

    public function test_permission_registry_is_seeded(): void
    {
        $this->assertGreaterThan(15, Permission::query()->count());
        $this->assertDatabaseHas('permissions', ['code' => 'dashboard.view']);
        $this->assertDatabaseHas('permissions', ['code' => 'rbac.manage']);
    }

    public function test_role_can_be_created(): void
    {
        $admin = $this->superAdminUser();

        $response = $this->actingAs($admin)->post(route('access-control.roles.store'), [
            'name' => 'Finance Approver',
            'code' => 'finance_approver',
            'status' => 'active',
            'requires_elevation' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['code' => 'finance_approver']);
    }

    public function test_permissions_can_be_assigned_to_role(): void
    {
        $role = Role::query()->create([
            'name' => 'Billing',
            'code' => 'billing_test',
            'status' => 'active',
        ]);

        $permission = Permission::query()->where('code', 'billing.view')->first();
        $role->permissions()->sync([$permission->id]);

        $this->assertTrue($role->permissions()->where('code', 'billing.view')->exists());
    }

    public function test_role_can_inherit_another_role(): void
    {
        $parent = Role::query()->create(['name' => 'Parent', 'code' => 'parent_role', 'status' => 'active']);
        $child = Role::query()->create(['name' => 'Child', 'code' => 'child_role', 'status' => 'active']);
        $permission = Permission::query()->where('code', 'tenants.view')->first();
        $parent->permissions()->sync([$permission->id]);

        $child->parentRoles()->attach($parent->id);

        $resolver = app(RolePermissionResolver::class);
        $this->assertContains('tenants.view', $resolver->resolvePermissionCodes($child));
    }

    public function test_circular_inheritance_is_blocked(): void
    {
        $a = Role::query()->create(['name' => 'A', 'code' => 'role_a', 'status' => 'active']);
        $b = Role::query()->create(['name' => 'B', 'code' => 'role_b', 'status' => 'active']);
        $b->parentRoles()->attach($a->id);

        $this->expectException(\InvalidArgumentException::class);
        app(RoleInheritanceValidator::class)->assertCanInherit($b, $a);
    }

    public function test_user_can_have_multiple_role_assignments(): void
    {
        $user = User::factory()->create();
        $roles = collect([
            Role::query()->create(['name' => 'Role One', 'code' => 'role_one', 'status' => 'active']),
            Role::query()->create(['name' => 'Role Two', 'code' => 'role_two', 'status' => 'active']),
        ]);

        foreach ($roles as $role) {
            UserRoleAssignment::query()->create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'scope_type' => RoleScopeType::Global,
                'status' => UserRoleAssignmentStatus::Active,
            ]);
        }

        $this->assertCount(2, $user->fresh()->roleAssignments);
    }

    public function test_user_can_activate_only_one_role_at_a_time(): void
    {
        $user = User::factory()->create();
        $roleA = Role::query()->create(['name' => 'A', 'code' => 'only_a', 'status' => 'active']);
        $roleB = Role::query()->create(['name' => 'B', 'code' => 'only_b', 'status' => 'active']);

        $assignA = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $roleA->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);
        $assignB = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $roleB->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        app(RoleSwitchService::class)->switch($user, $assignA, 'password');
        app(RoleSwitchService::class)->switch($user, $assignB, 'password');

        $this->assertSame(1, UserActiveRole::query()->where('user_id', $user->id)->count());
        $this->assertSame($assignB->id, $user->fresh()->activeRoleRecord->user_role_assignment_id);
    }

    public function test_expired_role_cannot_be_activated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $role = Role::query()->create(['name' => 'Expired', 'code' => 'expired_role', 'status' => 'active']);
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(ValidationException::class);
        app(RoleSwitchService::class)->switch($user, $assignment);
    }

    public function test_revoked_role_cannot_be_activated(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Revoked', 'code' => 'revoked_role', 'status' => 'active']);
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Revoked,
        ]);

        $this->expectException(ValidationException::class);
        app(RoleSwitchService::class)->switch($user, $assignment);
    }

    public function test_scoped_tenant_role_only_works_for_that_tenant(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Tenant AM', 'code' => 'tenant_am', 'status' => 'active']);
        $role->permissions()->sync(
            Permission::query()->where('code', 'tenants.view')->pluck('id')
        );

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Tenant,
            'tenant_id' => 10,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        $guard = app(RbacGuard::class);
        $this->assertTrue($guard->can($user, 'tenants.view', ['tenant_id' => 10]));
        $this->assertFalse($guard->can($user, 'tenants.view', ['tenant_id' => 99]));
    }

    public function test_sensitive_role_requires_password_confirmation(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $role = Role::query()->where('code', config('rbac.super_admin_role_code') ?: 'super_admin')->first();
        $assignment = UserRoleAssignment::query()->where('user_id', $user->id)->where('role_id', $role->id)->first()
            ?? UserRoleAssignment::query()->create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'scope_type' => RoleScopeType::Global,
                'status' => UserRoleAssignmentStatus::Active,
            ]);

        $this->expectException(ValidationException::class);
        app(RoleSwitchService::class)->switch($user, $assignment, null);
    }

    public function test_role_switch_is_logged(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $role = Role::query()->create(['name' => 'Ops', 'code' => 'ops_role', 'status' => 'active']);
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        app(RoleSwitchService::class)->switch($user, $assignment);

        $this->assertDatabaseHas('role_switch_logs', [
            'user_id' => $user->id,
            'to_assignment_id' => $assignment->id,
        ]);
        $this->assertGreaterThan(0, RoleSwitchLog::query()->where('user_id', $user->id)->count());
    }

    public function test_last_super_admin_cannot_be_revoked(): void
    {
        $admin = $this->superAdminUser();
        $assignment = UserRoleAssignment::query()
            ->where('user_id', $admin->id)
            ->whereHas('role', fn ($q) => $q->where('code', config('rbac.super_admin_role_code') ?: 'super_admin'))
            ->first();

        $this->expectException(ValidationException::class);
        app(UserRoleAssignmentService::class)->revoke($admin, $assignment, 'test');
    }

    public function test_wildcard_permission_matching(): void
    {
        $matcher = app(PermissionMatcher::class);
        $this->assertTrue($matcher->matches('tenants.*', 'tenants.view'));
        $this->assertTrue($matcher->matches('invoices.*', 'invoices.cancel'));
        $this->assertFalse($matcher->matches('tenants.*', 'billing.view'));
    }

    public function test_limited_user_cannot_access_protected_route(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->get(route('tenants.index'))
            ->assertForbidden();
    }

    public function test_limited_user_can_access_allowed_route(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_tenant_scoped_role_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Tenant Only', 'code' => 'tenant_only', 'status' => 'active']);
        $role->permissions()->sync(Permission::query()->where('code', 'tenants.view')->pluck('id'));

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Tenant,
            'tenant_id' => 1,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_expired_elevation_does_not_block_super_admin_permissions(): void
    {
        $admin = $this->superAdminUser();
        $record = UserActiveRole::query()->where('user_id', $admin->id)->first();
        $record->update(['elevation_verified_at' => now()->subHours(2)]);

        $this->actingAs($admin)
            ->get(route('access-control.permissions.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('server-health.index'))
            ->assertOk();
    }

    public function test_elevation_password_failure_does_not_switch_role(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);
        $role = Role::query()->create([
            'name' => 'Elevated',
            'code' => 'elevated_role',
            'status' => 'active',
            'requires_elevation' => true,
        ]);
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        try {
            app(RoleSwitchService::class)->switch($user, $assignment, 'wrong-password');
        } catch (ValidationException) {
            // expected
        }

        $this->assertNull(UserActiveRole::query()->where('user_id', $user->id)->value('user_role_assignment_id'));
    }

    public function test_access_control_requires_rbac_manage(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->get(route('access-control.permissions.index'))
            ->assertForbidden();
    }

    public function test_super_admin_bypasses_when_active_and_elevated(): void
    {
        $admin = $this->superAdminUser();

        $this->actingAs($admin)
            ->get(route('access-control.permissions.index'))
            ->assertOk();
    }

    public function test_inactive_parent_role_does_not_grant_permissions(): void
    {
        $parent = Role::query()->create(['name' => 'Inactive Parent', 'code' => 'inactive_parent', 'status' => 'inactive']);
        $child = Role::query()->create(['name' => 'Child', 'code' => 'child_inherit', 'status' => 'active']);
        $parent->permissions()->sync(Permission::query()->where('code', 'tenants.view')->pluck('id'));
        $child->parentRoles()->attach($parent->id);

        $resolver = app(RolePermissionResolver::class);
        $this->assertNotContains('tenants.view', $resolver->resolvePermissionCodes($child));
    }

    public function test_tenant_scoped_user_sees_only_assigned_tenant_on_index(): void
    {
        $project = Project::query()->create(['name' => 'Scope App', 'domain' => 'scope.test']);
        $tenantA = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Tenant Alpha Scoped',
            'status' => 'active',
        ]);
        $tenantB = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Tenant Beta Hidden',
            'status' => 'active',
        ]);

        $user = $this->scopedUser(RoleScopeType::Tenant, ['tenants.view'], tenantId: $tenantA->id);

        $this->actingAs($user)
            ->get(route('tenants.index'))
            ->assertOk()
            ->assertSee('Tenant Alpha Scoped')
            ->assertDontSee('Tenant Beta Hidden');
    }

    public function test_server_scoped_user_sees_only_assigned_server(): void
    {
        $serverA = Server::query()->create(['name' => 'Server A', 'status' => 'online']);
        $serverB = Server::query()->create(['name' => 'Server B', 'status' => 'online']);

        $user = $this->scopedUser(RoleScopeType::Server, ['servers.view'], serverId: $serverA->id);

        $this->actingAs($user)
            ->get(route('servers.index'))
            ->assertOk()
            ->assertSee('Server A')
            ->assertDontSee('Server B');
    }

    public function test_nested_tenant_document_download_denied_for_wrong_tenant(): void
    {
        $project = Project::query()->create(['name' => 'Doc Scope', 'domain' => 'docscope.test']);
        $tenantA = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Tenant A Docs',
            'status' => 'active',
        ]);
        $tenantB = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Tenant B Docs',
            'status' => 'active',
        ]);

        $document = OperationalDocument::query()->create([
            'tenant_id' => $tenantB->id,
            'title' => 'Secret Contract',
            'document_type' => 'contract',
            'status' => 'signed',
            'file_path' => 'operational-documents/'.$tenantB->id.'/secret.pdf',
        ]);

        $user = $this->scopedUser(RoleScopeType::Tenant, ['tenants.view', 'tenants.update'], tenantId: $tenantA->id);

        $this->actingAs($user)
            ->get(route('tenants.documents.download', [$tenantB, $document]))
            ->assertForbidden();
    }

    public function test_unprotected_ops_route_now_requires_permission(): void
    {
        $user = $this->userWithPermissions(['dashboard.view']);

        $this->actingAs($user)
            ->get(route('deployments.index'))
            ->assertForbidden();

        $userWithDeploy = $this->userWithPermissions(['deployments.view']);

        $this->actingAs($userWithDeploy)
            ->get(route('deployments.index'))
            ->assertOk();
    }

    public function test_session_mismatch_clears_active_role(): void
    {
        $user = $this->userWithPermissions(['deployments.view']);
        $record = UserActiveRole::query()->where('user_id', $user->id)->firstOrFail();
        $this->startSession();
        $record->update(['session_id' => 'stale-session-id']);

        $this->actingAs($user)
            ->get(route('deployments.index'))
            ->assertForbidden();

        $this->assertNull(UserActiveRole::query()->where('user_id', $user->id)->first());
    }

    public function test_role_switch_binds_active_role_to_session(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $role = Role::query()->create([
            'name' => 'Deployer',
            'code' => 'deployer_switch',
            'status' => 'active',
        ]);
        $role->permissions()->sync(
            Permission::query()->where('code', 'deployments.view')->pluck('id')
        );
        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        $this->actingAs($user)
            ->post(route('active-role.switch'), [
                'assignment_id' => $assignment->id,
            ])
            ->assertRedirect();

        $record = UserActiveRole::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($record->session_id);
        $this->assertTrue(
            filled(session()->getId()) && hash_equals($record->session_id, session()->getId())
        );
    }

    public function test_duplicate_assignment_is_rejected(): void
    {
        $admin = $this->superAdminUser();
        $target = User::factory()->create();
        $role = Role::query()->create(['name' => 'Dup', 'code' => 'dup_role', 'status' => 'active']);

        app(UserRoleAssignmentService::class)->assign($admin, $target, $role, [
            'scope_type' => RoleScopeType::Global->value,
        ]);

        $this->expectException(ValidationException::class);
        app(UserRoleAssignmentService::class)->assign($admin, $target, $role, [
            'scope_type' => RoleScopeType::Global->value,
        ]);
    }

    public function test_super_admin_works_with_matching_session(): void
    {
        $admin = $this->superAdminUser();

        $this->actingAs($admin)
            ->get(route('access-control.permissions.index'))
            ->assertOk();
    }

    public function test_project_scoped_user_sees_only_assigned_project_on_index(): void
    {
        $product = \App\Models\Product::query()->create(['name' => 'RBAC Product', 'slug' => 'rbac-product', 'status' => 'active']);
        $projectA = Project::query()->create(['product_id' => $product->id, 'name' => 'Project A', 'domain' => 'a.test', 'environment' => 'production', 'status' => 'active']);
        $projectB = Project::query()->create(['product_id' => $product->id, 'name' => 'Project B', 'domain' => 'b.test', 'environment' => 'production', 'status' => 'active']);

        $user = $this->scopedUser(RoleScopeType::Project, ['projects.view'], projectId: $projectA->id);

        $this->actingAs($user)
            ->get(route('hosted-projects.index'))
            ->assertOk()
            ->assertSee('Project A')
            ->assertDontSee('Project B');
    }

    public function test_tenant_infrastructure_update_denied_when_server_scope_mismatches(): void
    {
        $serverA = Server::query()->create(['name' => 'Infra Server A', 'status' => 'online']);
        $serverB = Server::query()->create(['name' => 'Infra Server B', 'status' => 'online']);
        $project = Project::query()->create(['name' => 'Infra Project', 'domain' => 'infra.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'server_id' => $serverA->id,
            'company_name' => 'Infra Tenant',
            'status' => 'active',
        ]);
        $subscription = TenantProjectSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'package_name' => 'Standard',
            'license_status' => 'active',
            'product_status' => 'active',
        ]);

        $user = $this->scopedUser(RoleScopeType::Server, ['tenants.update'], serverId: $serverA->id);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.infrastructure.update', [$tenant, $subscription]), [
                'server_id' => $serverB->id,
            ])
            ->assertForbidden();
    }

    public function test_project_scoped_role_only_works_for_that_project(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Project Lead', 'code' => 'project_lead', 'status' => 'active']);
        $role->permissions()->sync(Permission::query()->where('code', 'projects.view')->pluck('id'));

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Project,
            'project_id' => 5,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        $guard = app(RbacGuard::class);
        $this->assertTrue($guard->can($user, 'projects.view', ['project_id' => 5]));
        $this->assertFalse($guard->can($user, 'projects.view', ['project_id' => 9]));
    }

    /** @param  list<string>  $permissionCodes */
    private function userWithPermissions(array $permissionCodes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Limited',
            'code' => 'limited_'.uniqid(),
            'status' => 'active',
        ]);
        $ids = Permission::query()->whereIn('code', $permissionCodes)->pluck('id');
        $role->permissions()->sync($ids);

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

    private function superAdminUser(): User
    {
        $user = User::query()->first();
        if (! $user) {
            $user = User::factory()->create();
            $this->seed(RbacBootstrapSeeder::class);
        }

        $assignment = UserRoleAssignment::query()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->where('code', config('rbac.super_admin_role_code') ?: 'super_admin'))
            ->first();

        UserActiveRole::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_role_assignment_id' => $assignment->id,
                'activated_at' => now(),
                'elevation_verified_at' => now(),
            ]
        );

        return $user->fresh();
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    private function scopedUser(
        RoleScopeType $scopeType,
        array $permissionCodes,
        ?int $tenantId = null,
        ?int $projectId = null,
        ?int $serverId = null,
    ): User {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Scoped',
            'code' => 'scoped_'.uniqid(),
            'status' => 'active',
        ]);
        $role->permissions()->sync(Permission::query()->whereIn('code', $permissionCodes)->pluck('id'));

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => $scopeType,
            'tenant_id' => $tenantId,
            'project_id' => $projectId,
            'server_id' => $serverId,
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
