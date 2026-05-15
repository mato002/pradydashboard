<?php

namespace App\Console\Commands;

use App\Domain\Servers\ServerTelemetrySyncService;
use Illuminate\Console\Command;

class SyncServerTelemetryCommand extends Command
{
    protected $signature = 'servers:sync-telemetry {--server= : Sync a single server ID}';

    protected $description = 'Poll WHM, cloud APIs, SSL, and reachability for all registered servers';

    public function handle(ServerTelemetrySyncService $sync): int
    {
        if (! config('infrastructure.sync.enabled', true)) {
            $this->warn('Infrastructure sync is disabled (INFRA_SYNC_ENABLED=false).');

            return self::SUCCESS;
        }

        $serverId = $this->option('server');

        if ($serverId) {
            $server = \App\Models\Server::query()->find($serverId);
            if (! $server) {
                $this->error("Server #{$serverId} not found.");

                return self::FAILURE;
            }
            $result = $sync->sync($server);
            $this->line($result['message']);

            return $result['ok'] ? self::SUCCESS : self::FAILURE;
        }

        $results = $sync->syncFleet();
        $ok = $results->where('ok', true)->count();
        $this->info("Synced {$ok}/{$results->count()} servers.");

        foreach ($results as $row) {
            $icon = $row['ok'] ? '✓' : '✗';
            $this->line(" {$icon} {$row['server']->name}: {$row['message']}");
        }

        return self::SUCCESS;
    }
}
