<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;
use Illuminate\Support\Facades\Http;

class HetznerMonitorDriver implements ServerMonitorDriver
{
    public function key(): string
    {
        return 'hetzner';
    }

    public function supports(Server $server): bool
    {
        $creds = ServerConnectionConfig::hetznerCredentials($server);

        return $creds && ! empty($creds['token']) && ! empty($creds['server_id']);
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $creds = ServerConnectionConfig::hetznerCredentials($server);

        if (! $creds || empty($creds['server_id'])) {
            return new ServerTelemetrySnapshot(
                messages: [__('Hetzner server ID required in cloud_instance_id.')],
            );
        }

        try {
            $response = Http::withToken($creds['token'])
                ->timeout(15)
                ->get('https://api.hetzner.cloud/v1/servers/'.$creds['server_id']);

            if (! $response->successful()) {
                return new ServerTelemetrySnapshot(
                    messages: [__('Hetzner API error: :status', ['status' => $response->status()])],
                );
            }

            $hetzner = $response->json('server') ?? [];
            $status = ($hetzner['status'] ?? '') === 'running' ? 'online' : 'offline';

            $snapshot = new ServerTelemetrySnapshot(
                status: $status,
                messages: [__('Hetzner :name — :status', [
                    'name' => $hetzner['name'] ?? $server->name,
                    'status' => $hetzner['status'] ?? 'unknown',
                ])],
                sources: [$this->key()],
            );

            $metrics = Http::withToken($creds['token'])
                ->timeout(15)
                ->get('https://api.hetzner.cloud/v1/servers/'.$creds['server_id'].'/metrics', [
                    'type' => 'cpu',
                    'start' => now()->subMinutes(5)->toIso8601String(),
                    'end' => now()->toIso8601String(),
                ]);

            if ($metrics->successful()) {
                $series = $metrics->json('metrics.time_series.cpu.values') ?? [];
                $last = end($series);
                if (is_array($last) && isset($last[1])) {
                    $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                        cpuPercent: min(100, round((float) $last[1] * 100, 1)),
                    ));
                }
            }

            return $snapshot;
        } catch (\Throwable $e) {
            return new ServerTelemetrySnapshot(
                messages: [__('Hetzner poll failed: :msg', ['msg' => $e->getMessage()])],
            );
        }
    }
}
