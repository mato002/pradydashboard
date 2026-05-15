<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\ServerHealthLog;
use Illuminate\Database\Seeder;

class ServerHealthDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (ServerHealthLog::query()->exists()) {
            return;
        }

        $fleet = [
            [
                'name' => 'nairobi-prod-01',
                'provider' => 'DigitalOcean',
                'ip_address' => '102.211.45.12',
                'cpu_cores' => 8,
                'ram_gb' => 32,
                'storage_gb' => 500,
                'status' => 'online',
                'ssl_status' => 'valid',
                'backup_status' => 'healthy',
                'disk_usage_percent' => 62.4,
                'cpu_base' => 34,
                'ram_base' => 58,
                'disk_base' => 62,
            ],
            [
                'name' => 'mombasa-edge-02',
                'provider' => 'AWS',
                'ip_address' => '54.72.118.203',
                'cpu_cores' => 16,
                'ram_gb' => 64,
                'storage_gb' => 1000,
                'status' => 'online',
                'ssl_status' => 'valid',
                'backup_status' => 'healthy',
                'disk_usage_percent' => 78.1,
                'cpu_base' => 52,
                'ram_base' => 71,
                'disk_base' => 78,
            ],
            [
                'name' => 'eu-replica-03',
                'provider' => 'Hetzner',
                'ip_address' => '95.217.142.88',
                'cpu_cores' => 4,
                'ram_gb' => 16,
                'storage_gb' => 320,
                'status' => 'online',
                'ssl_status' => 'expiring',
                'backup_status' => 'healthy',
                'disk_usage_percent' => 88.6,
                'cpu_base' => 68,
                'ram_base' => 82,
                'disk_base' => 89,
            ],
            [
                'name' => 'staging-sbx-04',
                'provider' => 'Linode',
                'ip_address' => '172.104.22.41',
                'cpu_cores' => 4,
                'ram_gb' => 8,
                'storage_gb' => 160,
                'status' => 'online',
                'ssl_status' => 'valid',
                'backup_status' => 'warning',
                'disk_usage_percent' => 41.2,
                'cpu_base' => 22,
                'ram_base' => 44,
                'disk_base' => 41,
            ],
            [
                'name' => 'legacy-whm-05',
                'provider' => 'On-prem',
                'ip_address' => '196.201.8.55',
                'cpu_cores' => 12,
                'ram_gb' => 48,
                'storage_gb' => 2000,
                'status' => 'offline',
                'ssl_status' => 'expired',
                'backup_status' => 'failed',
                'disk_usage_percent' => 96.2,
                'cpu_base' => 0,
                'ram_base' => 0,
                'disk_base' => 96,
            ],
            [
                'name' => 'api-gateway-06',
                'provider' => 'GCP',
                'ip_address' => '34.89.201.17',
                'cpu_cores' => 8,
                'ram_gb' => 32,
                'storage_gb' => 256,
                'status' => 'online',
                'ssl_status' => 'valid',
                'backup_status' => 'healthy',
                'disk_usage_percent' => 55.8,
                'cpu_base' => 91,
                'ram_base' => 86,
                'disk_base' => 56,
            ],
        ];

        foreach ($fleet as $spec) {
            $server = Server::query()->firstOrCreate(
                ['name' => $spec['name']],
                [
                    'provider' => $spec['provider'],
                    'ip_address' => $spec['ip_address'],
                    'cpu_cores' => $spec['cpu_cores'],
                    'ram_gb' => $spec['ram_gb'],
                    'storage_gb' => $spec['storage_gb'],
                    'disk_usage_percent' => $spec['disk_usage_percent'],
                    'status' => $spec['status'],
                    'ssl_status' => $spec['ssl_status'],
                    'backup_status' => $spec['backup_status'],
                    'hosted_domains' => [$spec['name'].'.prady.internal'],
                    'renewal_expires_at' => now()->addMonths(4),
                    'monthly_cost' => random_int(12000, 85000),
                    'monthly_revenue' => random_int(45000, 220000),
                    'currency' => 'KES',
                ]
            );

            if ($server->status === 'offline') {
                continue;
            }

            $uptimeBase = 86400 * random_int(12, 120);

            for ($i = 47; $i >= 0; $i--) {
                $checkedAt = now()->subMinutes($i * 30);
                $wave = sin($i / 6) * 8;
                $noise = (($server->id * 17 + $i * 3) % 11) - 5;

                ServerHealthLog::query()->create([
                    'server_id' => $server->id,
                    'cpu_percent' => max(0, min(100, $spec['cpu_base'] + $wave + $noise)),
                    'ram_percent' => max(0, min(100, $spec['ram_base'] + $wave * 0.7 + $noise)),
                    'disk_percent' => max(0, min(100, $spec['disk_base'] + ($i < 12 ? 0.15 * (47 - $i) : 0))),
                    'uptime_seconds' => $uptimeBase + (47 - $i) * 1800,
                    'checked_at' => $checkedAt,
                ]);
            }
        }
    }
}
