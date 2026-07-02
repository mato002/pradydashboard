<?php

namespace App\Support\Billing;

final class PaymentSource
{
    public const MANUAL = 'manual';

    public const BANK_TRANSFER = 'bank_transfer';

    public const MPESA = 'mpesa';

    public const CASH = 'cash';

    public const CARD = 'card';

    public const CHEQUE = 'cheque';

    public const OTHER = 'other';

    public const PAYMENTS_GATEWAY = 'payments_gateway';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::MANUAL,
            self::BANK_TRANSFER,
            self::MPESA,
            self::CASH,
            self::CARD,
            self::CHEQUE,
            self::OTHER,
            self::PAYMENTS_GATEWAY,
        ];
    }

    public static function label(string $source): string
    {
        return match ($source) {
            self::BANK_TRANSFER => __('Bank transfer'),
            self::MPESA => __('M-Pesa'),
            self::CASH => __('Cash'),
            self::CARD => __('Card'),
            self::CHEQUE => __('Cheque'),
            self::OTHER => __('Other'),
            self::PAYMENTS_GATEWAY => __('Payments Gateway'),
            default => __('Manual'),
        };
    }
}
