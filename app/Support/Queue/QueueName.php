<?php

namespace App\Support\Queue;

class QueueName
{
    public const CRITICAL = 'critical';

    public const PAYMENTS = 'payments';

    public const WEBHOOKS = 'webhooks';

    public const BILLING = 'billing';

    public const TELEMETRY = 'telemetry';

    public const EMAILS = 'emails';

    public const PDF = 'pdf';

    public const INTEGRATIONS = 'integrations';

    public const DEFAULT = 'default';

    public const LOW = 'low';

    /** @return list<string> */
    public static function all(): array
    {
        return config('queue_names.all', [
            self::CRITICAL,
            self::PAYMENTS,
            self::WEBHOOKS,
            self::BILLING,
            self::TELEMETRY,
            self::EMAILS,
            self::PDF,
            self::INTEGRATIONS,
            self::DEFAULT,
            self::LOW,
        ]);
    }
}
