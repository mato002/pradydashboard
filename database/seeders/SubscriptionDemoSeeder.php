<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Database\Seeder;

class SubscriptionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'tier' => 'starter',
                'monthly_price' => 4999,
                'annual_price' => 49990,
                'features' => ['5 tenants', '10 GB storage', '50k API calls/mo', 'Email support'],
                'api_quota' => 50000,
                'storage_gb' => 10,
                'max_tenants' => 5,
                'max_seats' => 3,
                'sort_order' => 1,
            ],
            [
                'slug' => 'professional',
                'name' => 'Professional',
                'tier' => 'professional',
                'monthly_price' => 14999,
                'annual_price' => 149990,
                'features' => ['25 tenants', '100 GB storage', '500k API calls/mo', 'Priority support', 'Backups'],
                'api_quota' => 500000,
                'storage_gb' => 100,
                'max_tenants' => 25,
                'max_seats' => 15,
                'sort_order' => 2,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'tier' => 'enterprise',
                'monthly_price' => 49999,
                'annual_price' => 499990,
                'features' => ['Unlimited tenants', '1 TB storage', '5M API calls/mo', 'Dedicated CSM', 'SLA 99.9%'],
                'api_quota' => 5000000,
                'storage_gb' => 1024,
                'max_tenants' => null,
                'max_seats' => 100,
                'sort_order' => 3,
            ],
            [
                'slug' => 'custom',
                'name' => 'Custom',
                'tier' => 'custom',
                'monthly_price' => 0,
                'annual_price' => null,
                'features' => ['Negotiated limits', 'Private cloud', 'Custom SLA', 'White-label'],
                'api_quota' => null,
                'storage_gb' => null,
                'max_tenants' => null,
                'max_seats' => null,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SaasPlan::query()->updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        if (TenantSubscription::query()->exists()) {
            return;
        }

        $planModels = SaasPlan::query()->orderBy('sort_order')->get();
        $tenants = Tenant::query()->with('project')->get();
        $statuses = ['active', 'active', 'active', 'trial', 'trial', 'grace_period', 'overdue', 'suspended', 'cancelled', 'active'];

        foreach ($tenants as $i => $tenant) {
            $plan = $planModels->get($i % $planModels->count());
            $status = $statuses[$i % count($statuses)];
            $cycle = $i % 3 === 0 ? 'annual' : 'monthly';
            $amount = $cycle === 'annual'
                ? (float) ($plan->annual_price ?? $plan->monthly_price * 10)
                : (float) $plan->monthly_price;

            if ($plan->slug === 'custom') {
                $amount = (float) ($tenant->subscription_amount ?? 75000);
            }

            TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'product_name' => $tenant->project?->name ?? 'Prady Platform',
                'amount' => $amount,
                'billing_cycle' => $cycle,
                'current_period_start' => now()->subDays(15),
                'current_period_end' => $status === 'trial'
                    ? now()->addDays(14)
                    : now()->addDays($cycle === 'annual' ? 350 : 15),
                'status' => $status,
                'auto_renew' => ! in_array($status, ['suspended', 'cancelled', 'overdue'], true),
            ]);

            $tenant->update([
                'subscription_plan' => $plan->name,
                'subscription_amount' => $amount,
                'billing_cycle' => $cycle,
                'status' => match ($status) {
                    'grace_period' => 'trial',
                    'overdue' => 'overdue',
                    'suspended' => 'suspended',
                    'cancelled' => 'cancelled',
                    default => $status === 'trial' ? 'trial' : 'active',
                },
                'renewal_date' => now()->addDays($cycle === 'annual' ? 350 : 15),
            ]);
        }

        if ($tenants->isEmpty()) {
            $this->seedStandaloneSubscriptions($planModels);
        }
    }

    private function seedStandaloneSubscriptions($planModels): void
    {
        $samples = [
            ['Acme Properties Ltd', 'professional', 'active', 14999],
            ['Nova CRM Kenya', 'enterprise', 'active', 49999],
            ['Orbit Logistics', 'starter', 'trial', 4999],
            ['Sunrise Retail', 'professional', 'grace_period', 14999],
            ['Legacy Host Co', 'starter', 'overdue', 4999],
            ['Paused SaaS Demo', 'professional', 'suspended', 14999],
            ['Churned Client', 'starter', 'cancelled', 0],
            ['Growth Stack Inc', 'enterprise', 'active', 52000],
        ];

        $project = Project::query()->first();
        if (! $project) {
            return;
        }

        foreach ($samples as $i => [$company, $planSlug, $status, $amount]) {
            $plan = $planModels->firstWhere('slug', $planSlug) ?? $planModels->first();

            $tenant = Tenant::query()->create([
                'company_name' => $company,
                'project_id' => $project->id,
                'email' => strtolower(str_replace(' ', '.', $company)).'@example.com',
                'subscription_plan' => $plan->name,
                'subscription_amount' => $amount,
                'billing_cycle' => 'monthly',
                'status' => match ($status) {
                    'overdue' => 'overdue',
                    'suspended' => 'suspended',
                    'cancelled' => 'cancelled',
                    'trial', 'grace_period' => 'trial',
                    default => 'active',
                },
                'renewal_date' => now()->addDays(20),
            ]);

            TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'product_name' => $project?->name ?? 'Prady Platform',
                'amount' => $amount,
                'billing_cycle' => 'monthly',
                'current_period_start' => now()->subMonth(),
                'current_period_end' => now()->addDays(20),
                'status' => $status,
                'auto_renew' => $status === 'active',
            ]);
        }
    }
}
