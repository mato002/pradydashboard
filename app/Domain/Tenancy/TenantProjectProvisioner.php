<?php

namespace App\Domain\Tenancy;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;

class TenantProjectProvisioner
{
    public function syncPrimarySubscription(Tenant $tenant, ?Project $project = null): TenantProjectSubscription
    {
        $project ??= $tenant->project;
        if (! $project) {
            throw new \InvalidArgumentException('Tenant has no primary project.');
        }

        $licenseStatus = match ($tenant->status) {
            'trial', 'overdue' => 'grace',
            'suspended', 'restricted', 'terminated', 'cancelled' => 'suspended',
            default => 'active',
        };

        $project->loadMissing('product');
        $product = $project->product;
        $productId = $project->product_id ?? $tenant->product_id;

        $subscription = TenantProjectSubscription::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'product_id' => $productId,
            ],
            [
                'package_name' => $tenant->subscription_plan ?? $product?->name ?? $project->name,
                'billing_cycle' => $tenant->billing_cycle ?? 'monthly',
                'start_date' => $tenant->start_date,
                'renewal_date' => $tenant->renewal_date,
                'contract_status' => 'active',
                'license_status' => $licenseStatus,
                'product_status' => in_array($tenant->status, ['suspended', 'terminated', 'cancelled'], true) ? 'disabled' : 'active',
                'monthly_fee' => $tenant->subscription_amount ?? $product?->default_monthly_fee,
                'currency' => $tenant->tenant_currency ?? $product?->currency ?? 'KES',
                'grace_period_days' => $tenant->grace_days ?? $product?->grace_period_days,
                'kill_switch_enabled' => (bool) ($product?->kill_switch_allowed ?? false),
                'offline_mode_allowed' => (bool) ($product?->offline_mode_allowed ?? false),
            ]
        );

        TenantProjectInfrastructure::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $subscription->id],
            [
                'server_id' => $tenant->server_id ?? $project->server_id,
                'cpanel_account' => $tenant->cpanel_account_ref,
                'domain' => $tenant->tenant_domain ?? $project->domain,
                'database_name' => $tenant->database_ref ?? $project->database_name,
                'public_url' => $tenant->login_url ?? $project->base_url,
                'disk_quota_mb' => $product?->default_disk_quota_mb,
            ]
        );

        if ($tenant->deployment_version) {
            $latestVersion = $product?->versions()->where('is_current', true)->value('version');

            TenantProjectVersion::query()->updateOrCreate(
                ['tenant_project_subscription_id' => $subscription->id],
                [
                    'current_version' => $tenant->deployment_version,
                    'latest_version' => $latestVersion,
                    'update_status' => $this->resolveUpdateStatus($tenant->deployment_version, $latestVersion),
                ]
            );
        }

        return $subscription;
    }

    private function resolveUpdateStatus(?string $current, ?string $latest): string
    {
        if (! $current || ! $latest) {
            return 'unknown';
        }

        return version_compare($current, $latest, '>=') ? 'latest' : 'outdated';
    }
}
