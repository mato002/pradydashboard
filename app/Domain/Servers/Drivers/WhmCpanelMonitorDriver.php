<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;
use Illuminate\Support\Facades\Http;

class WhmCpanelMonitorDriver implements ServerMonitorDriver
{
    public function key(): string
    {
        return 'whm';
    }

    public function supports(Server $server): bool
    {
        return ServerConnectionConfig::whmCredentials($server) !== null;
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $creds = ServerConnectionConfig::whmCredentials($server);

        if (! $creds) {
            return new ServerTelemetrySnapshot(
                messages: [__('WHM credentials missing (host + API token required).')],
            );
        }

        $snapshot = new ServerTelemetrySnapshot(status: 'online', sources: [$this->key()]);
        $messages = [];

        $load = $this->whmGet($creds, 'systemloadavg');
        if ($load && isset($load['data'])) {
            $one = (float) ($load['data']['one'] ?? $load['data']['load1'] ?? 0);
            $cores = max(1, (int) ($server->cpu_cores ?: 2));
            $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                cpuPercent: min(100, round(($one / $cores) * 100, 1)),
                messages: [__('WHM load average :load', ['load' => $one])],
            ));
        }

        $disk = $this->whmGet($creds, 'getdiskusage');
        if ($disk && isset($disk['data'])) {
            $total = 0;
            $used = 0;
            foreach ((array) $disk['data'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $total += (float) ($row['total'] ?? $row['blocks'] ?? 0);
                $used += (float) ($row['used'] ?? $row['used_blocks'] ?? 0);
            }
            if ($total > 0) {
                $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                    diskPercent: min(100, round(($used / $total) * 100, 1)),
                ));
            }
        }

        $systemInfo = $this->whmGet($creds, 'systeminfo');
        if ($systemInfo && isset($systemInfo['data']['memory']['used_percent'])) {
            $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                ramPercent: min(100, (float) $systemInfo['data']['memory']['used_percent']),
            ));
        } elseif ($systemInfo && isset($systemInfo['data']['memory_used_percent'])) {
            $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                ramPercent: min(100, (float) $systemInfo['data']['memory_used_percent']),
            ));
        }

        $backup = $this->whmGet($creds, 'backup_configured');
        if ($backup !== null) {
            $configured = (bool) ($backup['data']['configured'] ?? $backup['data']['backup'] ?? false);
            $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                backupStatus: $configured ? 'configured' : 'not_configured',
            ));
        } else {
            $list = $this->whmGet($creds, 'listaccts');
            if ($list && isset($list['data']['acct'])) {
                $snapshot = $snapshot->merge(new ServerTelemetrySnapshot(
                    backupStatus: 'accounts:'.count((array) $list['data']['acct']),
                ));
            }
        }

        $uptime = $this->whmGet($creds, 'gethostname');
        if ($uptime) {
            $messages[] = __('WHM API connected.');
        }

        return $snapshot->merge(new ServerTelemetrySnapshot(
            messages: array_merge($messages, $snapshot->messages),
            sources: [$this->key()],
        ));
    }

    /**
     * @param  array{host: string, port: int, username: string, token: string}  $creds
     * @return array<string, mixed>|null
     */
    private function whmGet(array $creds, string $function): ?array
    {
        try {
            $url = sprintf(
                'https://%s:%d/json-api/%s',
                $creds['host'],
                $creds['port'],
                $function,
            );

            $response = Http::withHeaders([
                'Authorization' => 'whm '.$creds['username'].':'.$creds['token'],
            ])
                ->withoutVerifying()
                ->timeout((int) config('infrastructure.whm.timeout', 15))
                ->get($url, ['api.version' => 1]);

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Throwable) {
            return null;
        }
    }
}
