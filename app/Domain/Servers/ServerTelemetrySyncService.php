<?php

namespace App\Domain\Servers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Drivers\DigitalOceanMonitorDriver;
use App\Domain\Servers\Drivers\HetznerMonitorDriver;
use App\Domain\Servers\Drivers\HttpReachabilityDriver;
use App\Domain\Servers\Drivers\SslCertificateDriver;
use App\Domain\Servers\Drivers\WhmCpanelMonitorDriver;
use App\Models\Server;
use App\Models\ServerHealthLog;
use Illuminate\Support\Collection;

class ServerTelemetrySyncService
{
    /** @var list<ServerMonitorDriver> */
    private array $drivers;

    public function __construct()
    {
        $this->drivers = [
            new WhmCpanelMonitorDriver,
            new DigitalOceanMonitorDriver,
            new HetznerMonitorDriver,
            new HttpReachabilityDriver,
            new SslCertificateDriver,
        ];
    }

    /**
     * @return array{ok: bool, snapshot: ServerTelemetrySnapshot, message: string}
     */
    public function sync(Server $server): array
    {
        if (! config('infrastructure.sync.enabled', true)) {
            return [
                'ok' => false,
                'snapshot' => new ServerTelemetrySnapshot,
                'message' => __('Telemetry sync is disabled.'),
            ];
        }

        $snapshot = $this->poll($server);
        $this->apply($server, $snapshot);

        $ok = $snapshot->hasMetrics() || filled($snapshot->status);

        return [
            'ok' => $ok,
            'snapshot' => $snapshot,
            'message' => $ok
                ? __('Telemetry synced from :sources.', ['sources' => implode(', ', $snapshot->sources) ?: 'reachability'])
                : __('No telemetry collected — configure WHM API token, cloud instance ID, or verify IP/hostname.'),
        ];
    }

    /**
     * @return Collection<int, array{server: Server, ok: bool, message: string}>
     */
    public function syncFleet(): Collection
    {
        return Server::query()->orderBy('name')->get()->map(function (Server $server) {
            $result = $this->sync($server);

            return [
                'server' => $server->fresh(),
                'ok' => $result['ok'],
                'message' => $result['message'],
            ];
        });
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $merged = new ServerTelemetrySnapshot;

        foreach ($this->drivers as $driver) {
            if (! $driver->supports($server)) {
                continue;
            }

            try {
                $merged = $merged->merge($driver->poll($server));
            } catch (\Throwable $e) {
                $merged = $merged->merge(new ServerTelemetrySnapshot(
                    messages: [__(':driver failed: :msg', ['driver' => $driver->key(), 'msg' => $e->getMessage()])],
                ));
            }
        }

        return $merged;
    }

    /**
     * Probe connection settings before a server is saved.
     *
     * @param  array<string, mixed>  $input
     */
    public function probe(array $input): array
    {
        $server = new Server([
            'name' => $input['name'] ?? 'probe',
            'provider' => $input['provider'] ?? null,
            'ip_address' => $input['ip_address'] ?? null,
            'whm_cpanel_reference' => $input['whm_cpanel_reference'] ?? null,
            'cpu_cores' => $input['cpu_cores'] ?? null,
            'provisioning_meta' => $input['meta'] ?? [],
        ]);

        $snapshot = $this->poll($server);

        return [
            'ok' => $snapshot->hasMetrics() || filled($snapshot->status),
            'status' => $snapshot->status,
            'cpu_percent' => $snapshot->cpuPercent,
            'ram_percent' => $snapshot->ramPercent,
            'disk_percent' => $snapshot->diskPercent,
            'ssl_status' => $snapshot->sslStatus,
            'backup_status' => $snapshot->backupStatus,
            'messages' => $snapshot->messages,
            'sources' => $snapshot->sources,
        ];
    }

    private function apply(Server $server, ServerTelemetrySnapshot $snapshot): void
    {
        $updates = [];

        if ($snapshot->status) {
            $updates['status'] = $snapshot->status;
        }

        if ($snapshot->diskPercent !== null) {
            $updates['disk_usage_percent'] = $snapshot->diskPercent;
        }

        if ($snapshot->sslStatus) {
            $updates['ssl_status'] = $snapshot->sslStatus;
        }

        if ($snapshot->backupStatus) {
            $updates['backup_status'] = $snapshot->backupStatus;
        }

        if ($snapshot->certificateExpiry) {
            $meta = is_array($server->provisioning_meta) ? $server->provisioning_meta : [];
            $meta['certificate_expiry'] = $snapshot->certificateExpiry;
            $updates['provisioning_meta'] = $meta;
        }

        $updates['last_synced_at'] = now();
        $updates['sync_status'] = $snapshot->hasMetrics() || $snapshot->status ? 'ok' : 'partial';
        $updates['telemetry_source'] = $snapshot->sources === [] ? null : implode(',', $snapshot->sources);
        $updates['sync_message'] = $snapshot->messages === []
            ? null
            : implode(' ', array_slice($snapshot->messages, 0, 3));

        if ($server->exists) {
            $server->update($updates);
        }

        if (! $server->exists) {
            return;
        }

        if ($snapshot->cpuPercent !== null || $snapshot->ramPercent !== null || $snapshot->diskPercent !== null) {
            ServerHealthLog::query()->create([
                'server_id' => $server->id,
                'cpu_percent' => $snapshot->cpuPercent,
                'ram_percent' => $snapshot->ramPercent,
                'disk_percent' => $snapshot->diskPercent,
                'uptime_seconds' => $snapshot->uptimeSeconds,
                'checked_at' => now(),
            ]);
        }
    }
}
