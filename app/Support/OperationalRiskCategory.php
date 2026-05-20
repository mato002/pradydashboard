<?php

namespace App\Support;

class OperationalRiskCategory
{
    public const BILLING = 'billing';

    public const RENEWAL = 'renewal';

    public const SSL = 'ssl';

    public const DOCUMENT = 'document';

    public const CONTRACT = 'contract';

    public const HR = 'hr';

    public const SUPPORT = 'support';

    public const SERVER = 'server';

    public const INTEGRATION = 'integration';

    public const DEPLOYMENT = 'deployment';

    public const INFRASTRUCTURE = 'infrastructure';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::BILLING => __('Billing'),
            self::RENEWAL => __('Renewal'),
            self::SSL => __('SSL / certificates'),
            self::DOCUMENT => __('Documents'),
            self::CONTRACT => __('Contracts'),
            self::HR => __('HR'),
            self::SUPPORT => __('Support'),
            self::SERVER => __('Servers'),
            self::INTEGRATION => __('Integrations'),
            self::DEPLOYMENT => __('Deployments'),
            self::INFRASTRUCTURE => __('Infrastructure'),
        ];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
