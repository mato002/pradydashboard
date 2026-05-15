<?php

namespace App\Domain\Ssl;

use Carbon\Carbon;

class DomainSslInspector
{
    /**
     * @return array{ssl_status: string, ssl_expires_at: ?Carbon, ssl_issuer: ?string, message: ?string}
     */
    public function inspect(string $domain): array
    {
        $host = ltrim(str_replace('*.', '', $domain), '.');

        if ($host === '' || ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'ssl_status' => 'unknown',
                'ssl_expires_at' => null,
                'ssl_issuer' => null,
                'message' => __('Invalid domain for SSL probe.'),
            ];
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
            return [
                'ssl_status' => 'invalid',
                'ssl_expires_at' => null,
                'ssl_issuer' => null,
                'message' => $errstr ?: __('SSL endpoint unreachable.'),
            ];
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;

        if (! $cert) {
            return [
                'ssl_status' => 'unknown',
                'ssl_expires_at' => null,
                'ssl_issuer' => null,
                'message' => __('Could not read certificate.'),
            ];
        }

        $parsed = openssl_x509_parse($cert);
        $validTo = isset($parsed['validTo_time_t'])
            ? Carbon::createFromTimestamp($parsed['validTo_time_t'])
            : null;

        $issuer = $parsed['issuer']['O'] ?? $parsed['issuer']['CN'] ?? null;
        $daysLeft = $validTo ? now()->diffInDays($validTo, false) : null;

        if ($daysLeft === null) {
            $status = 'unknown';
        } elseif ($daysLeft < 0) {
            $status = 'expired';
        } elseif ($daysLeft <= 30) {
            $status = 'expiring_soon';
        } else {
            $status = 'active';
        }

        return [
            'ssl_status' => $status,
            'ssl_expires_at' => $validTo,
            'ssl_issuer' => is_string($issuer) ? $issuer : null,
            'message' => $validTo
                ? __('Certificate valid until :date.', ['date' => $validTo->toDateString()])
                : null,
        ];
    }
}
