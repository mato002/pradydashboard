<?php

namespace App\Console\Commands;

use App\Support\Cache\OperationalCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class RedisHealthCommand extends Command
{
    protected $signature = 'redis:health';

    protected $description = 'Verify Redis connectivity for cache, sessions, and queues';

    public function handle(OperationalCache $operationalCache): int
    {
        $checks = [
            'cache_store' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_connection' => config('queue.default'),
            'redis_client' => config('database.redis.client'),
        ];

        $this->table(['Setting', 'Value'], collect($checks)->map(fn ($v, $k) => [$k, (string) $v])->values()->all());

        $cacheOk = $operationalCache->ping();
        $this->line($cacheOk ? '<info>Cache read/write: OK</info>' : '<error>Cache read/write: FAILED</error>');

        $redisOk = $this->checkRedisConnection();
        $this->line($redisOk ? '<info>Redis connection: OK</info>' : '<comment>Redis connection: unavailable or not configured</comment>');

        $queueOk = $this->checkQueue();
        $this->line($queueOk ? '<info>Queue connection: OK</info>' : '<comment>Queue connection: check configuration</comment>');

        $operationalEnabled = $operationalCache->enabled();
        $this->line($operationalEnabled
            ? '<info>Operational summaries cache: enabled</info>'
            : '<comment>Operational summaries cache: bypassed (array/null driver or disabled)</comment>');

        if (! $cacheOk && in_array(config('cache.default'), ['redis'], true)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkRedisConnection(): bool
    {
        if (! in_array(config('cache.default'), ['redis'], true)
            && config('session.driver') !== 'redis'
            && config('queue.default') !== 'redis') {
            return false;
        }

        try {
            $connection = Redis::connection();
            $pong = $connection->ping();

            if (is_bool($pong)) {
                return $pong;
            }

            return strtoupper((string) $pong) === 'PONG'
                || strtoupper((string) $pong) === '+PONG';
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkQueue(): bool
    {
        if (config('queue.default') === 'sync') {
            return true;
        }

        try {
            $size = Queue::connection()->size();

            return is_int($size) || is_numeric($size);
        } catch (\Throwable) {
            return false;
        }
    }
}
