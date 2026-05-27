<?php

namespace Tests\Feature;

use App\Domain\Operations\OperationalRiskScanner;
use App\Models\Permission;
use App\Models\HostedProject;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Admin\OperationalRiskPresenter;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOperationalRisksTest extends TestCase
{
    use RefreshDatabase;

    public function test_presenter_bundles_duplicate_billing_risks(): void
    {
        $risks = collect([
            [
                'key' => 'invoice_overdue:1',
                'category' => 'billing',
                'severity' => 'critical',
                'title' => 'One',
                'description' => 'A',
                'recommended_action' => 'Act',
                'due_at' => null,
                'tenant_id' => 1,
                'url' => '/a',
                'acknowledged' => false,
            ],
            [
                'key' => 'invoice_overdue:2',
                'category' => 'billing',
                'severity' => 'critical',
                'title' => 'Two',
                'description' => 'B',
                'recommended_action' => 'Act',
                'due_at' => null,
                'tenant_id' => 2,
                'url' => '/b',
                'acknowledged' => false,
            ],
        ]);

        $billing = collect(OperationalRiskPresenter::build($risks)['sections'])
            ->firstWhere('id', 'billing');

        $this->assertSame('bundle', $billing['items'][0]['type']);
        $this->assertSame(2, $billing['count']);
    }

    public function test_dashboard_renders_operations_risk_center_with_summary_and_groups(): void
    {
        $user = $this->userWithDashboardAccess();

        $project = HostedProject::query()->create([
            'name' => 'Ops App',
            'domain' => 'ops.test',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Ops Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-OPS-1',
            'status' => 'overdue',
            'currency' => 'KES',
            'subtotal' => 5000,
            'total' => 5000,
            'amount_paid' => 0,
            'due_date' => now()->subDays(3),
            'issue_date' => now()->subDays(30)->toDateString(),
        ]);

        $attention = app(OperationalRiskScanner::class)->attentionRequired(50);
        $center = OperationalRiskPresenter::build($attention);

        $this->assertGreaterThan(0, $center['total']);
        $this->assertGreaterThan(0, $center['summary']['critical']['count']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Operations Risk Center'))
            ->assertSee(__('Critical risks'))
            ->assertSee(__('Billing & Collections'))
            ->assertSee(__('Infrastructure'));
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
}
