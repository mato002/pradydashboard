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
            'project_id' => $project->id,
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
}
