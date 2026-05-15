<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
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
        if (ServerHealthLog::query()->doesntExist()) {
            (new ServerHealthDemoSeeder)->run();
        }

        $servers = Server::query()->with('latestHealthLog')->get();
        $tenants = Tenant::query()->count();
        $projects = Project::query()->count();

        $onlineCount = $servers->where('status', 'online')->count();
        $totalServers = max(1, $servers->count());
        $systemUptime = round(($onlineCount / $totalServers) * 100, 2);

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

        $spark = fn (string $key) => $this->pseudoSparkline($key);

        return view('admin.monitoring.index', [
            'kpis' => $kpis,
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

    private function errorRate(): float
    {
        return round(0.12 + (crc32('err-'.now()->format('H')) % 28) / 100, 2);
    }

    /**
     * @param  Collection<int, Server>  $servers
     */
    private function avgResponseMs(Collection $servers): int
    {
        if ($servers->where('status', 'online')->isEmpty()) {
            return 0;
        }

        $base = 142;
        $loadFactor = $servers->avg(fn (Server $s) => (float) ($s->latestHealthLog?->cpu_percent ?? 40));

        return (int) round($base + ($loadFactor * 1.8));
    }

    private function apiAvailability(): float
    {
        return round(99.2 + (crc32('api-'.now()->format('d')) % 8) / 10, 2);
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
        $series = [];
        for ($i = 23; $i >= 0; $i--) {
            $online = $servers->where('status', 'online')->count();
            $total = max(1, $servers->count());
            $base = ($online / $total) * 100;

            $series[] = [
                'label' => now()->subHours($i)->format('H:i'),
                'pct' => round(min(100, max(97.5, $base + (($i % 5) - 2) * 0.15)), 2),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{label: string, p50: int, p95: int, p99: int}>
     */
    private function buildLatencySeries(): array
    {
        $series = [];
        for ($i = 23; $i >= 0; $i--) {
            $wave = sin($i / 4) * 40;
            $series[] = [
                'label' => now()->subHours($i)->format('H:i'),
                'p50' => (int) round(118 + $wave * 0.4),
                'p95' => (int) round(280 + $wave),
                'p99' => (int) round(520 + $wave * 1.4),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{label: string, rate: float}>
     */
    private function buildErrorRateSeries(): array
    {
        $series = [];
        for ($i = 23; $i >= 0; $i--) {
            $series[] = [
                'label' => now()->subHours($i)->format('H:i'),
                'rate' => round(0.08 + abs(sin($i / 3)) * 0.22 + ($i % 4) * 0.02, 2),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{method: string, path: string, status: string, latency: int, availability: float, requests: int}>
     */
    private function buildApiEndpoints(): array
    {
        $endpoints = [
            ['method' => 'GET', 'path' => '/api/v1/health', 'base' => 12, 'avail' => 99.99],
            ['method' => 'POST', 'path' => '/api/v1/licenses/validate', 'base' => 89, 'avail' => 99.95],
            ['method' => 'GET', 'path' => '/api/v1/tenants/{id}', 'base' => 145, 'avail' => 99.92],
            ['method' => 'POST', 'path' => '/api/v1/webhooks/dispatch', 'base' => 210, 'avail' => 99.88],
            ['method' => 'GET', 'path' => '/api/v1/deployments/status', 'base' => 178, 'avail' => 99.90],
            ['method' => 'POST', 'path' => '/api/v1/backups/trigger', 'base' => 340, 'avail' => 99.75],
        ];

        return collect($endpoints)->map(fn ($ep, $i) => [
            'method' => $ep['method'],
            'path' => $ep['path'],
            'status' => $ep['avail'] >= 99.9 ? 'healthy' : ($ep['avail'] >= 99.5 ? 'degraded' : 'critical'),
            'latency' => $ep['base'] + ($i * 7) % 45,
            'availability' => $ep['avail'],
            'requests' => 1200 + ($i * 431) % 8000,
        ])->all();
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

        if (count($incidents) < 4) {
            $incidents[] = ['time' => __('6h ago'), 'title' => __('Scheduled maintenance — edge nodes'), 'severity' => 'info', 'status' => 'resolved'];
            $incidents[] = ['time' => __('Yesterday'), 'title' => __('Redis failover drill completed'), 'severity' => 'info', 'status' => 'resolved'];
        }

        return $incidents;
    }

    /**
     * @return list<array{name: string, region: string, status: string, latency: int, last: string}>
     */
    private function buildSyntheticChecks(): array
    {
        return [
            ['name' => 'Login flow (EU)', 'region' => 'Frankfurt', 'status' => 'pass', 'latency' => 412, 'last' => __('1m ago')],
            ['name' => 'Checkout API (US)', 'region' => 'Virginia', 'status' => 'pass', 'latency' => 298, 'last' => __('1m ago')],
            ['name' => 'License webhook', 'region' => 'Singapore', 'status' => 'pass', 'latency' => 521, 'last' => __('2m ago')],
            ['name' => 'Admin dashboard', 'region' => 'London', 'status' => 'degraded', 'latency' => 1840, 'last' => __('3m ago')],
            ['name' => 'Tenant portal', 'region' => 'Sydney', 'status' => 'pass', 'latency' => 678, 'last' => __('1m ago')],
        ];
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
        return [
            ['trace_id' => 'tr_8f2a9c1e', 'service' => 'api-gateway', 'duration' => 342, 'status' => 'ok', 'endpoint' => 'POST /licenses/validate'],
            ['trace_id' => 'tr_3b7d4e2f', 'service' => 'tenant-svc', 'duration' => 891, 'status' => 'slow', 'endpoint' => 'GET /tenants/{id}/modules'],
            ['trace_id' => 'tr_9c1a5b8d', 'service' => 'worker', 'duration' => 1240, 'status' => 'error', 'endpoint' => 'queue:deployments'],
            ['trace_id' => 'tr_2e6f0a3c', 'service' => 'billing', 'duration' => 156, 'status' => 'ok', 'endpoint' => 'POST /invoices/sync'],
            ['trace_id' => 'tr_7d4b1e9a', 'service' => 'cache', 'duration' => 12, 'status' => 'ok', 'endpoint' => 'redis:get session'],
        ];
    }

    /**
     * @return list<array{query: string, avg_ms: float, calls: int, status: string}>
     */
    private function buildDbMetrics(): array
    {
        return [
            ['query' => 'SELECT tenants WHERE status = ?', 'avg_ms' => 4.2, 'calls' => 18420, 'status' => 'healthy'],
            ['query' => 'JOIN projects_deployments', 'avg_ms' => 28.6, 'calls' => 3201, 'status' => 'healthy'],
            ['query' => 'UPDATE server_health_logs', 'avg_ms' => 12.1, 'calls' => 9600, 'status' => 'healthy'],
            ['query' => 'AGG usage_metrics BY tenant', 'avg_ms' => 142.8, 'calls' => 890, 'status' => 'slow'],
        ];
    }

    /**
     * @return array{pending: int, processing: int, failed: int, throughput: int, workers: list<array{name: string, jobs: int, status: string}>}
     */
    private function buildQueueMetrics(): array
    {
        return [
            'pending' => 23,
            'processing' => 8,
            'failed' => 2,
            'throughput' => 184,
            'workers' => [
                ['name' => 'deployments', 'jobs' => 5, 'status' => 'busy'],
                ['name' => 'notifications', 'jobs' => 12, 'status' => 'healthy'],
                ['name' => 'backups', 'jobs' => 1, 'status' => 'healthy'],
                ['name' => 'webhooks', 'jobs' => 3, 'status' => 'degraded'],
            ],
        ];
    }

    /**
     * @return array{hit_rate: float, memory_mb: int, evictions: int, keys: int}
     */
    private function buildCacheMetrics(): array
    {
        return [
            'hit_rate' => 94.6,
            'memory_mb' => 512,
            'evictions' => 128,
            'keys' => 48291,
        ];
    }

    /**
     * @return list<array{name: string, levels: list<array{level: int, delay: string, channel: string, owner: string}>, enabled: bool}>
     */
    private function buildEscalationPolicies(): array
    {
        return [
            [
                'name' => 'Platform Critical',
                'enabled' => true,
                'levels' => [
                    ['level' => 1, 'delay' => '0m', 'channel' => 'PagerDuty', 'owner' => 'NOC On-call'],
                    ['level' => 2, 'delay' => '15m', 'channel' => 'Slack #incidents', 'owner' => 'Engineering Lead'],
                    ['level' => 3, 'delay' => '30m', 'channel' => 'Email + SMS', 'owner' => 'VP Engineering'],
                ],
            ],
            [
                'name' => 'Tenant SLA Breach',
                'enabled' => true,
                'levels' => [
                    ['level' => 1, 'delay' => '5m', 'channel' => 'Slack #support-escalation', 'owner' => 'Support Manager'],
                    ['level' => 2, 'delay' => '20m', 'channel' => 'PagerDuty', 'owner' => 'Customer Success'],
                ],
            ],
            [
                'name' => 'Security & Compliance',
                'enabled' => true,
                'levels' => [
                    ['level' => 1, 'delay' => '0m', 'channel' => 'Security hotline', 'owner' => 'SecOps'],
                    ['level' => 2, 'delay' => '10m', 'channel' => 'Email CISO', 'owner' => 'CISO Office'],
                ],
            ],
        ];
    }

    /**
     * @return list<array{name: string, uptime: float, alerts: int, status: string}>
     */
    private function buildTenantHealth(int $tenantCount): array
    {
        $samples = Tenant::query()->orderBy('company_name')->limit(6)->pluck('company_name');

        if ($samples->isEmpty()) {
            return [
                ['name' => 'Acme Corp', 'uptime' => 99.98, 'alerts' => 0, 'status' => 'healthy'],
                ['name' => 'Globex SaaS', 'uptime' => 99.91, 'alerts' => 1, 'status' => 'warning'],
                ['name' => 'Initech', 'uptime' => 99.99, 'alerts' => 0, 'status' => 'healthy'],
            ];
        }

        return $samples->map(fn (string $name, int $i) => [
            'name' => $name,
            'uptime' => round(99.85 + (($i * 17) % 14) / 100, 2),
            'alerts' => $i % 3 === 1 ? 1 : 0,
            'status' => $i % 3 === 1 ? 'warning' : 'healthy',
        ])->all();
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

    /**
     * @return array<int, float>
     */
    private function pseudoSparkline(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 12; $i++) {
            $pts[] = 28 + (($h >> ($i * 3)) & 0x3F) % 52;
        }

        return $pts;
    }
}
