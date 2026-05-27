<?php

namespace App\Support\Admin;

use App\Models\HostedProject;
use App\Models\Tenant;
use Illuminate\Support\Str;

class HostedProjectIntegrationKit
{
    /**
     * @return array<string, mixed>
     */
    public function forShow(HostedProject $project): array
    {
        $project->loadMissing(['product', 'tenants']);

        $dashboardUrl = rtrim((string) config('app.url'), '/');
        $productKey = $project->resolveProductKey();
        $productName = $project->product?->name ?? $project->name;

        $tenants = $project->tenants->map(fn (Tenant $tenant) => [
            'id' => $tenant->id,
            'company_name' => $tenant->company_name,
            'tenant_key' => $tenant->tenant_key,
            'tenant_code' => $tenant->tenant_code,
            'license_secret' => $tenant->license_secret,
            'tenant_domain' => $tenant->tenant_domain ?? $project->domain,
            'status' => $tenant->status,
            'show_url' => route('tenants.show', $tenant),
        ])->values()->all();

        $primaryTenant = $project->tenants->first();

        $lines = [
            'PRADY_LICENSE_ENFORCED=true',
            'PRADY_DASHBOARD_URL='.$dashboardUrl,
            'PRADY_PROJECT_API_TOKEN='.($project->api_token ?? ''),
            'PRADY_TENANT_KEY='.($primaryTenant?->tenant_key ?? ''),
            'PRADY_PRODUCT_KEY='.$productKey,
            'PRADY_LICENSE_SECRET='.($primaryTenant?->license_secret ?? ''),
            'PRADY_LICENSE_CACHE_TTL=600',
            '',
            '# Optional — dashboard pulls product health (set same value in product .env)',
            'PRADY_DASHBOARD_API_TOKEN='.Str::random(48),
            'PRADY_TENANT_CODE='.($primaryTenant?->tenant_code ?? ''),
            'PRADY_PRODUCT_NAME="'.$productName.'"',
        ];

        return [
            'dashboard_url' => $dashboardUrl,
            'license_endpoint' => $dashboardUrl.'/api/v1/license/check',
            'product_key' => $productKey,
            'product_name' => $productName,
            'project_api_token' => $project->api_token,
            'suggested_dashboard_api_token' => Str::random(48),
            'tenants' => $tenants,
            'primary_tenant' => $primaryTenant ? [
                'tenant_key' => $primaryTenant->tenant_key,
                'tenant_code' => $primaryTenant->tenant_code,
                'license_secret' => $primaryTenant->license_secret,
                'tenant_domain' => $primaryTenant->tenant_domain ?? $project->domain,
            ] : null,
            'env_block' => implode("\n", $lines),
            'create_tenant_url' => route('tenants.create', ['hosted_project_id' => $project->id]),
        ];
    }
}
