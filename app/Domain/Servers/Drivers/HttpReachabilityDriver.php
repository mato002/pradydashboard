<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;

class HttpReachabilityDriver implements ServerMonitorDriver
{
    public function key(): string
    {
        return 'reachability';
    }

    public function supports(Server $server): bool
    {
        return filled($server->ip_address) || filled(ServerConnectionConfig::hostname($server));
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $host = $server->ip_address ?: ServerConnectionConfig::hostname($server);

        if (! $host) {
            return new ServerTelemetrySnapshot(
                messages: [__('No IP or hostname to probe.')],
            );
        }

        $checks = [];
        $messages = [];

        $checks['port_443'] = $this->canConnect($host, 443);
        $checks['port_80'] = $this->canConnect($host, 80);
        $checks['port_22'] = $this->canConnect($host, 22);

        if ($checks['port_443']) {
            $messages[] = __('HTTPS (443) reachable.');
        }
        if ($checks['port_80']) {
            $messages[] = __('HTTP (80) reachable.');
        }

        $whmPort = (int) ($server->meta('whm_port') ?? config('infrastructure.whm.port', 2087));
        if (filled($server->whm_cpanel_reference) || filled($server->meta('api_endpoint'))) {
            $checks['port_2087'] = $this->canConnect($host, $whmPort);
            $messages[] = $checks['port_2087']
                ? __('WHM port :port reachable.', ['port' => $whmPort])
                : __('WHM port :port closed or filtered.', ['port' => $whmPort]);
        }

        $hostname = ServerConnectionConfig::hostname($server);
        if ($hostname && filled($server->ip_address)) {
            $resolved = gethostbyname($hostname);
            $checks['dns_resolves'] = $resolved !== $hostname;
            $checks['dns_matches_ip'] = $resolved === $server->ip_address;
            if ($checks['dns_matches_ip']) {
                $messages[] = __('DNS resolves to configured IP.');
            } elseif ($checks['dns_resolves']) {
                $messages[] = __('DNS resolves to :ip (configured: :expected).', [
                    'ip' => $resolved,
                    'expected' => $server->ip_address,
                ]);
            } else {
                $messages[] = __('DNS does not resolve for hostname.');
            }
        } elseif ($hostname) {
            $resolved = gethostbyname($hostname);
            $checks['dns_resolves'] = $resolved !== $hostname;
            $messages[] = $checks['dns_resolves']
                ? __('DNS resolves to :ip.', ['ip' => $resolved])
                : __('DNS does not resolve.');
        }

        $online = $checks['port_443'] || $checks['port_80'] || ($checks['port_22'] ?? false);

        return new ServerTelemetrySnapshot(
            status: $online ? 'online' : 'offline',
            messages: $messages !== [] ? $messages : [__('No response on ports 443, 80, or 22.')],
            sources: [$this->key()],
            healthChecks: $checks,
        );
    }

    private function canConnect(string $host, int $port): bool
    {
        $timeout = (float) config('infrastructure.reachability.timeout', 3);
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
