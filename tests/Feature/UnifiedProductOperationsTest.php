<?php

namespace Tests\Feature;

use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedProductOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_have_multiple_project_subscriptions(): void
    {
        $user = User::factory()->create();
        $p1 = Project::query()->create(['name' => 'Product A', 'domain' => 'a.test']);
        $p2 = Project::query()->create(['name' => 'Product B', 'domain' => 'b.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $p1->id,
            'company_name' => 'Acme Ltd',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        TenantProjectSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $p2->id,
            'package_name' => 'Enterprise',
            'monthly_fee' => 5000,
            'currency' => 'KES',
            'contract_status' => 'active',
            'license_status' => 'active',
            'product_status' => 'active',
        ]);

        $tenant->load('projectSubscriptions.project');

        $this->assertCount(2, $tenant->projectSubscriptions);
        $this->assertTrue($tenant->projectSubscriptions->pluck('project_id')->contains($p1->id));
        $this->assertTrue($tenant->projectSubscriptions->pluck('project_id')->contains($p2->id));
    }

    public function test_tenant_show_includes_ops_summary(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Ops Product', 'domain' => 'ops.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Ops Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'subscription_amount' => 12000,
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=projects')
            ->assertOk()
            ->assertSee('Ops Product')
            ->assertSee(__('Add project subscription'));
    }
}
