<?php

namespace App\Domain\Tenancy;

use App\Models\Server;
use App\Models\Tenant;
use Carbon\Carbon;

class TenantCommandCenter
{
    public function __construct(
        private readonly TenantProjectModuleMatrix $moduleMatrix,
        private readonly ProjectVersionRolloutSummary $rolloutSummary,
        private readonly OperationalDocumentInsights $documentInsights,
        private readonly TenantIntegrationInsights $integrationInsights,
        private readonly TenantSystemApiInsights $tenantSystemApiInsights,
    ) {}
    /**
     * @return array<string, mixed>
     */
    public function summary(Tenant $tenant): array
    {
        $subscriptions = $tenant->projectSubscriptions;
        $activeProducts = $subscriptions->where('product_status', 'active')->count();
        $mrr = (float) $subscriptions->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));
        $renewalRisk = $subscriptions->filter(function ($s) {
            if (! $s->renewal_date) {
                return false;
            }

            return $s->renewal_date->lte(Carbon::now()->addDays(14));
        })->count();

        $licenseIssues = $subscriptions->whereIn('license_status', ['suspended', 'expired', 'grace'])->count();
        $outdatedVersions = $subscriptions->filter(
            fn ($s) => in_array($this->rolloutSummary->resolveSubscriptionStatus($s), ['outdated', 'critical_update_required'], true)
        )->count();
        $openTickets = $tenant->supportTickets->where('status', 'open')->count();

        $moduleStats = $this->moduleMatrix->statsForTenant($tenant);
        $documentStats = $this->documentInsights->summaryForTenant($tenant);
        $integrationStats = $this->integrationInsights->summaryForTenant($tenant);

        $primary = $subscriptions->firstWhere('product_status', 'active') ?? $subscriptions->first();
        $tenantSystemApi = $primary
            ? $this->tenantSystemApiInsights->forSubscription($primary)
            : $this->tenantSystemApiInsights->forTenant($tenant);
        $infra = $primary?->infrastructure;
        $version = $primary?->versionTracking;
        $serversExist = Server::query()->exists();
        $versionStatus = $primary
            ? $this->rolloutSummary->resolveSubscriptionStatus($primary)
            : 'unknown';

        return [
            'active_projects' => $activeProducts,
            'monthly_revenue' => $mrr,
            'currency' => $tenant->tenant_currency ?? 'KES',
            'renewal_risk' => $renewalRisk,
            'license_issues' => $licenseIssues,
            'outdated_versions' => $outdatedVersions,
            'open_tickets' => $openTickets,
            'infrastructure_gaps' => $subscriptions->filter(fn ($s) => ! $s->infrastructure?->server_id && $s->project?->requires_server)->count(),
            'modules_enabled' => $moduleStats['enabled'],
            'modules_subscribed' => $moduleStats['subscribed'],
            'modules_billing_total' => $moduleStats['billing_total'],
            'modules_currency' => $moduleStats['currency'],
            'assigned_server' => $serversExist
                ? ($infra?->server?->name ?? __('Unassigned'))
                : __('No server registered yet'),
            'public_url' => $infra?->public_url,
            'ssl_status' => $infra?->ssl_status ? ucfirst(str_replace('_', ' ', $infra->ssl_status)) : __('Unknown'),
            'backup_status' => $infra?->backup_status ? ucfirst(str_replace('_', ' ', $infra->backup_status)) : __('Unknown'),
            'version_status' => ucfirst(str_replace('_', ' ', $versionStatus)),
            'update_risk' => in_array($versionStatus, ['outdated', 'critical_update_required'], true)
                ? ($versionStatus === 'critical_update_required' ? __('Critical') : __('Outdated'))
                : ($versionStatus === 'unknown' ? __('Unknown') : __('OK')),
            'documents_count' => $documentStats['count'],
            'documents_expiring' => $documentStats['expiring'],
            'missing_required_contracts' => $documentStats['missing_contracts'],
            'integrations_active' => $integrationStats['active'],
            'integrations_failing' => $integrationStats['failing'],
            'integrations_not_tested' => $integrationStats['not_tested'],
            'tenant_system_configured' => $tenantSystemApi['configured'],
            'tenant_system_version' => $tenantSystemApi['current_version'],
            'tenant_system_health' => $tenantSystemApi['health_status'],
            'tenant_system_last_check' => $tenantSystemApi['last_api_check'],
            'tenant_system_last_heartbeat' => $tenantSystemApi['last_heartbeat'],
            'tenant_system_last_error' => $tenantSystemApi['last_error'],
        ];
    }
}
