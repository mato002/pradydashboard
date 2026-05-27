<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\HostedProject;
use App\Models\Product;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantQuickActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspend_quick_action_updates_tenant_status(): void
    {
        $user = $this->userWithPermissions(['tenants.view', 'tenants.suspend']);
        $tenant = $this->makeTenant(['status' => 'active']);

        $this->actingAs($user)
            ->post(route('tenants.quick-actions.suspend', $tenant))
            ->assertRedirect(route('tenants.index'))
            ->assertSessionHas('status')
            ->assertSessionHas('tenant_drawer', $tenant->id);

        $this->assertSame('suspended', $tenant->fresh()->status);
    }

    public function test_reset_license_quick_action_sets_subscription_active(): void
    {
        $user = $this->userWithPermissions(['tenants.view', 'tenants.update']);
        $tenant = $this->makeTenant();
        $subscription = $tenant->projectSubscriptions()->create([
            'product_id' => $tenant->product_id,
            'product_status' => 'suspended',
            'license_status' => 'suspended',
            'disabled_reason' => 'test',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.quick-actions.reset-license', $tenant))
            ->assertRedirect(route('tenants.index'))
            ->assertSessionHas('status');

        $subscription->refresh();
        $this->assertSame('active', $subscription->product_status);
        $this->assertSame('active', $subscription->license_status);
        $this->assertNull($subscription->disabled_reason);
    }

    public function test_force_backup_quick_action_creates_queued_job(): void
    {
        $user = $this->userWithPermissions(['tenants.view', 'tenants.update', 'backups.create']);
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->post(route('tenants.quick-actions.backup', $tenant))
            ->assertRedirect(route('tenants.index'))
            ->assertSessionHas('status');

        $backup = Backup::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($backup);
        $this->assertSame('queued', $backup->status);
        $this->assertSame($tenant->hosted_project_id, $backup->{Backup::hostedProjectForeignKey()});
    }

    public function test_open_app_redirects_to_login_url(): void
    {
        $user = $this->userWithPermissions(['tenants.view']);
        $tenant = $this->makeTenant(['login_url' => 'https://mfi.example.com/login']);

        $this->actingAs($user)
            ->get(route('tenants.quick-actions.open-app', $tenant))
            ->assertRedirect('https://mfi.example.com/login');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeTenant(array $overrides = []): Tenant
    {
        $product = Product::query()->create([
            'name' => 'Quick Action Product',
            'slug' => 'quick-action-'.uniqid(),
            'status' => 'active',
        ]);

        $project = HostedProject::query()->create([
            'product_id' => $product->id,
            'name' => 'Quick Action App',
            'domain' => 'quick.test',
        ]);

        return Tenant::query()->create(array_merge([
            'hosted_project_id' => $project->id,
            'product_id' => $product->id,
            'company_name' => 'Quick Action Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ], $overrides));
    }

    /**
     * @param  list<string>  $codes
     */
    private function userWithPermissions(array $codes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Tenant Ops',
            'code' => 'tenant_ops_'.uniqid(),
            'status' => 'active',
        ]);

        $permissionIds = collect($codes)->map(function (string $code) {
            return Permission::query()->firstOrCreate(
                ['code' => $code],
                ['name' => $code, 'group' => 'tenants'],
            )->id;
        })->all();

        $role->permissions()->sync($permissionIds);

        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        return $user;
    }
}
