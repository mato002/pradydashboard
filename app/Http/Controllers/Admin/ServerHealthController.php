<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoMode;
use App\Support\OperationalMetrics;
use App\Models\Backup;
use App\Models\Project;
use App\Models\Server;
use App\Models\ServerHealthLog;
use Database\Seeders\ServerHealthDemoSeeder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ServerHealthController extends Controller
{
    public function index(): View
    {
        if (DemoMode::enabled() && ServerHealthLog::query()->doesntExist()) {
            (new ServerHealthDemoSeeder)->run();
        }

        $servers = Server::query()
            ->with(['latestHealthLog', 'healthLogs' => fn ($q) => $q->orderByDesc('checked_at')->limit(48)])
            ->withCount(['tenants', 'projects'])
            ->orderBy('name')
            ->get();

        $onlineServers = $servers->where('status', 'online');
        $logsWithMetrics = $onlineServers
            ->map(fn (Server $s) => $s->latestHealthLog)
            ->filter();

        $avgCpu = $logsWithMetrics->avg('cpu_percent');
        $avgRam = $logsWithMetrics->avg('ram_percent');
        $avgDisk = $onlineServers->avg(fn (Server $s) => (float) ($s->disk_usage_percent ?? $s->latestHealthLog?->disk_percent ?? 0));

        $fleetCards = $servers->map(fn (Server $s) => $this->buildFleetCard($s))->values();
        $alerts = $this->buildAlerts($servers);
        $activeAlerts = $alerts->whereIn('severity', ['WARNING', 'CRITICAL'])->count();

        $kpis = [
            'total' => $servers->count(),
            'online' => $onlineServers->count(),
            'avgCpu' => $avgCpu !== null ? round($avgCpu, 1) : 0,
            'avgRam' => $avgRam !== null ? round($avgRam, 1) : 0,
            'avgDisk' => $avgDisk !== null ? round($avgDisk, 1) : 0,
            'activeAlerts' => $activeAlerts,
        ];

        $spark = fn (string $key) => OperationalMetrics::emptySparkline();

        $fleetCpuSeries = $this->buildFleetAggregateSeries($servers, 'cpu_percent');
        $fleetRamSeries = $this->buildFleetAggregateSeries($servers, 'ram_percent');
        $networkSeries = $this->buildNetworkSeries();
        $uptimeHistory = $this->buildUptimeHistory($servers);
        $incidentTrends = $this->buildIncidentTrends();
        $utilizationTrends = $this->buildUtilizationTrends($servers);

        $detailPayload = $fleetCards->keyBy('id')->all();

        return view('admin.server-health.index', compact(
            'kpis',
            'spark',
            'fleetCards',
            'alerts',
            'fleetCpuSeries',
            'fleetRamSeries',
            'networkSeries',
            'uptimeHistory',
            'incidentTrends',
            'utilizationTrends',
            'detailPayload',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFleetCard(Server $server): array
    {
        $log = $server->latestHealthLog;
        $cpu = $log?->cpu_percent;
        $ram = $log?->ram_percent;
        $disk = $server->disk_usage_percent ?? $log?->disk_percent;
        $health = $this->resolveHealth($server, $cpu, $ram, $disk);

        $cpuSeries = $server->healthLogs
            ->sortBy('checked_at')
            ->pluck('cpu_percent')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        $ramSeries = $server->healthLogs
            ->sortBy('checked_at')
            ->pluck('ram_percent')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        $loadAvg = $cpu !== null
            ? round(max(0.1, ((float) $cpu / 100) * ($server->cpu_cores ?: 4)), 2)
            : null;

        return [
            'id' => $server->id,
            'name' => $server->name,
            'ip' => $server->ip_address ?? '—',
            'provider' => $server->provider ?? '—',
            'status' => $server->status,
            'health' => $health,
            'cpu' => $cpu !== null ? round((float) $cpu, 1) : null,
            'ram' => $ram !== null ? round((float) $ram, 1) : null,
            'disk' => $disk !== null ? round((float) $disk, 1) : null,
            'uptime' => $this->formatUptime($log?->uptime_seconds),
            'uptime_seconds' => $log?->uptime_seconds,
            'load_avg' => $loadAvg,
            'ping_ms' => $this->pingMs($server),
            'bandwidth_in' => $this->bandwidthMbps($server, 'in'),
            'bandwidth_out' => $this->bandwidthMbps($server, 'out'),
            'tenants' => $server->tenants_count,
            'projects' => $server->projects_count,
            'cpu_series' => $cpuSeries,
            'ram_series' => $ramSeries,
            'net_series' => $cpuSeries ? $this->buildNetSeriesForServer($server) : [],
            'ssl_status' => $server->ssl_status ?? 'unknown',
            'backup_status' => $server->backup_status ?? 'unknown',
            'specs' => [
                'cpu_cores' => $server->cpu_cores,
                'ram_gb' => $server->ram_gb,
                'storage_gb' => $server->storage_gb,
            ],
            'services' => $this->runningServices($server),
            'tenants_list' => $server->tenants()->limit(6)->pluck('company_name')->all(),
            'deployments' => $this->recentDeployments($server),
            'logs' => $this->recentLogs($server, $health),
            'show_url' => route('servers.show', $server),
        ];
    }

    private function resolveHealth(Server $server, mixed $cpu, mixed $ram, mixed $disk): string
    {
        if ($server->status === 'offline') {
            return 'offline';
        }

        if ($server->status !== 'online') {
            return 'warning';
        }

        $cpuF = $cpu !== null ? (float) $cpu : 0;
        $ramF = $ram !== null ? (float) $ram : 0;
        $diskF = $disk !== null ? (float) $disk : 0;

        if ($cpuF >= 90 || $ramF >= 92 || $diskF >= 95) {
            return 'critical';
        }

        if ($cpuF >= 75 || $ramF >= 80 || $diskF >= 85) {
            return 'warning';
        }

        return 'healthy';
    }

    private function formatUptime(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) {
            return '—';
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);

        if ($days > 0) {
            return sprintf('%dd %dh', $days, $hours);
        }

        return sprintf('%dh %dm', $hours, intdiv($seconds % 3600, 60));
    }

    private function pingMs(Server $server): ?int
    {
        return null;
    }

    private function bandwidthMbps(Server $server, string $direction): ?float
    {
        return null;
    }

    /**
     * @return array<int, float>
     */
    private function buildNetSeriesForServer(Server $server): array
    {
        return [];
    }

    /**
     * @return list<array{name: string, status: string}>
     */
    private function runningServices(Server $server): array
    {
        if (! $server->last_synced_at) {
            return [];
        }

        if ($server->status === 'offline') {
            return [
                ['name' => __('Reachability'), 'status' => 'stopped'],
            ];
        }

        return [
            ['name' => __('Reachability'), 'status' => 'running'],
        ];
    }

    /**
     * @return list<array{version: string, at: string, project: string}>
     */
    private function recentDeployments(Server $server): array
    {
        return Project::query()
            ->where('server_id', $server->id)
            ->with(['deployments' => fn ($q) => $q->orderByDesc('deployed_at')->limit(3)])
            ->limit(3)
            ->get()
            ->flatMap(fn (Project $project) => $project->deployments->map(fn ($d) => [
                'version' => $d->version,
                'at' => $d->deployed_at?->diffForHumans() ?? __('Unknown'),
                'project' => $project->name,
            ]))
            ->take(4)
            ->values()
            ->all();
    }

    /**
     * @return list<array{level: string, message: string, at: string}>
     */
    private function recentLogs(Server $server, string $health): array
    {
        $entries = [
            ['level' => 'info', 'message' => __('Health probe completed for :host', ['host' => $server->name]), 'at' => __('Just now')],
        ];

        if ($health === 'critical') {
            $entries[] = ['level' => 'critical', 'message' => __('Resource threshold exceeded — auto-scaling policy engaged'), 'at' => __('2m ago')];
        }

        if ($health === 'warning') {
            $entries[] = ['level' => 'warning', 'message' => __('Elevated load average detected on :host', ['host' => $server->name]), 'at' => __('8m ago')];
        }

        if ($server->backup_status === 'failed') {
            $entries[] = ['level' => 'critical', 'message' => __('Backup agent reported failure on last snapshot window'), 'at' => __('1h ago')];
        }

        if (in_array($server->ssl_status, ['expiring', 'expired'], true)) {
            $entries[] = ['level' => 'warning', 'message' => __('TLS certificate requires renewal attention'), 'at' => __('3h ago')];
        }

        return array_slice($entries, 0, 5);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return Collection<int, array{severity: string, title: string, body: string, server: string, time: string}>
     */
    private function buildAlerts(Collection $servers): Collection
    {
        $alerts = collect();

        foreach ($servers as $server) {
            $log = $server->latestHealthLog;
            $cpu = (float) ($log?->cpu_percent ?? 0);
            $ram = (float) ($log?->ram_percent ?? 0);
            $disk = (float) ($server->disk_usage_percent ?? $log?->disk_percent ?? 0);

            if ($server->status === 'offline') {
                $alerts->push([
                    'severity' => 'CRITICAL',
                    'title' => __('Host unreachable'),
                    'body' => __(':server is not responding to ICMP or agent heartbeat.', ['server' => $server->name]),
                    'server' => $server->name,
                    'time' => __('Live'),
                ]);

                continue;
            }

            if ($cpu >= 85) {
                $alerts->push([
                    'severity' => 'CRITICAL',
                    'title' => __('High CPU usage'),
                    'body' => __(':server CPU at :pct% — investigate runaway processes.', ['server' => $server->name, 'pct' => (int) $cpu]),
                    'server' => $server->name,
                    'time' => __('Live'),
                ]);
            } elseif ($cpu >= 70) {
                $alerts->push([
                    'severity' => 'WARNING',
                    'title' => __('Elevated CPU'),
                    'body' => __(':server sustained load above baseline.', ['server' => $server->name]),
                    'server' => $server->name,
                    'time' => __('5m ago'),
                ]);
            }

            if ($disk >= 90) {
                $alerts->push([
                    'severity' => 'CRITICAL',
                    'title' => __('Low disk space'),
                    'body' => __(':server volume at :pct% capacity.', ['server' => $server->name, 'pct' => (int) $disk]),
                    'server' => $server->name,
                    'time' => __('Live'),
                ]);
            } elseif ($disk >= 80) {
                $alerts->push([
                    'severity' => 'WARNING',
                    'title' => __('Disk threshold'),
                    'body' => __('Plan expansion for :server block storage.', ['server' => $server->name]),
                    'server' => $server->name,
                    'time' => __('12m ago'),
                ]);
            }

            if ($ram >= 88) {
                $alerts->push([
                    'severity' => 'WARNING',
                    'title' => __('Memory spike'),
                    'body' => __(':server RAM at :pct% — cache pressure likely.', ['server' => $server->name, 'pct' => (int) $ram]),
                    'server' => $server->name,
                    'time' => __('3m ago'),
                ]);
            }

            if (in_array($server->ssl_status, ['expiring', 'expired'], true)) {
                $alerts->push([
                    'severity' => $server->ssl_status === 'expired' ? 'CRITICAL' : 'WARNING',
                    'title' => __('SSL expiry'),
                    'body' => __('Certificate on :server needs renewal.', ['server' => $server->name]),
                    'server' => $server->name,
                    'time' => __('Ops'),
                ]);
            }

            if (in_array($server->backup_status, ['failed', 'error'], true)) {
                $alerts->push([
                    'severity' => 'CRITICAL',
                    'title' => __('Backup failure'),
                    'body' => __('Last snapshot on :server did not complete successfully.', ['server' => $server->name]),
                    'server' => $server->name,
                    'time' => __('1h ago'),
                ]);
            }
        }

        foreach (Backup::query()->where('status', 'failed')->latest('started_at')->take(2)->get() as $backup) {
            $alerts->push([
                'severity' => 'WARNING',
                'title' => __('Backup job failed'),
                'body' => $backup->name,
                'server' => $backup->server?->name ?? __('Fleet'),
                'time' => $backup->started_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'severity' => 'INFO',
                'title' => __('All systems nominal'),
                'body' => __('No critical infrastructure alerts in the current window.'),
                'server' => __('Fleet'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->sortBy(fn ($a) => match ($a['severity']) {
            'CRITICAL' => 0,
            'WARNING' => 1,
            default => 2,
        })->values()->take(12);
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return array<int, float>
     */
    private function buildFleetAggregateSeries(Collection $servers, string $field): array
    {
        $online = $servers->where('status', 'online');
        if ($online->isEmpty()) {
            return [];
        }

        $points = [];
        for ($i = 0; $i < 24; $i++) {
            $sum = 0;
            $count = 0;
            foreach ($online as $server) {
                $log = $server->healthLogs->sortBy('checked_at')->values()->get($i);
                if ($log && $log->{$field} !== null) {
                    $sum += (float) $log->{$field};
                    $count++;
                }
            }
            $points[] = $count > 0 ? round($sum / $count, 1) : 0;
        }

        return $points;
    }

    /**
     * @return list<array{label: string, in: float, out: float}>
     */
    private function buildNetworkSeries(): array
    {
        return [];
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return list<array{label: string, pct: float}>
     */
    private function buildUptimeHistory(Collection $servers): array
    {
        $synced = $servers->filter(fn (Server $s) => $s->last_synced_at !== null);
        if ($synced->isEmpty()) {
            return [];
        }

        $pct = round(($synced->where('status', 'online')->count() / max(1, $synced->count())) * 100, 2);

        return [
            ['label' => now()->format('D'), 'pct' => $pct],
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function buildIncidentTrends(): array
    {
        return [];
    }

    /**
     * @param  Collection<int, Server>  $servers
     * @return array{cpu: array<int, float>, ram: array<int, float>, disk: array<int, float>}
     */
    private function buildUtilizationTrends(Collection $servers): array
    {
        return [
            'cpu' => $this->buildFleetAggregateSeries($servers, 'cpu_percent'),
            'ram' => $this->buildFleetAggregateSeries($servers, 'ram_percent'),
            'disk' => $this->buildFleetAggregateSeries($servers, 'disk_percent'),
        ];
    }

}
