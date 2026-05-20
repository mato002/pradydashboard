<?php

namespace App\Support\Billing;

final class BillingDocumentType
{
    public const INVOICE = 'invoice';

    public const QUOTATION = 'quotation';

    public const PROFORMA = 'proforma';

    public const RECEIPT = 'receipt';

    public const CREDIT_NOTE = 'credit_note';

    public const DEBIT_NOTE = 'debit_note';

    public const STATEMENT = 'statement';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::INVOICE,
            self::QUOTATION,
            self::PROFORMA,
            self::RECEIPT,
            self::CREDIT_NOTE,
            self::DEBIT_NOTE,
            self::STATEMENT,
        ];
    }

    public static function label(string $type): string
    {
        return match ($type) {
            self::QUOTATION => __('Quotation'),
            self::PROFORMA => __('Proforma'),
            self::RECEIPT => __('Receipt'),
            self::CREDIT_NOTE => __('Credit note'),
            self::DEBIT_NOTE => __('Debit note'),
            self::STATEMENT => __('Statement'),
            default => __('Invoice'),
        };
    }

    public static function numberPrefix(string $type): string
    {
        return match ($type) {
            self::QUOTATION => 'QUO',
            self::PROFORMA => 'PRO',
            self::RECEIPT => 'RCP',
            self::CREDIT_NOTE => 'CRN',
            self::DEBIT_NOTE => 'DBN',
            self::STATEMENT => 'STM',
            default => 'INV',
        };
    }

    public static function registerTypes(): array
    {
        return [self::INVOICE, self::QUOTATION, self::PROFORMA, self::RECEIPT];
    }
}
