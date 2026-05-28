<?php

namespace App\Support\Cache;

use App\Domain\Rbac\RbacScopeFilter;
use App\Support\Rbac\RoleScopeType;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OperationalCache
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function remember(string $domain, string $name, int $ttlSeconds, Closure $callback, ?string $scopeKey = 'global'): mixed
    {
        if (! $this->enabled()) {
            return $callback();
        }

        $key = $this->key($domain, $name, $scopeKey);

        return Cache::remember($key, $ttlSeconds, $callback);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T|null
     */
    public function lock(string $lockName, int $seconds, Closure $callback, int $waitSeconds = 5): mixed
    {
        if (! $this->locksSupported()) {
            return $callback();
        }

        $lock = Cache::lock($this->lockKey($lockName), $seconds);

        try {
            return $lock->block($waitSeconds, $callback);
        } catch (LockTimeoutException) {
            Log::warning('Operational cache lock timeout.', ['lock' => $lockName]);

            return null;
        }
    }

    public function scopeFingerprint(?RbacScopeFilter $scopeFilter): string
    {
        if ($scopeFilter === null || $scopeFilter->isGlobalScope()) {
            return 'global';
        }

        $assignment = $scopeFilter->assignment();
        if ($assignment === null) {
            return 'global';
        }

        $id = match ($assignment->scope_type) {
            RoleScopeType::Tenant => (string) ($assignment->tenant_id ?? 0),
            RoleScopeType::Project => 'p'.($assignment->project_id ?? 0),
            RoleScopeType::Server => 's'.($assignment->server_id ?? 0),
            default => 'restricted',
        };

        return $assignment->scope_type.':'.$id;
    }

    public function bumpVersion(string $domain): void
    {
        if (! $this->enabled()) {
            return;
        }

        $versionKey = $this->versionKey($domain);

        if (! Cache::has($versionKey)) {
            Cache::forever($versionKey, 2);

            return;
        }

        Cache::increment($versionKey);
    }

    public function forgetTenant(int $tenantId): void
    {
        if (! $this->enabled()) {
            return;
        }

        Cache::forget($this->key('tenant', 'command-center', (string) $tenantId));
    }

    public function forgetServer(int $serverId): void
    {
        if (! $this->enabled()) {
            return;
        }

        Cache::forget($this->key('server', 'telemetry-summary', (string) $serverId));
    }

    public function key(string $domain, string $name, string $scopeKey = 'global'): string
    {
        $schema = config('redis_cache.schema_version', 'v1');
        $version = (int) Cache::get($this->versionKey($domain), 1);

        return implode(':', array_filter([
            $domain,
            $name,
            $schema,
            'rv'.$version,
            $scopeKey !== 'global' ? $scopeKey : null,
        ]));
    }

    public function lockKey(string $name): string
    {
        return 'lock:'.$name;
    }

    public function paymentReferenceLockKey(?int $tenantId, ?string $reference): ?string
    {
        if ($reference === null || trim($reference) === '') {
            return null;
        }

        $hash = hash('sha256', strtolower(trim($reference)));

        return 'payment:reference:'.($tenantId ?? 'any').':'.$hash;
    }

    public function enabled(): bool
    {
        if (! config('redis_cache.enabled', true)) {
            return false;
        }

        return config('cache.default') !== 'null';
    }

    public function locksSupported(): bool
    {
        return $this->enabled();
    }

    public function ping(): bool
    {
        try {
            Cache::put('redis:health:ping', 'ok', 5);

            return Cache::get('redis:health:ping') === 'ok';
        } catch (\Throwable) {
            return false;
        }
    }

    private function versionKey(string $domain): string
    {
        return 'cache:version:'.$domain;
    }
}
