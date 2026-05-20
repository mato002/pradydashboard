<?php

namespace App\Domain\Tenancy;

use App\Models\Project;
use App\Models\TenantProjectServiceIntegration;
use App\Support\IntegrationServiceOptions;
use Illuminate\Support\Collection;

class ApiIntegrationsHub
{
    /**
     * @return array<string, mixed>
     */
    public function globalSummary(): array
    {
        $integrations = TenantProjectServiceIntegration::query()
            ->with(['subscription.tenant', 'subscription.project'])
            ->get();

        $provider = $integrations->filter(fn (TenantProjectServiceIntegration $i) => $i->isProvider());
        $tenantSystem = $integrations->filter(fn (TenantProjectServiceIntegration $i) => $i->isTenantSystem());

        $checkedToday = $integrations->filter(
            fn (TenantProjectServiceIntegration $i) => $i->last_checked_at?->isToday()
        );

        $totalChecks = $integrations->sum(fn (TenantProjectServiceIntegration $i) => (int) $i->success_count + (int) $i->failure_count);
        $totalSuccess = $integrations->sum('success_count');

        $successRate = $totalChecks > 0
            ? round(($totalSuccess / $totalChecks) * 100, 1)
            : null;

        $avgResponse = $integrations
            ->whereNotNull('average_response_time_ms')
            ->avg('average_response_time_ms');

        return [
            'total_configured' => $integrations->count(),
            'active' => $integrations->where('status', 'active')->count(),
            'failing' => $integrations->where('status', 'failing')->count(),
            'untested' => $integrations->whereNull('last_checked_at')->count(),
            'requests_today' => $checkedToday->count(),
            'success_rate' => $successRate,
            'average_response_time_ms' => $avgResponse ? (int) round($avgResponse) : null,
            'failed_checks' => $integrations->where('status', 'failing')->count(),
            'provider_count' => $provider->count(),
            'tenant_system_count' => $tenantSystem->count(),
            'by_category' => [
                ['category' => __('Provider integrations'), 'count' => $provider->count()],
                ['category' => __('Tenant system APIs'), 'count' => $tenantSystem->count()],
            ],
            'by_provider_type' => $this->groupCountByProviderType($provider),
        ];
    }

    /**
     * @return Collection<int, TenantProjectServiceIntegration>
     */
    public function providerIntegrations(): Collection
    {
        return TenantProjectServiceIntegration::query()
            ->provider()
            ->with(['subscription.tenant', 'subscription.project'])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, TenantProjectServiceIntegration>
     */
    public function tenantSystemApis(): Collection
    {
        return TenantProjectServiceIntegration::query()
            ->tenantSystem()
            ->with(['subscription.tenant', 'subscription.project', 'subscription.versionTracking'])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function projectApiKeys(): Collection
    {
        return Project::query()
            ->whereNotNull('api_token')
            ->withCount('tenants')
            ->orderBy('name')
            ->get()
            ->map(fn (Project $project) => [
                'id' => 'key_'.$project->id,
                'project_id' => $project->id,
                'project' => $project->name,
                'name' => $project->name.' '.__('License API'),
                'masked_token' => $this->maskToken($project->api_token),
                'status' => $project->status === 'active' ? 'active' : 'suspended',
                'tenants_count' => $project->tenants_count,
                'updated_at' => $project->updated_at,
            ]);
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    public function recentCheckLog(int $limit = 20): array
    {
        return TenantProjectServiceIntegration::query()
            ->whereNotNull('last_checked_at')
            ->orderByDesc('last_checked_at')
            ->limit($limit)
            ->get()
            ->map(fn (TenantProjectServiceIntegration $i) => [
                'integration' => $i->resolvedApiName(),
                'category' => $i->integration_category,
                'checked_at' => $i->last_checked_at,
                'response_code' => $i->last_response_code,
                'response_time_ms' => $i->last_response_time_ms,
                'status' => $i->status,
                'success' => $i->last_test_status === 'pass',
            ])
            ->all();
    }

    /**
     * @param  Collection<int, TenantProjectServiceIntegration>  $integrations
     * @return array<int, array{type: string, count: int}>
     */
    private function groupCountByProviderType(Collection $integrations): array
    {
        return $integrations
            ->groupBy('service_type')
            ->map(fn (Collection $group, string $type) => [
                'type' => IntegrationServiceOptions::providerServiceTypes()[$type] ?? $type,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    private function maskToken(?string $token): string
    {
        if (! filled($token)) {
            return '—';
        }

        return 'prady_live_'.substr($token, 0, 4).'********'.substr($token, -4);
    }
}
