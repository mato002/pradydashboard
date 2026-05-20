<?php

namespace App\Domain\Servers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Drivers\DigitalOceanMonitorDriver;
use App\Domain\Servers\Drivers\HetznerMonitorDriver;
use App\Domain\Servers\Drivers\HttpReachabilityDriver;
use App\Domain\Servers\Drivers\SslCertificateDriver;
use App\Domain\Servers\Drivers\WhmCpanelMonitorDriver;
use App\Domain\Servers\Support\ServerConnectionConfig;
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

        if ($server->telemetry_mode === 'manual') {
            return [
                'ok' => false,
                'snapshot' => new ServerTelemetrySnapshot,
                'message' => __('Manual monitoring — automatic sync skipped.'),
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
            if ($server->telemetry_mode === 'manual') {
                return [
                    'server' => $server,
                    'ok' => false,
                    'message' => __(':name: manual monitoring.', ['name' => $server->name]),
                ];
            }

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
            'load_average' => $snapshot->loadAverage,
            'ssl_status' => $snapshot->sslStatus,
            'ssl_days_remaining' => $snapshot->sslDaysRemaining,
            'backup_status' => $snapshot->backupStatus,
            'account_count' => $snapshot->accountCount,
            'messages' => $snapshot->messages,
            'sources' => $snapshot->sources,
            'health_checks' => $snapshot->healthChecks,
            'telemetry_mode' => $this->resolveTelemetryMode($server, $snapshot),
            'has_whm_credentials' => ServerConnectionConfig::whmCredentials($server) !== null,
        ];
    }

    private function apply(Server $server, ServerTelemetrySnapshot $snapshot): void
    {
        $updates = [];

        if ($snapshot->status) {
            $updates['status'] = $this->resolveStatus($server, $snapshot);
        }

        if ($snapshot->diskPercent !== null) {
            $updates['disk_usage_percent'] = $snapshot->diskPercent;
        }

        if ($snapshot->ramPercent !== null) {
            $updates['ram_usage_percent'] = $snapshot->ramPercent;
        }

        if ($snapshot->loadAverage !== null) {
            $updates['load_average'] = $snapshot->loadAverage;
        }

        if ($snapshot->sslStatus) {
            $updates['ssl_status'] = $snapshot->sslStatus;
        }

        if ($snapshot->sslDaysRemaining !== null) {
            $updates['ssl_days_remaining'] = $snapshot->sslDaysRemaining;
        }

        if ($snapshot->backupStatus) {
            $updates['backup_status'] = $snapshot->backupStatus;
        }

        if ($snapshot->accountCount !== null) {
            $updates['account_count'] = $snapshot->accountCount;
        }

        if ($snapshot->certificateExpiry) {
            $meta = is_array($server->provisioning_meta) ? $server->provisioning_meta : [];
            $meta['certificate_expiry'] = $snapshot->certificateExpiry;
            $updates['provisioning_meta'] = $meta;
        }

        if ($snapshot->healthChecks !== []) {
            $meta = $updates['provisioning_meta'] ?? (is_array($server->provisioning_meta) ? $server->provisioning_meta : []);
            $meta['last_health_checks'] = $snapshot->healthChecks;
            $meta['last_health_checked_at'] = now()->toIso8601String();
            $updates['provisioning_meta'] = $meta;
        }

        $updates['telemetry_mode'] = $this->resolveTelemetryMode($server, $snapshot);
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

    private function resolveTelemetryMode(Server $server, ServerTelemetrySnapshot $snapshot): string
    {
        if ($snapshot->hasWhmMetrics()) {
            return 'whm';
        }

        if ($snapshot->sources !== [] && (in_array('reachability', $snapshot->sources, true) || in_array('ssl', $snapshot->sources, true))) {
            return 'basic';
        }

        return $server->telemetry_mode ?: 'manual';
    }

    private function resolveStatus(Server $server, ServerTelemetrySnapshot $snapshot): string
    {
        $base = $snapshot->status ?? $server->status ?? 'unknown';

        if ($base === 'offline') {
            return 'offline';
        }

        $disk = $snapshot->diskPercent ?? (float) ($server->disk_usage_percent ?? 0);
        $ram = $snapshot->ramPercent ?? (float) ($server->ram_usage_percent ?? 0);
        $sslDays = $snapshot->sslDaysRemaining ?? $server->ssl_days_remaining;

        if ($disk >= 90 || $ram >= 90 || ($sslDays !== null && $sslDays <= 7) || $server->renewalRisk() === 'overdue') {
            return 'warning';
        }

        if ($disk >= 80 || $ram >= 80 || ($sslDays !== null && $sslDays <= 14) || $server->renewalRisk() === 'soon') {
            return 'warning';
        }

        return $base === 'unknown' ? 'online' : $base;
    }
}
