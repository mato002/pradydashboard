<?php

namespace App\Support;

final class TenantOpsFormOptions
{
    /**
     * @return array<string, string>
     */
    public static function sslStatus(): array
    {
        return [
            'valid' => __('Valid'),
            'expiring' => __('Expiring soon'),
            'expired' => __('Expired'),
            'missing' => __('Missing'),
            'unknown' => __('Unknown'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function backupPolicy(): array
    {
        return [
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'none' => __('None'),
            'custom' => __('Custom'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function backupStatus(): array
    {
        return [
            'ok' => __('OK'),
            'warning' => __('Warning'),
            'failed' => __('Failed'),
            'never' => __('Never backed up'),
            'unknown' => __('Unknown'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function updateStatus(): array
    {
        return [
            'latest' => __('Latest'),
            'outdated' => __('Outdated'),
            'critical_update_required' => __('Critical update required'),
            'unknown' => __('Unknown'),
        ];
    }
}
