<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_subscription_create_page(): void
    {
        $user = User::factory()->create();
        SaasPlan::query()->create([
            'slug' => 'starter',
            'name' => 'Starter',
            'tier' => 'starter',
            'monthly_price' => 4999,
            'annual_price' => 49990,
            'currency' => 'KES',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('subscriptions.create'))
            ->assertOk()
            ->assertSee(__('New subscription'))
            ->assertSee(__('Create subscription'));
    }

    public function test_authenticated_user_can_create_subscription(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'CRM Suite',
            'domain' => 'crm.example.com',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Acme Ltd',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        $plan = SaasPlan::query()->create([
            'slug' => 'pro',
            'name' => 'Professional',
            'tier' => 'professional',
            'monthly_price' => 14999,
            'annual_price' => 149990,
            'currency' => 'KES',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.store'), [
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $plan->id,
                'plan_name' => 'Professional',
                'product_name' => 'CRM Suite',
                'amount' => 14999,
                'billing_cycle' => 'monthly',
                'current_period_start' => '2026-05-01',
                'current_period_end' => '2026-06-01',
                'status' => 'active',
                'auto_renew' => '1',
            ])
            ->assertRedirect(route('subscriptions.index'));

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_name' => 'Professional',
            'amount' => 14999,
        ]);

        $tenant->refresh();
        $this->assertSame('Professional', $tenant->subscription_plan);
        $this->assertEquals(14999, (float) $tenant->subscription_amount);
    }

    public function test_upgrade_cancels_prior_subscription(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'CRM Suite',
            'domain' => 'crm-upgrade.example.com',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Upgrade Co',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'subscription_plan' => 'Starter',
            'subscription_amount' => 5000,
        ]);
        $starter = SaasPlan::query()->create([
            'slug' => 'starter-up',
            'name' => 'Starter',
            'tier' => 'starter',
            'monthly_price' => 5000,
            'annual_price' => 50000,
            'currency' => 'KES',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $pro = SaasPlan::query()->create([
            'slug' => 'pro-up',
            'name' => 'Professional',
            'tier' => 'professional',
            'monthly_price' => 15000,
            'annual_price' => 150000,
            'currency' => 'KES',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $existing = \App\Models\TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $starter->id,
            'plan_name' => 'Starter',
            'product_name' => 'CRM Suite',
            'amount' => 5000,
            'billing_cycle' => 'monthly',
            'current_period_start' => now()->subMonth()->toDateString(),
            'current_period_end' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.store'), [
                'upgrade' => '1',
                'replaces_subscription_id' => $existing->id,
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $pro->id,
                'plan_name' => 'Professional',
                'product_name' => 'CRM Suite',
                'amount' => 15000,
                'billing_cycle' => 'monthly',
                'current_period_start' => now()->toDateString(),
                'current_period_end' => now()->addMonth()->toDateString(),
                'status' => 'active',
                'auto_renew' => '1',
            ])
            ->assertRedirect(route('subscriptions.index', ['tenant' => $tenant->id]));

        $existing->refresh();
        $this->assertSame('cancelled', $existing->status);
        $this->assertFalse($existing->auto_renew);

        $this->assertSame(1, \App\Models\TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->count());

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_name' => 'Professional',
            'status' => 'active',
            'amount' => 15000,
        ]);

        $tenant->refresh();
        $this->assertSame('Professional', $tenant->subscription_plan);
        $this->assertEquals(15000, (float) $tenant->subscription_amount);
    }

    public function test_upgrade_requires_subscription_to_replace(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'name' => 'CRM',
            'domain' => 'crm-req.example.com',
        ]);
        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Need Replace Co',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.store'), [
                'upgrade' => '1',
                'tenant_id' => $tenant->id,
                'plan_name' => 'Professional',
                'amount' => 15000,
                'billing_cycle' => 'monthly',
                'current_period_start' => now()->toDateString(),
                'current_period_end' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ])
            ->assertSessionHasErrors('replaces_subscription_id');
    }
}
