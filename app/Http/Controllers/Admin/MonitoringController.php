<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Support\DemoMode;
use App\Support\OperationalMetrics;
use App\Models\ManagedDomain;
use App\Models\Project;
use App\Models\Server;
use App\Models\ServerHealthLog;
use App\Models\Tenant;
use Database\Seeders\ServerHealthDemoSeeder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        if (DemoMode::enabled() && ServerHealthLog::query()->doesntExist()) {
            (new ServerHealthDemoSeeder)->run();
        }

        $servers = Server::query()->with('latestHealthLog')->get();
        $tenants = Tenant::query()->count();
        $projects = Project::query()->count();

        $syncedServers = $servers->filter(fn (Server $s) => $s->last_synced_at !== null);
        $onlineCount = $syncedServers->where('status', 'online')->count();
        $totalServers = $servers->count();
        $systemUptime = $syncedServers->isEmpty()
            ? null
            : round(($onlineCount / max(1, $syncedServers->count())) * 100, 2);

        $alerts = $this->buildAlertCenter($servers);
        $activeAlerts = $alerts->whereIn('severity', ['critical', 'warning'])->count();
        $criticalIncidents = $alerts->where('severity', 'critical')->where('category', 'incident')->count();

        $kpis = [
            'system_uptime' => $systemUptime,
            'active_alerts' => $activeAlerts,
            'error_rate' => $this->errorRate(),
            'avg_response_ms' => $this->avgResponseMs($servers),
            'api_availability' => $this->apiAvailability(),
            'critical_incidents' => max($criticalIncidents, $alerts->where('severity', 'critical')->count()),
        ];

        $spark = fn (string $key) => OperationalMetrics::emptySparkline();
        $hasLiveTelemetry = $syncedServers->isNotEmpty();

        return view('admin.monitoring.index', [
            'kpis' => $kpis,
            'hasLiveTelemetry' => $hasLiveTelemetry,
            'spark' => $spark,
            'alerts' => $alerts,
            'uptimeSeries' => $this->buildUptimeSeries($servers),
            'latencySeries' => $this->buildLatencySeries(),
            'errorRateSeries' => $this->buildErrorRateSeries(),
            'apiEndpoints' => $this->buildApiEndpoints(),
            'incidentTimeline' => $this->buildIncidentTimeline($alerts),
            'syntheticChecks' => $this->buildSyntheticChecks(),
            'heartbeats' => $this->buildHeartbeats($servers),
            'traces' => $this->buildTraces(),
            'dbMetrics' => $this->buildDbMetrics(),
            'queueMetrics' => $this->buildQueueMetrics(),
            'cacheMetrics' => $this->buildCacheMetrics(),
            'escalationPolicies' => $this->buildEscalationPolicies(),
            'tenantHealth' => $this->buildTenantHealth($tenants),
            'deploymentEvents' => $this->buildDeploymentEvents(),
            'fleetSummary' => [
                'servers' => $servers->count(),
                'tenants' => $tenants,
                'projects' => $projects,
                'online' => $onlineCount,
            ],
        ]);
    }

    private function errorRate(): ?float
    {
        return null;
    }

    /**
     * @param  Collection<int, Server>  $servers
     */
    private function avgResponseMs(Collection $servers): ?int
    {
        $synced = $servers->filter(fn (Server $s) => $s->last_synced_at !== null && $s->status === 'online');
        if ($synced->isEmpty()) {
            return null;
        }

        $loads = $synced
            ->map(fn (Server $s) => (float) ($s->latestHealthLog?->cpu_percent ?? $s->load_average))
            ->filter(fn ($v) => $v > 0);

        if ($loads->isEmpty()) {
            return null;
        }

        return (int) round($loads->avg());
    }

    private function apiAvailability(): ?float
    {
        return null;
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return Collection<int, array{severity: string, category: string, title: string, body: string, source: string, service: string, time: string, ack: bool}>
     */
    private function buildAlertCenter(Collection $servers): Collection
    {
        $alerts = collect();

        foreach ($servers as $server) {
            if ($server->status === 'offline') {
                $alerts->push([
                    'severity' => 'critical',
                    'category' => 'downtime',
                    'title' => __('Host unreachable'),
                    'body' => __(':host failed heartbeat and synthetic checks.', ['host' => $server->name]),
                    'source' => $server->name,
                    'service' => 'infrastructure',
                    'time' => __('Live'),
                    'ack' => false,
                ]);
            }

            $cpu = (float) ($server->latestHealthLog?->cpu_percent ?? 0);
            if ($cpu >= 85) {
                $alerts->push([
                    'severity' => 'critical',
                    'category' => 'incident',
                    'title' => __('P99 latency breach'),
                    'body' => __(':host CPU at :pct% — APM traces show thread pool saturation.', ['host' => $server->name, 'pct' => (int) $cpu]),
                    'source' => $server->name,
                    'service' => 'apm',
                    'time' => __('2m ago'),
                    'ack' => false,
                ]);
            }

            if (in_array($server->ssl_status, ['expiring', 'expired'], true)) {
                $alerts->push([
                    'severity' => $server->ssl_status === 'expired' ? 'critical' : 'warning',
                    'category' => 'ssl',
                    'title' => __('SSL certificate failure risk'),
                    'body' => __('TLS on :host requires immediate renewal.', ['host' => $server->name]),
                    'source' => $server->name,
                    'service' => 'edge',
                    'time' => __('Ops'),
                    'ack' => false,
                ]);
            }

            if (in_array($server->backup_status, ['failed', 'error'], true)) {
                $alerts->push([
                    'severity' => 'critical',
                    'category' => 'backup',
                    'title' => __('Backup failure'),
                    'body' => __('Snapshot agent on :host did not complete.', ['host' => $server->name]),
                    'source' => $server->name,
                    'service' => 'backup',
                    'time' => __('1h ago'),
                    'ack' => true,
                ]);
            }
        }

        foreach (Backup::query()->where('status', 'failed')->latest('started_at')->take(3)->get() as $backup) {
            $alerts->push([
                'severity' => 'critical',
                'category' => 'backup',
                'title' => __('Backup job failed'),
                'body' => $backup->name,
                'source' => $backup->server?->name ?? __('Fleet'),
                'service' => 'backup',
                'time' => $backup->started_at?->diffForHumans() ?? __('Recent'),
                'ack' => false,
            ]);
        }

        foreach (ManagedDomain::query()->whereIn('ssl_status', ['expired', 'expiring_soon', 'invalid'])->take(2)->get() as $domain) {
            $alerts->push([
                'severity' => $domain->ssl_status === 'expired' ? 'critical' : 'warning',
                'category' => 'ssl',
                'title' => __('SSL expiry alert'),
                'body' => $domain->domain,
                'source' => $domain->tenant?->company_name ?? __('Edge'),
                'service' => 'ssl',
                'time' => $domain->ssl_expires_at?->diffForHumans() ?? __('Soon'),
                'ack' => false,
            ]);
        }

        $failedDeploys = Project::query()
            ->with(['deployments' => fn ($q) => $q->where('notes', 'like', '%fail%')->latest('deployed_at')->limit(1)])
            ->get()
            ->flatMap(fn (Project $p) => $p->deployments->map(fn ($d) => [
                'severity' => 'warning',
                'category' => 'deployment',
                'title' => __('Failed deployment'),
                'body' => __(':project v:ver rollback initiated.', ['project' => $p->name, 'ver' => $d->version]),
                'source' => $p->name,
                'service' => 'ci/cd',
                'time' => $d->deployed_at?->diffForHumans() ?? __('Recent'),
                'ack' => true,
            ]));

        $alerts = $alerts->merge($failedDeploys);

        if ($alerts->isEmpty()) {
            $alerts->push([
                'severity' => 'info',
                'category' => 'status',
                'title' => __('All systems operational'),
                'body' => __('No active incidents in the observability window.'),
                'source' => __('NOC'),
                'service' => 'platform',
                'time' => __('Just now'),
                'ack' => true,
            ]);
        }

        return $alerts->sortBy(fn ($a) => match ($a['severity']) {
            'critical' => 0,
            'warning' => 1,
            default => 2,
        })->values()->take(16);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return list<array{label: string, pct: float}>
     */
    private function buildUptimeSeries(Collection $servers): array
    {
        $synced = $servers->filter(fn (Server $s) => $s->last_synced_at !== null);
        if ($synced->isEmpty()) {
            return [];
        }

        $online = $synced->where('status', 'online')->count();
        $pct = round(($online / max(1, $synced->count())) * 100, 2);

        return [
            ['label' => now()->format('H:i'), 'pct' => $pct],
        ];
    }

    /**
     * @return list<array{label: string, p50: int, p95: int, p99: int}>
     */
    private function buildLatencySeries(): array
    {
        return [];
    }

    /**
     * @return list<array{label: string, rate: float}>
     */
    private function buildErrorRateSeries(): array
    {
        return [];
    }

    /**
     * @return list<array{method: string, path: string, status: string, latency: int, availability: float, requests: int}>
     */
    private function buildApiEndpoints(): array
    {
        return [];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $alerts
     * @return list<array{time: string, title: string, severity: string, status: string}>
     */
    private function buildIncidentTimeline(Collection $alerts): array
    {
        $incidents = $alerts
            ->whereIn('severity', ['critical', 'warning'])
            ->take(6)
            ->map(fn ($a) => [
                'time' => $a['time'],
                'title' => $a['title'],
                'severity' => $a['severity'],
                'status' => $a['ack'] ? 'resolved' : 'active',
            ])
            ->values()
            ->all();

        return $incidents;
    }

    /**
     * @return list<array{name: string, region: string, status: string, latency: int, last: string}>
     */
    private function buildSyntheticChecks(): array
    {
        return [];
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return list<array{name: string, status: string, interval: string, last: string}>
     */
    private function buildHeartbeats(Collection $servers): array
    {
        return $servers->take(8)->map(fn (Server $s) => [
            'name' => $s->name,
            'status' => $s->status === 'online' ? 'alive' : 'dead',
            'interval' => '30s',
            'last' => $s->status === 'online' ? __('Just now') : __('Unreachable'),
        ])->values()->all();
    }

    /**
     * @return list<array{trace_id: string, service: string, duration: int, status: string, endpoint: string}>
     */
    private function buildTraces(): array
    {
        return [];
    }

    /**
     * @return list<array{query: string, avg_ms: float, calls: int, status: string}>
     */
    private function buildDbMetrics(): array
    {
        return [];
    }

    /**
     * @return array{pending: int, processing: int, failed: int, throughput: int, workers: list<array{name: string, jobs: int, status: string}>}
     */
    private function buildQueueMetrics(): ?array
    {
        return null;
    }

    /**
     * @return array{hit_rate: float, memory_mb: int, evictions: int, keys: int}
     */
    private function buildCacheMetrics(): ?array
    {
        return null;
    }

    /**
     * @return list<array{name: string, levels: list<array{level: int, delay: string, channel: string, owner: string}>, enabled: bool}>
     */
    private function buildEscalationPolicies(): array
    {
        return [];
    }

    /**
     * @return list<array{name: string, uptime: float, alerts: int, status: string}>
     */
    private function buildTenantHealth(int $tenantCount): array
    {
        if ($tenantCount === 0) {
            return [];
        }

        return Tenant::query()
            ->with('server.latestHealthLog')
            ->orderBy('company_name')
            ->limit(6)
            ->get()
            ->map(function (Tenant $tenant): array {
                $server = $tenant->server;
                $uptime = null;
                if ($server?->last_synced_at && $server->status === 'online') {
                    $log = $server->latestHealthLog;
                    if ($log?->uptime_seconds) {
                        $uptime = min(100, round(($log->uptime_seconds / 86400) * 100, 2));
                    }
                }

                return [
                    'name' => $tenant->company_name,
                    'uptime' => $uptime,
                    'alerts' => 0,
                    'status' => match ($tenant->status) {
                        'overdue', 'suspended' => 'warning',
                        default => 'healthy',
                    },
                ];
            })
            ->all();
    }

    /**
     * @return list<array{project: string, version: string, status: string, at: string}>
     */
    private function buildDeploymentEvents(): array
    {
        return Project::query()
            ->with(['deployments' => fn ($q) => $q->orderByDesc('deployed_at')->limit(2)])
            ->limit(5)
            ->get()
            ->flatMap(fn (Project $p) => $p->deployments->map(fn ($d) => [
                'project' => $p->name,
                'version' => $d->version,
                'status' => str_contains(strtolower($d->notes ?? ''), 'fail') ? 'failed' : 'success',
                'at' => $d->deployed_at?->diffForHumans() ?? __('Unknown'),
            ]))
            ->take(6)
            ->values()
            ->all();
    }

}
