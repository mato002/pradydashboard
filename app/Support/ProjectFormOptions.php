<?php

namespace App\Support;

final class ProjectFormOptions
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return [
            'status' => self::status(),
            'business_model' => self::businessModel(),
            'deployment_type' => self::deploymentType(),
            'billing_model' => self::billingModel(),
            'license_validation_mode' => self::licenseValidationMode(),
            'currency' => self::currency(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function status(): array
    {
        return [
            'development' => __('Development'),
            'active' => __('Active'),
            'maintenance' => __('Maintenance'),
            'suspended' => __('Suspended'),
            'retired' => __('Retired'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function businessModel(): array
    {
        return [
            'saas' => __('SaaS subscription'),
            'license' => __('Per-seat / license'),
            'perpetual' => __('Perpetual license'),
            'open_source' => __('Open source + support'),
            'hybrid' => __('Hybrid'),
            'custom' => __('Custom / enterprise'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function deploymentType(): array
    {
        return [
            'cloud' => __('Cloud hosted'),
            'on_premise' => __('On-premise'),
            'hybrid' => __('Hybrid'),
            'embedded' => __('Embedded / white-label'),
            'mobile' => __('Mobile app'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function billingModel(): array
    {
        return [
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
            'annual' => __('Annual'),
            'per_user' => __('Per user'),
            'usage_based' => __('Usage based'),
            'one_off' => __('One-off'),
            'hybrid' => __('Hybrid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function licenseValidationMode(): array
    {
        return [
            'api' => __('API check'),
            'offline_token' => __('Offline token'),
            'manual' => __('Manual approval'),
            'none' => __('No validation'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function currency(): array
    {
        return [
            'KES' => 'KES',
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'UGX' => 'UGX',
            'TZS' => 'TZS',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function moduleBillingStatus(): array
    {
        return [
            'active' => __('Active'),
            'trial' => __('Trial'),
            'suspended' => __('Suspended'),
            'waived' => __('Waived'),
            'cancelled' => __('Cancelled'),
        ];
    }
}
