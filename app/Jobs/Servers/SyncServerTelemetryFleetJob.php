<?php

namespace App\Jobs\Servers;

use App\Jobs\OperationalJob;
use App\Models\Server;
use App\Support\Queue\QueueName;

class SyncServerTelemetryFleetJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::TELEMETRY);
        $this->timeout = 600;
    }

    public function handle(): void
    {
        $this->withLock(
            'server:sync-fleet',
            config('redis_cache.locks.server_sync', 120) * 2,
            function (): void {
                Server::query()
                    ->where('telemetry_mode', '!=', 'manual')
                    ->orderBy('name')
                    ->pluck('id')
                    ->each(fn (int $id) => SyncServerTelemetryJob::dispatch($id));
            },
        );
    }
}
