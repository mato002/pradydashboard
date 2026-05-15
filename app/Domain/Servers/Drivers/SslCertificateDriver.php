<?php

namespace App\Domain\Servers\Drivers;

use App\Domain\Servers\Contracts\ServerMonitorDriver;
use App\Domain\Servers\DTO\ServerTelemetrySnapshot;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Models\Server;
use Carbon\Carbon;

class SslCertificateDriver implements ServerMonitorDriver
{
    public function key(): string
    {
        return 'ssl';
    }

    public function supports(Server $server): bool
    {
        return filled(ServerConnectionConfig::hostname($server))
            || filled($server->ip_address);
    }

    public function poll(Server $server): ServerTelemetrySnapshot
    {
        $host = ServerConnectionConfig::hostname($server) ?? $server->ip_address;

        if (! $host) {
            return new ServerTelemetrySnapshot;
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $client = @stream_socket_client(
            'ssl://'.$host.':443',
            $errno,
            $errstr,
            (float) config('infrastructure.ssl.timeout', 5),
            STREAM_CLIENT_CONNECT,
            $context,
        );

        if ($client === false) {
            return new ServerTelemetrySnapshot(
                sslStatus: 'unreachable',
                messages: [__('SSL endpoint unreachable: :error', ['error' => $errstr ?: $errno])],
                sources: [$this->key()],
            );
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;

        if (! $cert) {
            return new ServerTelemetrySnapshot(
                sslStatus: 'unknown',
                messages: [__('Could not read peer certificate.')],
                sources: [$this->key()],
            );
        }

        $parsed = openssl_x509_parse($cert);
        $validTo = isset($parsed['validTo_time_t'])
            ? Carbon::createFromTimestamp($parsed['validTo_time_t'])
            : null;

        if (! $validTo) {
            return new ServerTelemetrySnapshot(
                sslStatus: 'unknown',
                sources: [$this->key()],
            );
        }

        $daysLeft = now()->diffInDays($validTo, false);
        $expiry = $validTo->toDateString();

        if ($daysLeft < 0) {
            $status = 'expired';
        } elseif ($daysLeft <= 14) {
            $status = 'expiring_soon';
        } else {
            $status = 'valid';
        }

        return new ServerTelemetrySnapshot(
            sslStatus: $status,
            certificateExpiry: $expiry,
            messages: [__('Certificate valid until :date (:days days).', [
                'date' => $expiry,
                'days' => max(0, $daysLeft),
            ])],
            sources: [$this->key()],
        );
    }
}
