<?php

namespace App\Support;

class ActivityLogCategory
{
    public const TENANT = 'tenant';

    public const PROJECT = 'project';

    public const SERVER = 'server';

    public const BILLING = 'billing';

    public const LICENSE = 'license';

    public const DOCUMENT = 'document';

    public const INTEGRATION = 'integration';

    public const SUPPORT = 'support';

    public const COMMUNICATION = 'communication';

    public const HR = 'hr';

    public const SYSTEM = 'system';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::TENANT => __('Tenant'),
            self::PROJECT => __('Project'),
            self::SERVER => __('Server'),
            self::BILLING => __('Billing'),
            self::LICENSE => __('License'),
            self::DOCUMENT => __('Document'),
            self::INTEGRATION => __('Integration'),
            self::SUPPORT => __('Support'),
            self::COMMUNICATION => __('Communication'),
            self::HR => __('HR'),
            self::SYSTEM => __('System'),
        ];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
