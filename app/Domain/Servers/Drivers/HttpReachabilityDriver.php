<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;

class HttpReachabilityDriver implements ServerMonitorDriver
{
    /** @var list<int> */
    private array $ports;

    public function __construct()
    {
        $this->ports = config('infrastructure.reachability.ports', [443, 80, 22]);
    }

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

        $openPort = null;
        foreach ($this->ports as $port) {
            if ($this->canConnect($host, $port)) {
                $openPort = $port;
                break;
            }
        }

        if ($openPort !== null) {
            return new ServerTelemetrySnapshot(
                status: 'online',
                messages: [__('Reachable on port :port.', ['port' => $openPort])],
                sources: [$this->key()],
            );
        }

        return new ServerTelemetrySnapshot(
            status: 'offline',
            messages: [__('No response on ports :ports.', ['ports' => implode(', ', $this->ports)])],
            sources: [$this->key()],
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
