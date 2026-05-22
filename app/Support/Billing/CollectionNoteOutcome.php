<?php

namespace App\Support\Billing;

final class CollectionNoteOutcome
{
    public const NO_RESPONSE = 'no_response';

    public const PROMISED_PAYMENT = 'promised_payment';

    public const DISPUTED = 'disputed';

    public const PAID = 'paid';

    public const ESCALATED = 'escalated';

    public const SUSPENDED = 'suspended';

    public const OTHER = 'other';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::NO_RESPONSE => __('No response'),
            self::PROMISED_PAYMENT => __('Promised payment'),
            self::DISPUTED => __('Disputed'),
            self::PAID => __('Paid'),
            self::ESCALATED => __('Escalated'),
            self::SUSPENDED => __('Suspended'),
            self::OTHER => __('Other'),
        ];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
