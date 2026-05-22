<?php

namespace App\Support\Billing;

final class PaymentReconciliationStatus
{
    public const UNRECONCILED = 'unreconciled';

    public const MATCHED = 'matched';

    public const PARTIALLY_MATCHED = 'partially_matched';

    public const DUPLICATE = 'duplicate';

    public const IGNORED = 'ignored';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::UNRECONCILED,
            self::MATCHED,
            self::PARTIALLY_MATCHED,
            self::DUPLICATE,
            self::IGNORED,
        ];
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::MATCHED => __('Matched'),
            self::PARTIALLY_MATCHED => __('Partially matched'),
            self::DUPLICATE => __('Duplicate'),
            self::IGNORED => __('Ignored'),
            default => __('Unreconciled'),
        };
    }

    public static function variant(string $status): string
    {
        return match ($status) {
            self::MATCHED => 'success',
            self::PARTIALLY_MATCHED => 'info',
            self::DUPLICATE => 'warning',
            self::IGNORED => 'neutral',
            default => 'warning',
        };
    }
}
