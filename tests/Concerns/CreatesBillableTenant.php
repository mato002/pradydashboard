<?php

namespace Tests\Concerns;

use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;

trait CreatesBillableTenant
{
    /**
     * @param  array<string, mixed>  $tenantAttributes
     * @param  array<string, mixed>  $projectAttributes
     * @param  array<string, mixed>  $subscriptionAttributes
     * @return array{0: Product, 1: Project, 2: Tenant, 3: TenantProjectSubscription}
     */
    protected function createTenantWithSubscription(
        string $name = 'Billing Co',
        array $tenantAttributes = [],
        array $projectAttributes = [],
        array $subscriptionAttributes = [],
    ): array {
        $slug = str($name)->slug();

        $product = Product::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
        ]);

        $project = Project::query()->create(array_merge([
            'name' => $name,
            'domain' => $slug.'.test',
            'currency' => 'KES',
            'product_id' => $product->id,
        ], $projectAttributes));

        $tenant = Tenant::query()->create(array_merge([
            'hosted_project_id' => $project->id,
            'product_id' => $product->id,
            'company_name' => $name,
            'status' => 'active',
            'subscription_amount' => 10000,
        ], $tenantAttributes));

        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant, $project);

        $subscription = TenantProjectSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        if ($subscriptionAttributes !== []) {
            $subscription->update($subscriptionAttributes);
        }

        return [$product, $project, $tenant, $subscription];
    }
}
