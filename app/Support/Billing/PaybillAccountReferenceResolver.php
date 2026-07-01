<?php

namespace App\Support\Billing;

/**
 * Derives the M-Pesa Paybill account reference shown on financial documents.
 * One shared Paybill number; each client pays using their own account reference (usually their name).
 */
final class PaybillAccountReferenceResolver
{
    public static function fromClientName(?string $clientName): string
    {
        $name = trim((string) $clientName);
        if ($name === '') {
            return '';
        }

        $name = strtoupper($name);
        foreach ([' LIMITED', ' LTD', ' LLC', ' INC', ' PLC'] as $suffix) {
            if (str_ends_with($name, $suffix)) {
                $name = trim(substr($name, 0, -strlen($suffix)));
                break;
            }
        }

        // e.g. "PAGE CAPITAL" -> "PAGECAPITAL", "CLIENT NAME" -> "CLIENTNAME"
        $normalized = preg_replace('/[^A-Z0-9]/', '', $name);

        return (string) $normalized;
    }
}
