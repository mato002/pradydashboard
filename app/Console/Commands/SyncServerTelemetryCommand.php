<?php

namespace App\Console\Commands;

use App\Jobs\Servers\SyncServerTelemetryFleetJob;
use App\Jobs\Servers\SyncServerTelemetryJob;
use Illuminate\Console\Command;

class SyncServerTelemetryCommand extends Command
{
    protected $signature = 'servers:sync-telemetry {--server= : Sync a single server ID} {--sync : Run inline instead of dispatching to queue}';

    protected $description = 'Poll WHM, cloud APIs, SSL, and reachability for all registered servers';

    public function handle(): int
    {
        if (! config('infrastructure.sync.enabled', true)) {
            $this->warn('Infrastructure sync is disabled (INFRA_SYNC_ENABLED=false).');

            return self::SUCCESS;
        }

        $serverId = $this->option('server');

        if ($serverId) {
            if ($this->option('sync') || config('queue.default') === 'sync') {
                return $this->syncInline((int) $serverId);
            }

            SyncServerTelemetryJob::dispatch((int) $serverId);
            $this->info(__('Telemetry sync job dispatched for server #:id.', ['id' => $serverId]));

            return self::SUCCESS;
        }

        if ($this->option('sync') || config('queue.default') === 'sync') {
            return $this->syncFleetInline();
        }

        SyncServerTelemetryFleetJob::dispatch();
        $this->info(__('Fleet telemetry sync job dispatched to the queue.'));

        return self::SUCCESS;
    }

    private function syncInline(int $serverId): int
    {
        $server = \App\Models\Server::query()->find($serverId);
        if (! $server) {
            $this->error("Server #{$serverId} not found.");

            return self::FAILURE;
        }

        $result = app(\App\Domain\Servers\ServerTelemetrySyncService::class)->sync($server);
        $this->line($result['message']);

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }

    private function syncFleetInline(): int
    {
        $results = app(\App\Domain\Servers\ServerTelemetrySyncService::class)->syncFleet();
        $ok = $results->where('ok', true)->count();
        $this->info("Synced {$ok}/{$results->count()} servers.");

        foreach ($results as $row) {
            $icon = $row['ok'] ? '✓' : '✗';
            $this->line(" {$icon} {$row['server']->name}: {$row['message']}");
        }

        return self::SUCCESS;
    }
}
