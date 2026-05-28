<?php

namespace App\Jobs\Servers;

use App\Domain\Servers\ServerTelemetrySyncService;
use App\Jobs\OperationalJob;
use App\Models\Server;
use App\Support\Queue\QueueName;

class SyncServerTelemetryJob extends OperationalJob
{
    public function __construct(
        public int $serverId,
    ) {
        $this->onQueue(QueueName::TELEMETRY);
        $this->timeout = 180;
    }

    public function handle(ServerTelemetrySyncService $sync): void
    {
        $server = Server::query()->find($this->serverId);
        if (! $server) {
            return;
        }

        $sync->sync($server);
    }
}
