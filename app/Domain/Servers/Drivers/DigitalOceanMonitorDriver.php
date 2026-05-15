<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;
use Illuminate\Support\Facades\Http;

class DigitalOceanMonitorDriver implements ServerMonitorDriver
{
    public function key(): string
    {
        return 'digitalocean';
    }

    public function supports(Server $server): bool
    {
        $creds = ServerConnectionConfig::digitalOceanCredentials($server);

        return $creds && ! empty($creds['token']) && ! empty($creds['droplet_id']);
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $creds = ServerConnectionConfig::digitalOceanCredentials($server);

        if (! $creds || empty($creds['droplet_id'])) {
            return new ServerTelemetrySnapshot(
                messages: [__('DigitalOcean droplet ID required in cloud_instance_id.')],
            );
        }

        try {
            $response = Http::withToken($creds['token'])
                ->timeout(15)
                ->get('https://api.digitalocean.com/v2/droplets/'.$creds['droplet_id']);

            if (! $response->successful()) {
                return new ServerTelemetrySnapshot(
                    messages: [__('DigitalOcean API error: :status', ['status' => $response->status()])],
                );
            }

            $droplet = $response->json('droplet') ?? [];
            $status = ($droplet['status'] ?? '') === 'active' ? 'online' : 'offline';

            return new ServerTelemetrySnapshot(
                status: $status,
                messages: [__('Droplet :name — :status', [
                    'name' => $droplet['name'] ?? $server->name,
                    'status' => $droplet['status'] ?? 'unknown',
                ])],
                sources: [$this->key()],
            );
        } catch (\Throwable $e) {
            return new ServerTelemetrySnapshot(
                messages: [__('DigitalOcean poll failed: :msg', ['msg' => $e->getMessage()])],
            );
        }
    }
}
