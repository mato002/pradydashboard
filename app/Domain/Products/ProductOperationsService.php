<?php

namespace App\Domain\Products;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductOperationsService
{
    /**
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public function kpis(Collection $products): array
    {
        $hostedCount = $products->sum(fn (Product $p) => $p->hosted_projects_count ?? $p->hostedProjects()->count());
        $tenantCount = $products->sum(fn (Product $p) => $p->tenants_count ?? 0);
        $activeTenants = $products->sum(fn (Product $p) => $p->active_tenants_count ?? 0);
        $suspendedTenants = $products->sum(fn (Product $p) => $p->suspended_tenants_count ?? 0);

        return [
            'total_products' => $products->count(),
            'hosted_instances' => $hostedCount,
            'total_tenants' => $tenantCount,
            'active_tenants' => $activeTenants,
            'suspended_tenants' => $suspendedTenants,
            'monthly_revenue' => number_format(
                (float) $products->sum(
                    fn (Product $p) => (float) $p->tenants()
                        ->whereIn('status', ['active', 'trial'])
                        ->sum('subscription_amount')
                ),
                0
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enrich(Product $product): array
    {
        $product->loadCount([
            'hostedProjects',
            'tenants',
            'tenants as active_tenants_count' => fn ($q) => $q->where('status', 'active'),
            'tenants as suspended_tenants_count' => fn ($q) => $q->where('status', 'suspended'),
        ]);

        $monthlyRevenue = (float) $product->tenants()
            ->where('status', 'active')
            ->sum('subscription_amount');

        return [
            'hosted_projects_count' => $product->hosted_projects_count,
            'tenants_count' => $product->tenants_count,
            'active_tenants_count' => $product->active_tenants_count,
            'suspended_tenants_count' => $product->suspended_tenants_count,
            'monthly_revenue' => $monthlyRevenue,
            'license_checks_24h' => $product->hostedProjects()
                ->withCount(['licenseCheckLogs as checks_24h' => fn ($q) => $q->where('checked_at', '>=', now()->subDay())])
                ->get()
                ->sum('checks_24h'),
        ];
    }
}
