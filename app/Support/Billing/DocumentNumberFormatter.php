<?php

namespace App\Support\Billing;

final class DocumentNumberFormatter
{
    /**
     * Display label for PDF/header (e.g. "No. 39/26").
     */
    public static function display(?string $invoiceNumber): string
    {
        $number = trim((string) $invoiceNumber);
        if ($number === '') {
            return '—';
        }

        if (str_starts_with(strtoupper($number), 'NO.')) {
            return $number;
        }

        return 'No. '.$number;
    }

    /**
     * Parse sequence from a short-format number ({seq}/{YY}).
     */
    public static function parseShortSequence(string $invoiceNumber): ?int
    {
        if (preg_match('/^(\d+)\/(\d{2})$/', trim($invoiceNumber), $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Parse two-digit year suffix from short-format number.
     */
    public static function parseShortYear(string $invoiceNumber): ?string
    {
        if (preg_match('/^(\d+)\/(\d{2})$/', trim($invoiceNumber), $matches)) {
            return $matches[2];
        }

        return null;
    }

    public static function isShortFormat(string $invoiceNumber): bool
    {
        return (bool) preg_match('/^\d+\/\d{2}$/', trim($invoiceNumber));
    }
}
