<?php

namespace App\Support;

final class IntegrationServiceOptions
{
    public const CATEGORY_PROVIDER = 'provider';

    public const CATEGORY_TENANT_SYSTEM = 'tenant_system';

    /**
     * @return array<string, string>
     */
    public static function providerServiceTypes(): array
    {
        return [
            'hostinger' => __('Hostinger'),
            'whm_cpanel' => __('WHM / cPanel'),
            'bulk_sms' => __('Bulk SMS'),
            'mpesa_payment' => __('M-Pesa'),
            'ai_openai' => __('OpenAI'),
            'smtp_email' => __('SMTP'),
            'whatsapp_api' => __('WhatsApp'),
            'storage_s3' => __('Storage / S3'),
            'maps_api' => __('Maps API'),
            'ocr_api' => __('OCR API'),
            'domain_dns' => __('Domain / DNS'),
            'backup_provider' => __('Backup provider'),
            'custom_api' => __('Custom provider API'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tenantSystemPurposes(): array
    {
        return [
            'health' => __('Health check'),
            'version' => __('Version endpoint'),
            'usage' => __('Usage sync'),
            'license' => __('License check'),
            'heartbeat' => __('Heartbeat'),
            'system_info' => __('System info'),
            'custom' => __('Custom'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function authenticationTypes(): array
    {
        return [
            'none' => __('None'),
            'bearer_token' => __('Bearer token'),
            'api_key_header' => __('API key header'),
            'basic_auth' => __('Basic auth'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function integrationCategories(): array
    {
        return [
            self::CATEGORY_PROVIDER => __('Provider integration'),
            self::CATEGORY_TENANT_SYSTEM => __('Tenant system API'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function serviceTypes(): array
    {
        return array_merge(
            self::providerServiceTypes(),
            ['tenant_system' => __('Tenant system API')],
        );
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'not_configured' => __('Not configured'),
            'active' => __('Active'),
            'failing' => __('Failing'),
            'suspended' => __('Suspended'),
            'inactive' => __('Inactive'),
            'pending' => __('Pending'),
        ];
    }

    public static function isTenantSystemPurpose(?string $purpose): bool
    {
        return $purpose !== null && array_key_exists($purpose, self::tenantSystemPurposes());
    }
}
