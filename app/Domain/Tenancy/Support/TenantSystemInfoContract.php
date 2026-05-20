<?php

namespace App\Domain\Tenancy\Support;

/**
 * Canonical Tenant System API contract exposed by every Prady product installation.
 *
 * @see stubs/tenant-integration/ for sample Laravel implementation
 */
final class TenantSystemInfoContract
{
    public const ENDPOINT_METHOD = 'GET';

    public const ENDPOINT_PATH = '/api/system/info';

    public const ENV_DASHBOARD_TOKEN = 'PRADY_DASHBOARD_API_TOKEN';

    /**
     * @return list<string>
     */
    public static function coreFields(): array
    {
        return ['status', 'version'];
    }

    /**
     * @return list<string>
     */
    public static function recommendedFields(): array
    {
        return [
            'project',
            'tenant_code',
            'build',
            'commit',
            'environment',
            'app_url',
            'last_deployed_at',
            'usage',
            'health',
        ];
    }

    /**
     * @return list<string>
     */
    public static function usageMetricKeys(): array
    {
        return ['users', 'branches', 'storage_mb', 'sms_sent_month', 'api_requests_today'];
    }

    /**
     * @return list<string>
     */
    public static function healthComponentKeys(): array
    {
        return ['database', 'queue', 'scheduler', 'storage'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function samplePayload(): array
    {
        return [
            'status' => 'ok',
            'project' => 'Prady MFI',
            'tenant_code' => 'MATTARE',
            'version' => '1.2.3',
            'build' => '100',
            'commit' => 'abc123',
            'environment' => 'production',
            'app_url' => 'https://tenant-domain.com',
            'last_deployed_at' => '2026-05-23T10:00:00Z',
            'usage' => [
                'users' => 12,
                'branches' => 3,
                'storage_mb' => 542,
                'sms_sent_month' => 1200,
                'api_requests_today' => 350,
            ],
            'health' => [
                'database' => 'ok',
                'queue' => 'ok',
                'scheduler' => 'ok',
                'storage' => 'ok',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tenantEnvVariables(): array
    {
        return [
            self::ENV_DASHBOARD_TOKEN => __('Shared secret for Dashboard outbound calls (Bearer or X-API-Key).'),
            'PRADY_TENANT_CODE' => __('Short tenant identifier shown in Dashboard.'),
            'PRADY_PRODUCT_NAME' => __('Product display name (e.g. Prady MFI).'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function supportedAuthHeaders(): array
    {
        return [
            'Authorization: Bearer {PRADY_DASHBOARD_API_TOKEN}',
            'X-API-Key: {PRADY_DASHBOARD_API_TOKEN}',
        ];
    }
}
