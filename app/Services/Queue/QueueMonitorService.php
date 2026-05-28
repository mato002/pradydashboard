<?php

namespace App\Services\Queue;

use App\Support\Cache\OperationalCache;
use App\Support\Queue\QueueName;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class QueueMonitorService
{
    public function __construct(
        private readonly OperationalCache $operationalCache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $checkedAt = now();

        Cache::put('queue:monitor:last_check', $checkedAt->toIso8601String(), 3600);

        $redis = $this->redisStatus();
        $queues = $this->queueCards();
        $failedJobsCount = $this->failedJobsCount();
        $recentFailedJobs = $this->recentFailedJobs(15);
        $horizon = $this->horizonStatus();
        $totalPending = (int) collect($queues)->sum('pending');
        $busiestQueue = collect($queues)
            ->sortByDesc('pending')
            ->first();
        $pendingHistory = $this->recordPendingHistory($totalPending);
        $liveness = $this->liveness($redis, $horizon, $totalPending, $failedJobsCount, $queues);

        return [
            'checked_at' => $checkedAt,
            'health' => [
                'redis' => $redis,
                'horizon' => $horizon,
                'total_pending' => $totalPending,
                'failed_jobs_count' => $failedJobsCount,
                'busiest_queue' => $busiestQueue && ($busiestQueue['pending'] ?? 0) > 0
                    ? ['queue' => $busiestQueue['queue'], 'pending' => $busiestQueue['pending']]
                    : null,
                'last_failed_at' => $this->lastFailedAt(),
                'queues_clear' => $totalPending === 0,
                'has_failures' => $failedJobsCount > 0,
                'overall_status' => $liveness['overall_status'],
                'overall_label' => $liveness['overall_label'],
                'overall_detail' => $liveness['overall_detail'],
            ],
            'liveness' => $liveness,
            'pending_history' => $pendingHistory,
            'redis' => $redis,
            'queues' => $queues,
            'failed_jobs_count' => $failedJobsCount,
            'recent_failed_jobs' => $recentFailedJobs,
            'horizon_enabled' => $horizon['available'],
            'horizon' => $horizon,
            'horizon_path' => config('horizon.path', 'horizon'),
            'queue_connection' => config('queue.default'),
            'operational_cache_enabled' => $this->operationalCache->enabled(),
            'guidance' => $this->operationalGuidance(),
            'worker_guidance' => $this->workerGuidanceLines(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function redisStatus(): array
    {
        $cacheOk = $this->operationalCache->ping();
        $redisOk = false;
        $memoryMb = null;
        $latencyMs = null;
        $connectedClients = null;

        if ($this->usesRedis()) {
            try {
                $started = microtime(true);
                $pong = Redis::connection()->ping();
                $latencyMs = round((microtime(true) - $started) * 1000, 1);
                $redisOk = is_bool($pong) ? $pong : strtoupper((string) $pong) === 'PONG';

                $info = $this->flattenRedisInfo(Redis::connection()->info());
                if ($info !== []) {
                    if (isset($info['used_memory'])) {
                        $memoryMb = round(((int) $info['used_memory']) / 1024 / 1024, 1);
                    }
                    if (isset($info['connected_clients'])) {
                        $connectedClients = (int) $info['connected_clients'];
                    }
                }
            } catch (\Throwable) {
                $redisOk = false;
            }
        }

        $available = $cacheOk || $redisOk;

        return [
            'available' => $available,
            'status' => $available ? 'connected' : 'unavailable',
            'label' => $available ? __('Connected') : __('Unavailable'),
            'cache_ping' => $cacheOk,
            'connection_ping' => $redisOk,
            'latency_ms' => $latencyMs,
            'connected_clients' => $connectedClients,
            'memory_mb' => $memoryMb,
            'client' => config('database.redis.client'),
            'host' => $this->safeHost(),
            'cache_keys' => $this->cacheKeyCount(),
        ];
    }

    /**
     * Predis returns nested INFO sections; phpredis returns a flat array.
     *
     * @param  array<string, mixed>  $info
     * @return array<string, mixed>
     */
    private function flattenRedisInfo(array $info): array
    {
        $flat = [];

        foreach ($info as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if (! is_array($nestedValue)) {
                        $flat[(string) $nestedKey] = $nestedValue;
                    }
                }

                continue;
            }

            $flat[(string) $key] = $value;
        }

        return $flat;
    }

    private function cacheKeyCount(): ?int
    {
        if (config('cache.default') !== 'redis') {
            return null;
        }

        try {
            $connection = config('cache.stores.redis.connection', 'cache');

            return (int) Redis::connection($connection)->dbSize();
        } catch (\Throwable) {
            return null;
        }
    }

    private function horizonExpectedInEnvironment(): bool
    {
        return class_exists(\Laravel\Horizon\Horizon::class) && PHP_OS_FAMILY !== 'Windows';
    }

    /**
     * @return array<string, mixed>
     */
    private function horizonStatus(): array
    {
        $available = class_exists(\Laravel\Horizon\Horizon::class);

        if (! $available) {
            return [
                'available' => false,
                'running' => null,
                'status' => 'unavailable',
                'label' => __('Not installed'),
                'detail' => __('Horizon requires Linux/macOS (pcntl). Use queue:work on Windows.'),
            ];
        }

        $running = $this->horizonIsRunning();

        return [
            'available' => true,
            'running' => $running,
            'status' => $running ? 'running' : 'stopped',
            'label' => $running ? __('Running') : __('Not running'),
            'detail' => $running
                ? __('Horizon supervisors are active.')
                : __('Start Horizon under Supervisor in production.'),
        ];
    }

    private function horizonIsRunning(): bool
    {
        if (! $this->usesRedis()) {
            return false;
        }

        try {
            $prefix = (string) config('horizon.prefix', 'horizon:');
            $connection = config('horizon.use', 'default');
            $supervisors = Redis::connection($connection)->scard($prefix.'supervisors');

            return is_int($supervisors) && $supervisors > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function queueCards(): array
    {
        $meta = config('queue_names.meta', []);
        $rows = [];

        foreach (QueueName::all() as $queue) {
            $pending = $this->pendingCount($queue);
            $queueMeta = $meta[$queue] ?? [];
            $warnAbove = (int) ($queueMeta['warn_above'] ?? 50);
            $priority = (string) ($queueMeta['priority'] ?? 'normal');

            $rows[] = [
                'queue' => $queue,
                'pending' => $pending,
                'label' => (string) ($queueMeta['label'] ?? Str::headline($queue)),
                'priority' => $priority,
                'priority_label' => $this->priorityLabel($priority),
                'warn_above' => $warnAbove,
                'is_high_pending' => $pending >= $warnAbove,
                'is_idle' => $pending === 0,
                'load_pct' => $warnAbove > 0 ? min(100, (int) round(($pending / $warnAbove) * 100)) : 0,
                'status' => $pending === 0 ? 'idle' : ($pending >= $warnAbove ? 'backlog' : 'active'),
                'status_label' => $pending === 0 ? __('Idle') : ($pending >= $warnAbove ? __('Backlog') : __('Active')),
                'worker_command' => $this->singleQueueWorkerCommand($queue),
                'full_worker_command' => (string) config('queue_names.worker_command.local_windows'),
            ];
        }

        return $rows;
    }

    private function priorityLabel(string $priority): string
    {
        return match ($priority) {
            'critical' => __('Critical'),
            'high' => __('High'),
            'low' => __('Low'),
            default => __('Normal'),
        };
    }

    private function singleQueueWorkerCommand(string $queue): string
    {
        return sprintf('php artisan queue:work redis --queue=%s --tries=3', $queue);
    }

    private function pendingCount(string $queue): int
    {
        if (config('queue.default') === 'sync') {
            return 0;
        }

        try {
            return (int) Queue::connection()->size($queue);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function failedJobsCount(): int
    {
        if (! $this->failedJobsTableExists()) {
            return 0;
        }

        return (int) DB::table('failed_jobs')->count();
    }

    public function lastFailedAt(): ?Carbon
    {
        if (! $this->failedJobsTableExists()) {
            return null;
        }

        $value = DB::table('failed_jobs')->max('failed_at');

        return $value ? Carbon::parse($value) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function failedJobDetails(string $uuid): ?array
    {
        if (! $this->failedJobsTableExists()) {
            return null;
        }

        $row = DB::table('failed_jobs')
            ->where('uuid', $uuid)
            ->first(['id', 'uuid', 'queue', 'payload', 'exception', 'failed_at']);

        if (! $row) {
            return null;
        }

        $payload = json_decode((string) $row->payload, true);

        return [
            'id' => $row->id,
            'uuid' => $row->uuid,
            'queue' => $row->queue,
            'job_name' => is_array($payload) ? class_basename((string) ($payload['displayName'] ?? 'unknown')) : 'unknown',
            'failed_at' => Carbon::parse($row->failed_at)->toDateTimeString(),
            'exception' => Str::limit((string) $row->exception, 4000),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function recentFailedJobs(int $limit = 20): Collection
    {
        if (! $this->failedJobsTableExists()) {
            return collect();
        }

        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit($limit)
            ->get(['id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'])
            ->map(function ($row) {
                $payload = json_decode((string) $row->payload, true);
                $row->job_name = is_array($payload) ? class_basename((string) ($payload['displayName'] ?? 'unknown')) : 'unknown';
                $row->exception_summary = Str::limit(
                    trim(strtok((string) $row->exception, "\n") ?: __('Unknown error')),
                    180,
                );
                $row->failed_at_human = Carbon::parse($row->failed_at)->diffForHumans();
                $row->failed_at_exact = Carbon::parse($row->failed_at)->toDateTimeString();

                unset($row->exception, $row->payload);

                return $row;
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function operationalGuidance(): array
    {
        return [
            'local_windows' => (string) config('queue_names.worker_command.local_windows'),
            'production_linux' => (string) config('queue_names.worker_command.production_linux'),
            'queue_order' => (string) config('queue_names.worker_command.queue_order'),
            'supervisor' => [
                __('Run Horizon under Supervisor or systemd in production.'),
                __('Ensure only one Horizon master process runs per app instance.'),
            ],
            'scheduler' => [
                __('Add to crontab: * * * * * php artisan schedule:run'),
                __('The scheduler dispatches recurring billing, telemetry, and maintenance jobs.'),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function workerGuidanceLines(): array
    {
        if (config('queue.default') === 'sync') {
            return [__('Queue connection is sync — jobs run inline during the request/command.')];
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return [
                __('Local Windows: :cmd', ['cmd' => config('queue_names.worker_command.local_windows')]),
                __('Horizon requires Linux/macOS (pcntl). Use queue:work locally; Horizon in production.'),
            ];
        }

        return [
            __('Production: :cmd under Supervisor.', ['cmd' => config('queue_names.worker_command.production_linux')]),
            __('Fallback worker: :cmd', ['cmd' => config('queue_names.worker_command.local_windows')]),
        ];
    }

    private function usesRedis(): bool
    {
        return in_array(config('queue.default'), ['redis'], true)
            || config('cache.default') === 'redis'
            || config('session.driver') === 'redis';
    }

    private function safeHost(): string
    {
        $host = (string) config('database.redis.default.host', '127.0.0.1');

        return $host === '127.0.0.1' || $host === 'localhost' ? $host : '[configured]';
    }

    private function failedJobsTableExists(): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable('failed_jobs');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<array{at: int, pending: int}>
     */
    private function recordPendingHistory(int $totalPending): array
    {
        /** @var list<array{at: int, pending: int}> $history */
        $history = Cache::get('queue:monitor:pending_history', []);
        $history[] = ['at' => now()->timestamp, 'pending' => $totalPending];
        $history = array_slice($history, -24);
        Cache::put('queue:monitor:pending_history', $history, 86400);

        return $history;
    }

    /**
     * @param  list<array<string, mixed>>  $queues
     * @return array<string, mixed>
     */
    private function liveness(array $redis, array $horizon, int $totalPending, int $failedJobsCount, array $queues): array
    {
        $criticalBacklog = collect($queues)
            ->whereIn('priority', ['critical', 'high'])
            ->contains(fn (array $queue) => ($queue['is_high_pending'] ?? false));

        $horizonExpected = $this->horizonExpectedInEnvironment();

        $overallStatus = match (true) {
            ! ($redis['available'] ?? false) => 'critical',
            $failedJobsCount > 0, $criticalBacklog => 'critical',
            $horizonExpected && ! ($horizon['running'] ?? false) && config('queue.default') === 'redis' => 'degraded',
            collect($queues)->contains(fn (array $queue) => ($queue['is_high_pending'] ?? false)) => 'degraded',
            default => 'healthy',
        };

        $overallLabel = match ($overallStatus) {
            'critical' => __('Needs attention'),
            'degraded' => __('Degraded'),
            default => __('All systems operational'),
        };

        $overallDetail = match ($overallStatus) {
            'critical' => $failedJobsCount > 0
                ? __('Failed jobs or critical queue backlog detected.')
                : __('Redis is unreachable — queues cannot process.'),
            'degraded' => __('Workers may be stopped or a queue is building backlog.'),
            default => $totalPending === 0
                ? __('Redis is live, queues are idle, and nothing is stuck.')
                : __('Redis is live and :count jobs are in flight.', ['count' => number_format($totalPending)]),
        };

        $workerStatus = match (true) {
            ($horizon['running'] ?? false) => [
                'status' => 'running',
                'label' => __('Horizon active'),
                'variant' => 'success',
                'detail' => __('Supervisors are processing jobs.'),
            ],
            config('queue.default') === 'sync' => [
                'status' => 'inline',
                'label' => __('Inline (sync)'),
                'variant' => 'info',
                'detail' => __('Jobs run during the request or command.'),
            ],
            ($horizon['available'] ?? false) && PHP_OS_FAMILY === 'Windows' => [
                'status' => 'manual',
                'label' => __('queue:work'),
                'variant' => 'info',
                'detail' => __('Horizon needs Linux — use queue:work on Windows.'),
            ],
            $horizonExpected => [
                'status' => 'stopped',
                'label' => __('Horizon stopped'),
                'variant' => 'warning',
                'detail' => __('Start Horizon under Supervisor in production.'),
            ],
            default => [
                'status' => 'manual',
                'label' => __('queue:work'),
                'variant' => 'info',
                'detail' => __('Run a Redis queue worker to process jobs.'),
            ],
        };

        $operationalCacheEnabled = $this->operationalCache->enabled();

        return [
            'overall_status' => $overallStatus,
            'overall_label' => $overallLabel,
            'overall_detail' => $overallDetail,
            'worker' => $workerStatus,
            'horizon_expected' => $horizonExpected,
            'recent_activity' => $this->recentPipelineActivity(),
            'infrastructure' => [
                [
                    'label' => __('Cache'),
                    'driver' => (string) config('cache.default'),
                    'detail' => $operationalCacheEnabled
                        ? __('Operational summaries enabled')
                        : __('Summaries bypassed'),
                    'status' => ($redis['cache_ping'] ?? false) ? 'ok' : 'warn',
                ],
                [
                    'label' => __('Sessions'),
                    'driver' => (string) config('session.driver'),
                    'detail' => __('User login state'),
                    'status' => config('session.driver') === 'redis' && ($redis['connection_ping'] ?? false) ? 'ok' : 'neutral',
                ],
                [
                    'label' => __('Queues'),
                    'driver' => (string) config('queue.default'),
                    'detail' => __('Async job transport'),
                    'status' => config('queue.default') === 'redis' && ($redis['connection_ping'] ?? false) ? 'ok' : 'neutral',
                ],
            ],
            'stack' => [
                ['label' => __('Cache'), 'value' => config('cache.default')],
                ['label' => __('Sessions'), 'value' => config('session.driver')],
                ['label' => __('Queues'), 'value' => config('queue.default')],
            ],
        ];
    }

    /**
     * @return list<array{action: string, message: string, at: string, ago: string}>
     */
    private function recentPipelineActivity(int $limit = 8): array
    {
        try {
            if (! DB::getSchemaBuilder()->hasTable('system_activity_logs')) {
                return [];
            }

            return DB::table('system_activity_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->where(function ($query) {
                    $query->where('action', 'like', 'payment.%')
                        ->orWhere('action', 'like', 'invoice.%')
                        ->orWhere('action', 'like', 'document.%')
                        ->orWhere('action', 'like', 'webhook.%')
                        ->orWhere('action', 'like', 'billing.%')
                        ->orWhere('action', 'like', 'queue.%');
                })
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get(['action', 'message', 'created_at'])
                ->map(function ($row) {
                    $at = Carbon::parse($row->created_at);

                    return [
                        'action' => (string) $row->action,
                        'message' => Str::limit((string) ($row->message ?: $row->action), 90),
                        'at' => $at->toDateTimeString(),
                        'ago' => $at->diffForHumans(),
                    ];
                })
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
