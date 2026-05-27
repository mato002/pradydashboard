<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\Admin\TenantWorkspaceMetrics;
use Tests\TestCase;

class TenantWorkspaceMetricsTest extends TestCase
{
    public function test_groups_include_human_readable_ssl_label(): void
    {
        $tenant = new Tenant([
            'tenant_currency' => 'KES',
            'subscription_amount' => null,
        ]);

        $groups = TenantWorkspaceMetrics::groups(
            [
                'currency' => 'KES',
                'monthly_revenue' => 0,
                'renewal_risk' => 0,
                'license_issues' => 0,
                'outdated_versions' => 0,
                'open_tickets' => 0,
                'infrastructure_gaps' => 0,
                'modules_enabled' => 0,
                'modules_subscribed' => 0,
                'modules_billing_total' => 0,
                'assigned_server' => 'Unassigned',
                'ssl_status' => 'Unknown',
                'backup_status' => 'Unknown',
                'version_status' => 'Unknown',
                'update_risk' => 'Unknown',
                'integrations_active' => 0,
                'integrations_failing' => 0,
                'integrations_not_tested' => 0,
                'documents_count' => 0,
                'documents_expiring' => 0,
                'missing_required_contracts' => 0,
                'tenant_system_configured' => false,
            ],
            ['outstanding' => 0],
            $tenant,
        );

        $infra = collect($groups)->firstWhere('id', 'infrastructure');
        $ssl = collect($infra['items'])->first(fn ($i) => str_contains($i['label'], 'SSL'));

        $this->assertStringContainsString('not configured', strtolower($ssl['value']));
    }
}
