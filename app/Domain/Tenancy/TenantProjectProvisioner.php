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

        $subscription = TenantProjectSubscription::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'project_id' => $project->id,
            ],
            [
                'package_name' => $tenant->subscription_plan ?? $project->name,
                'billing_cycle' => $tenant->billing_cycle ?? 'monthly',
                'start_date' => $tenant->start_date,
                'renewal_date' => $tenant->renewal_date,
                'contract_status' => 'active',
                'license_status' => $licenseStatus,
                'product_status' => in_array($tenant->status, ['suspended', 'terminated', 'cancelled'], true) ? 'disabled' : 'active',
                'monthly_fee' => $tenant->subscription_amount ?? $project->default_monthly_fee,
                'currency' => $tenant->tenant_currency ?? $project->currency ?? 'KES',
                'grace_period_days' => $tenant->grace_days ?? $project->grace_period_days,
                'kill_switch_enabled' => (bool) $project->kill_switch_allowed,
                'offline_mode_allowed' => (bool) $project->offline_mode_allowed,
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
                'disk_quota_mb' => $project->default_disk_quota_mb,
            ]
        );

        if ($tenant->deployment_version) {
            TenantProjectVersion::query()->updateOrCreate(
                ['tenant_project_subscription_id' => $subscription->id],
                [
                    'current_version' => $tenant->deployment_version,
                    'latest_version' => $project->version,
                    'update_status' => $this->resolveUpdateStatus($tenant->deployment_version, $project->version),
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
