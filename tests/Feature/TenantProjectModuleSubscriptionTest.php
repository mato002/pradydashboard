<?php

namespace Tests\Feature;

use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\Tenant;
use App\Models\TenantProjectModuleSubscription;
use App\Models\TenantProjectSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantProjectModuleSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_subscription_can_be_enabled_and_disabled(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Modular App', 'domain' => 'mod.test', 'currency' => 'KES']);
        $module = ProjectModule::query()->create([
            'project_id' => $project->id,
            'name' => 'Reports',
            'code' => 'reports',
            'monthly_price' => 1500,
        ]);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Module Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.modules.update', [$tenant, $subscription]), [
                'modules' => [
                    $module->id => [
                        'enabled' => '1',
                        'subscribed' => '1',
                        'billing_status' => 'active',
                        'monthly_price_override' => '2000',
                        'notes' => 'Custom price',
                    ],
                ],
            ])
            ->assertRedirect(route('tenants.show', [
                'tenant' => $tenant,
                'tab' => 'modules',
                'subscription' => $subscription->id,
            ]));

        $row = TenantProjectModuleSubscription::query()
            ->where('tenant_project_subscription_id', $subscription->id)
            ->where('project_module_id', $module->id)
            ->first();

        $this->assertNotNull($row);
        $this->assertTrue($row->enabled);
        $this->assertTrue($row->subscribed);
        $this->assertSame('2000.00', $row->monthly_price_override);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.modules.update', [$tenant, $subscription]), [
                'modules' => [
                    $module->id => [
                        'enabled' => '0',
                        'subscribed' => '0',
                        'billing_status' => 'suspended',
                    ],
                ],
            ])
            ->assertRedirect();

        $row->refresh();
        $this->assertFalse($row->enabled);
        $this->assertFalse($row->subscribed);
        $this->assertSame('suspended', $row->billing_status);
    }

    public function test_modules_tab_empty_states(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Empty Modules', 'domain' => 'empty-mod.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'No Sub Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=modules')
            ->assertOk()
            ->assertSee(__('No project subscriptions for this tenant'));

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=modules')
            ->assertOk()
            ->assertSee(__('No modules defined for this project'));
    }

    public function test_tenant_overview_shows_module_summary(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create(['name' => 'Summary App', 'domain' => 'sum.test']);
        $module = ProjectModule::query()->create([
            'project_id' => $project->id,
            'name' => 'Core',
            'code' => 'core',
            'monthly_price' => 1000,
        ]);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Summary Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        TenantProjectModuleSubscription::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'project_module_id' => $module->id,
            'enabled' => true,
            'subscribed' => true,
            'billing_status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('tenants.show', $tenant).'?tab=overview')
            ->assertOk()
            ->assertSee(__('Modules enabled'))
            ->assertSee(__('Modules subscribed'));
    }
}
