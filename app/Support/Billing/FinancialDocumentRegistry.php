<?php

namespace App\Support\Billing;

final class FinancialDocumentRegistry
{
    /** @var array<string, array<string, mixed>> */
    private const DEFINITIONS = [
        BillingDocumentType::INVOICE => [
            'document_type' => BillingDocumentType::INVOICE,
            'title' => 'INVOICE',
            'label' => 'Invoice',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => true,
            'supports_conversion' => false,
            'shows_statement_rows' => false,
        ],
        BillingDocumentType::PROFORMA => [
            'document_type' => BillingDocumentType::PROFORMA,
            'title' => 'PROFORMA INVOICE',
            'label' => 'Proforma',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => true,
            'supports_conversion' => true,
            'shows_statement_rows' => false,
        ],
        BillingDocumentType::QUOTATION => [
            'document_type' => BillingDocumentType::QUOTATION,
            'title' => 'QUOTATION',
            'label' => 'Quotation',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => false,
            'supports_conversion' => true,
            'shows_statement_rows' => false,
        ],
        BillingDocumentType::RECEIPT => [
            'document_type' => BillingDocumentType::RECEIPT,
            'title' => 'RECEIPT',
            'label' => 'Receipt',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => true,
            'supports_conversion' => false,
            'shows_statement_rows' => false,
        ],
        BillingDocumentType::STATEMENT => [
            'document_type' => BillingDocumentType::STATEMENT,
            'title' => 'ACCOUNT STATEMENT',
            'label' => 'Statement',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => false,
            'shows_payment_options' => false,
            'shows_paid_balance' => false,
            'supports_conversion' => false,
            'shows_statement_rows' => true,
        ],
        BillingDocumentType::CREDIT_NOTE => [
            'document_type' => BillingDocumentType::CREDIT_NOTE,
            'title' => 'CREDIT NOTE',
            'label' => 'Credit note',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => false,
            'supports_conversion' => false,
            'shows_statement_rows' => false,
        ],
        BillingDocumentType::DEBIT_NOTE => [
            'document_type' => BillingDocumentType::DEBIT_NOTE,
            'title' => 'DEBIT NOTE',
            'label' => 'Debit note',
            'default_template_family' => 'prady_classic_a5',
            'paper_size' => 'A5',
            'orientation' => 'portrait',
            'has_line_items' => true,
            'shows_payment_options' => true,
            'shows_paid_balance' => false,
            'supports_conversion' => false,
            'shows_statement_rows' => false,
        ],
    ];

    /** @return list<string> */
    public static function coreTypes(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $documentType): array
    {
        return self::DEFINITIONS[$documentType]
            ?? self::DEFINITIONS[BillingDocumentType::INVOICE];
    }

    public static function title(string $documentType): string
    {
        return (string) self::get($documentType)['title'];
    }

    public static function label(string $documentType): string
    {
        return (string) self::get($documentType)['label'];
    }

    public static function defaultTemplateFamily(string $documentType): string
    {
        return (string) self::get($documentType)['default_template_family'];
    }

    public static function paperSize(string $documentType): string
    {
        return (string) self::get($documentType)['paper_size'];
    }

    public static function orientation(string $documentType): string
    {
        return (string) self::get($documentType)['orientation'];
    }

    public static function hasLineItems(string $documentType): bool
    {
        return (bool) self::get($documentType)['has_line_items'];
    }

    public static function showsPaymentOptions(string $documentType): bool
    {
        return (bool) self::get($documentType)['shows_payment_options'];
    }

    public static function showsPaidBalance(string $documentType): bool
    {
        return (bool) self::get($documentType)['shows_paid_balance'];
    }

    public static function supportsConversion(string $documentType): bool
    {
        return (bool) self::get($documentType)['supports_conversion'];
    }

    public static function showsStatementRows(string $documentType): bool
    {
        return (bool) self::get($documentType)['shows_statement_rows'];
    }
}
