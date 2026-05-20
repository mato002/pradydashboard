<?php

namespace App\Domain\Servers\Support;

use App\Models\Server;

class ServerConnectionConfig
{
    public const MASKED_TOKEN_PLACEHOLDER = '********';
    /**
     * @return array<string, mixed>
     */
    public static function meta(Server $server): array
    {
        return is_array($server->provisioning_meta) ? $server->provisioning_meta : [];
    }

    public static function hostname(Server $server): ?string
    {
        $meta = self::meta($server);

        return self::firstNonEmpty(
            $meta['hostname'] ?? null,
            self::hostFromReference($server->whm_cpanel_reference),
            self::hostFromUrl($meta['api_endpoint'] ?? null),
        );
    }

    public static function whmCredentials(Server $server): ?array
    {
        $meta = self::meta($server);
        $host = self::hostFromReference($server->whm_cpanel_reference)
            ?? self::hostFromUrl($meta['api_endpoint'] ?? null)
            ?? self::hostFromUrl($server->ip_address);

        $token = $server->decryptedApiToken();
        $username = $meta['whm_username'] ?? $meta['ssh_username'] ?? config('infrastructure.whm.username', 'root');

        if (! $host || ! $token) {
            return null;
        }

        $port = (int) ($meta['whm_port'] ?? config('infrastructure.whm.port', 2087));

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'token' => $token,
        ];
    }

    public static function digitalOceanCredentials(Server $server): ?array
    {
        $meta = self::meta($server);
        $token = $meta['api_token'] ?? config('infrastructure.digitalocean.token');

        if (! $token) {
            return null;
        }

        $dropletId = $meta['cloud_instance_id'] ?? $meta['droplet_id'] ?? null;

        if (! $dropletId && ! str_contains(strtolower((string) $server->provider), 'digitalocean')) {
            return null;
        }

        return ['token' => $token, 'droplet_id' => $dropletId];
    }

    public static function hetznerCredentials(Server $server): ?array
    {
        $meta = self::meta($server);
        $token = $meta['api_token'] ?? config('infrastructure.hetzner.token');

        if (! $token) {
            return null;
        }

        $serverId = $meta['cloud_instance_id'] ?? $meta['hetzner_server_id'] ?? null;

        if (! $serverId && ! str_contains(strtolower((string) $server->provider), 'hetzner')) {
            return null;
        }

        return ['token' => $token, 'server_id' => $serverId];
    }

    private static function hostFromReference(?string $reference): ?string
    {
        if (! $reference) {
            return null;
        }

        if (preg_match('#https?://([^/\s:]+)#i', $reference, $m)) {
            return strtolower($m[1]);
        }

        if (preg_match('/\b([a-z0-9][a-z0-9.-]+\.[a-z]{2,})\b/i', $reference, $m)) {
            return strtolower($m[1]);
        }

        return null;
    }

    private static function hostFromUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }

        $host = parse_url(str_contains($value, '://') ? $value : 'https://'.$value, PHP_URL_HOST);

        return $host ? strtolower($host) : null;
    }

    private static function firstNonEmpty(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }
}
