<?php

namespace App\Domain\Audit;

use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuditEventAggregator
{
    /** @var list<string> */
    public const EVENT_TYPES = [
        'Login',
        'Deployment',
        'Payment',
        'Backup',
        'API',
        'Tenant Action',
        'Server Event',
        'Security Alert',
    ];

    /** @var list<string> */
    public const SEVERITIES = ['info', 'success', 'warning', 'error', 'critical'];

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collect(): Collection
    {
        return collect()
            ->merge($this->fromTenantLogs())
            ->merge($this->syntheticInfrastructureEvents())
            ->sortByDesc(fn (array $e): int => $e['timestamp']->timestamp)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filter(Collection $events, array $filters): Collection
    {
        return $events->filter(function (array $event) use ($filters): bool {
            if ($search = trim((string) ($filters['q'] ?? ''))) {
                $haystack = strtolower(implode(' ', [
                    $event['event_type'],
                    $event['user'],
                    $event['tenant'] ?? '',
                    $event['ip'],
                    $event['module'],
                    $event['description'],
                    $event['status'],
                ]));
                if (! str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }

            if ($type = $filters['event_type'] ?? null) {
                if ($event['event_type'] !== $type) {
                    return false;
                }
            }

            if ($severity = $filters['severity'] ?? null) {
                if ($event['severity'] !== $severity) {
                    return false;
                }
            }

            if ($tenant = $filters['tenant'] ?? null) {
                if (($event['tenant'] ?? '') !== $tenant) {
                    return false;
                }
            }

            if ($user = $filters['user'] ?? null) {
                if ($event['user'] !== $user) {
                    return false;
                }
            }

            if ($module = $filters['module'] ?? null) {
                if ($event['module'] !== $module) {
                    return false;
                }
            }

            if ($server = $filters['server'] ?? null) {
                if (($event['server'] ?? '') !== $server) {
                    return false;
                }
            }

            if ($from = $filters['from'] ?? null) {
                $start = Carbon::parse($from)->startOfDay();
                if ($event['timestamp']->lt($start)) {
                    return false;
                }
            }

            if ($to = $filters['to'] ?? null) {
                $end = Carbon::parse($to)->endOfDay();
                if ($event['timestamp']->gt($end)) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return array<string, int|float>
     */
    public function kpis(Collection $events): array
    {
        $today = now()->startOfDay();

        $todayEvents = $events->filter(fn (array $e): bool => $e['timestamp']->gte($today));

        return [
            'total_today' => $todayEvents->count(),
            'security_events' => $events->whereIn('event_type', ['Security Alert', 'Login'])
                ->whereIn('severity', ['warning', 'error', 'critical'])->count(),
            'failed_logins' => $events->where('event_type', 'Login')->where('status', 'failed')->count(),
            'api_requests' => $events->where('event_type', 'API')->count(),
            'tenant_activities' => $events->where('event_type', 'Tenant Action')->count(),
            'critical_alerts' => $events->where('severity', 'critical')->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<int>
     */
    public function sparkline(Collection $events, string $key, int $points = 12): array
    {
        $buckets = array_fill(0, $points, 0);
        $start = now()->subHours($points - 1)->startOfHour();

        foreach ($events as $event) {
            if (! $this->matchesKpiKey($event, $key)) {
                continue;
            }
            $hoursAgo = (int) $start->diffInHours($event['timestamp']->copy()->startOfHour(), false);
            $idx = max(0, min($points - 1, $points - 1 + $hoursAgo));
            if ($idx >= 0 && $idx < $points) {
                $buckets[$idx]++;
            }
        }

        $max = max($buckets) ?: 1;

        return array_map(fn (int $v): int => (int) round(($v / $max) * 100), $buckets);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return array<int, array<int, int>>
     */
    public function heatmap(Collection $events): array
    {
        $grid = [];
        for ($d = 0; $d < 7; $d++) {
            for ($h = 0; $h < 24; $h++) {
                $grid[$d][$h] = 0;
            }
        }

        $start = now()->subDays(6)->startOfDay();

        foreach ($events as $event) {
            if ($event['timestamp']->lt($start)) {
                continue;
            }
            $day = (int) $start->diffInDays($event['timestamp']->copy()->startOfDay());
            $hour = (int) $event['timestamp']->format('G');
            if ($day >= 0 && $day < 7 && $hour >= 0 && $hour < 24) {
                $grid[$day][$hour]++;
            }
        }

        return $grid;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<array{label: string, value: int}>
     */
    public function timeline(Collection $events, int $days = 7): array
    {
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $series[] = [
                'label' => $day->format('D'),
                'value' => $events->filter(fn (array $e): bool => $e['timestamp']->isSameDay($day))->count(),
            ];
        }

        return $series;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<array{label: string, value: int, pct: float}>
     */
    public function breakdown(Collection $events, string $field): array
    {
        $counts = $events->groupBy($field)->map->count()->sortDesc();
        $total = max(1, $counts->sum());

        return $counts->take(5)->map(function (int $count, string $label) use ($total): array {
            return [
                'label' => $label ?: '—',
                'value' => $count,
                'pct' => round(($count / $total) * 100, 1),
            ];
        })->values()->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fromTenantLogs(): Collection
    {
        return collect(
            TenantActivityLog::query()
                ->with(['tenant.project.server', 'user'])
                ->orderByDesc('created_at')
                ->limit(200)
                ->get()
                ->map(fn (TenantActivityLog $log): array => $this->mapTenantLog($log))
                ->all()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTenantLog(TenantActivityLog $log): array
    {
        $action = $log->action;
        $eventType = 'Tenant Action';
        $severity = 'info';
        $module = 'Tenancy';
        $status = 'success';

        if (str_contains($action, 'module')) {
            $module = 'Access Control';
        }

        if (str_contains($action, 'payment') || str_contains($action, 'invoice')) {
            $eventType = 'Payment';
            $module = 'Billing';
        }

        if (str_contains($action, 'security') || str_contains($action, 'restrict')) {
            $eventType = 'Security Alert';
            $severity = 'warning';
            $module = 'Access Control';
        }

        $serverName = $log->tenant?->project?->server?->name;

        return [
            'id' => 'tal-'.$log->id,
            'timestamp' => $log->created_at ?? now(),
            'event_type' => $eventType,
            'user' => $log->user?->name ?? 'System',
            'tenant' => $log->tenant?->company_name,
            'ip' => $this->pseudoIp($log->id, 'tenant'),
            'module' => $module,
            'server' => $serverName,
            'severity' => $severity,
            'description' => $log->summary ?? Str::headline(str_replace('.', ' ', $action)),
            'status' => $status,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function syntheticInfrastructureEvents(): Collection
    {
        $events = collect();
        $users = User::query()->pluck('name', 'id');
        $staffNames = $users->isNotEmpty() ? $users->values()->all() : ['Admin', 'Ops Lead', 'DevOps'];

        $templates = [
            ['type' => 'Login', 'module' => 'Auth', 'severity' => 'info', 'status' => 'success', 'desc' => 'Dashboard session established'],
            ['type' => 'Login', 'module' => 'Auth', 'severity' => 'error', 'status' => 'failed', 'desc' => 'Invalid credentials — 3 attempts'],
            ['type' => 'API', 'module' => 'License API', 'severity' => 'info', 'status' => 'success', 'desc' => 'POST /api/v1/license/check — 200 OK'],
            ['type' => 'API', 'module' => 'License API', 'severity' => 'warning', 'status' => 'success', 'desc' => 'Rate limit threshold at 82%'],
            ['type' => 'Deployment', 'module' => 'CI/CD', 'severity' => 'success', 'status' => 'success', 'desc' => 'Production deploy v2.4.1 completed'],
            ['type' => 'Deployment', 'module' => 'CI/CD', 'severity' => 'warning', 'status' => 'pending', 'desc' => 'Staging rollout awaiting approval'],
            ['type' => 'Backup', 'module' => 'Infrastructure', 'severity' => 'success', 'status' => 'success', 'desc' => 'Nightly snapshot verified'],
            ['type' => 'Backup', 'module' => 'Infrastructure', 'severity' => 'error', 'status' => 'failed', 'desc' => 'Backup job exceeded retention window'],
            ['type' => 'Payment', 'module' => 'Billing', 'severity' => 'success', 'status' => 'success', 'desc' => 'Subscription renewal processed'],
            ['type' => 'Payment', 'module' => 'Billing', 'severity' => 'warning', 'status' => 'pending', 'desc' => 'Invoice overdue — grace period active'],
            ['type' => 'Server Event', 'module' => 'Infrastructure', 'severity' => 'info', 'status' => 'success', 'desc' => 'Health probe passed — all metrics nominal'],
            ['type' => 'Server Event', 'module' => 'Infrastructure', 'severity' => 'warning', 'status' => 'success', 'desc' => 'Disk usage crossed 78% threshold'],
            ['type' => 'Security Alert', 'module' => 'Access Control', 'severity' => 'critical', 'status' => 'failed', 'desc' => 'Privilege escalation attempt blocked'],
            ['type' => 'Security Alert', 'module' => 'Access Control', 'severity' => 'error', 'status' => 'failed', 'desc' => 'API token validation failed from unknown IP'],
            ['type' => 'Tenant Action', 'module' => 'Tenancy', 'severity' => 'info', 'status' => 'success', 'desc' => 'Module entitlement updated'],
        ];

        $tenants = Tenant::query()->with('project.server')->limit(12)->get();
        $servers = Server::query()->limit(8)->get();

        $offset = 0;
        foreach ($templates as $i => $tpl) {
            $tenant = $tenants->get($i % max(1, $tenants->count()));
            $server = $servers->get($i % max(1, $servers->count())) ?? $tenant?->project?->server;
            $minutesAgo = ($i * 17) + ($offset % 43);

            $events->push([
                'id' => 'syn-'.md5($tpl['type'].$i),
                'timestamp' => now()->subMinutes($minutesAgo),
                'event_type' => $tpl['type'],
                'user' => $staffNames[$i % count($staffNames)],
                'tenant' => $tenant?->company_name,
                'ip' => $this->pseudoIp($i, 'infra'),
                'module' => $tpl['module'],
                'server' => $server?->name,
                'severity' => $tpl['severity'],
                'description' => $tpl['desc'],
                'status' => $tpl['status'],
            ]);
            $offset += 7;
        }

        foreach ($servers as $idx => $server) {
            $events->push([
                'id' => 'srv-'.$server->id,
                'timestamp' => now()->subHours($idx + 2),
                'event_type' => 'Server Event',
                'user' => 'Health Agent',
                'tenant' => null,
                'ip' => $server->ip_address ?? $this->pseudoIp($server->id, 'srv'),
                'module' => 'Infrastructure',
                'server' => $server->name,
                'severity' => $server->status === 'offline' ? 'critical' : ($server->status === 'online' ? 'success' : 'warning'),
                'description' => match ($server->status) {
                    'offline' => 'Node unreachable — failover checks initiated',
                    'online' => 'Heartbeat OK — CPU/RAM within SLO',
                    default => 'Intermittent latency detected on health checks',
                },
                'status' => $server->status === 'offline' ? 'failed' : 'success',
            ]);
        }

        return $events;
    }

    private function pseudoIp(int|string $seed, string $salt): string
    {
        $hash = crc32((string) $seed.$salt);

        return sprintf(
            '10.%d.%d.%d',
            ($hash >> 16) & 0xFF,
            ($hash >> 8) & 0xFF,
            $hash & 0xFF
        );
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function matchesKpiKey(array $event, string $key): bool
    {
        return match ($key) {
            'events' => true,
            'security' => in_array($event['event_type'], ['Security Alert', 'Login'], true)
                && in_array($event['severity'], ['warning', 'error', 'critical'], true),
            'failed_logins' => $event['event_type'] === 'Login' && $event['status'] === 'failed',
            'api' => $event['event_type'] === 'API',
            'tenant' => $event['event_type'] === 'Tenant Action',
            'critical' => $event['severity'] === 'critical',
            default => false,
        };
    }
}
