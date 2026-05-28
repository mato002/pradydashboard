<?php

namespace App\Jobs\Concerns;

use App\Support\Cache\OperationalCache;
use Closure;
use Illuminate\Support\Facades\Log;

trait UsesOperationalLocks
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T|null
     */
    protected function withLock(string $name, int $seconds, Closure $callback, int $waitSeconds = 10): mixed
    {
        $cache = app(OperationalCache::class);

        if (! $cache->locksSupported()) {
            return $callback();
        }

        $result = $cache->lock($name, $seconds, $callback, $waitSeconds);

        if ($result === null) {
            Log::info('Queue job skipped — lock already held.', [
                'job' => static::class,
                'lock' => $name,
            ]);
        }

        return $result;
    }
}
