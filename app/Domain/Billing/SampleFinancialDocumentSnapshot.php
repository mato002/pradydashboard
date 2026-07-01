<?php

namespace App\Domain\Billing;

use App\Support\Billing\PaybillAccountReferenceResolver;
/**
 * Demo snapshot for template previews only (no real tenant data).
 */
class SampleFinancialDocumentSnapshot
{
    /**
     * @return array<string, mixed>
     */
    public static function proforma(): array
    {
        return self::buildSample('proforma', '175/24', 'PAGE CAPITAL LTD', [
            [
                'description' => 'Prady Technologies Microfinance Renting',
                'item_type' => 'subscription',
                'quantity' => 1.0,
                'unit_price' => 30000.0,
                'discount' => 0.0,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'line_total' => 30000.0,
            ],
        ], 30000.0, 0.0, 30000.0);
    }

    /**
     * @return array<string, mixed>
     */
    public static function receipt(): array
    {
        $snapshot = self::buildSample('receipt', '39/26', 'PAGE CAPITAL LTD', [
            [
                'description' => 'Prady Technologies Microfinance Renting',
                'item_type' => 'payment',
                'quantity' => 1.0,
                'unit_price' => 30000.0,
                'discount' => 0.0,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'line_total' => 30000.0,
            ],
        ], 30000.0, 0.0, 30000.0);

        $snapshot['status'] = 'paid';
        $snapshot['issue_date'] = '2026-03-03';

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public static function invoice(): array
    {
        return self::buildSample('invoice', '175/24', 'PAGE CAPITAL LTD', [
            [
                'description' => 'Prady Technologies Microfinance Renting',
                'item_type' => 'custom',
                'quantity' => 1.0,
                'unit_price' => 30000.0,
                'discount' => 0.0,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'line_total' => 30000.0,
            ],
        ], 30000.0, 0.0, 30000.0);
    }

    /**
     * @return array<string, mixed>
     */
    public static function quotation(): array
    {
        return self::buildSample('quotation', '42/26', 'PAGE CAPITAL LTD', [
            [
                'description' => 'Prady Technologies Microfinance Renting',
                'item_type' => 'subscription',
                'quantity' => 1.0,
                'unit_price' => 30000.0,
                'discount' => 0.0,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'line_total' => 30000.0,
            ],
        ], 30000.0, 0.0, 30000.0);
    }

    /**
     * @return array<string, mixed>
     */
    public static function statement(): array
    {
        $snapshot = self::buildSample('statement', '12/26', 'PAGE CAPITAL LTD', [], 30000.0, 0.0, 30000.0);
        $rows = [
            [
                'date' => '2026-03-01',
                'reference' => '—',
                'description' => __('Opening balance'),
                'debit' => 0.0,
                'credit' => 0.0,
                'balance' => 0.0,
            ],
            [
                'date' => '2026-03-03',
                'reference' => '175/24',
                'description' => __('Invoice'),
                'debit' => 30000.0,
                'credit' => 0.0,
                'balance' => 30000.0,
            ],
        ];

        $snapshot['line_items'] = [];
        $snapshot['period_start'] = '2026-03-01';
        $snapshot['period_end'] = '2026-03-31';
        $snapshot['opening_balance'] = 0.0;
        $snapshot['closing_balance'] = 30000.0;
        $snapshot['statement_rows'] = $rows;
        $snapshot['statement'] = [
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'opening_balance' => 0.0,
            'closing_balance' => 30000.0,
            'rows' => $rows,
        ];

        return $snapshot;
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @return array<string, mixed>
     */
    private static function buildSample(
        string $documentType,
        string $number,
        string $clientName,
        array $lineItems,
        float $total,
        float $paid,
        float $balance,
    ): array {
        $issuer = app(DocumentIssuerResolver::class)->resolve();
        $billing = app(BillingSettings::class);
        $presentation = app(DocumentPresentationResolver::class);

        $invoice = new \App\Models\TenantInvoice([
            'document_type' => $documentType,
            'invoice_number' => $number,
            'status' => 'draft',
            'currency' => 'KES',
            'issue_date' => '2026-03-03',
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => $total,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => $total,
            'amount_paid' => $paid,
            'amount_due' => $balance,
            'manual_client_name' => $clientName,
        ]);

        $paymentOptions = [
            'bank_name' => $billing->bankName() ?: 'Absa bank',
            'account_name' => $billing->bankAccountName() ?: 'P technologies Ltd',
            'account_number' => $billing->bankAccountNumber() ?: '2040790635',
            'bank_account_number' => $billing->bankAccountNumber() ?: '2040790635',
            'bank_branch' => $billing->bankBranch() ?: 'Gilgil',
            'paybill' => $billing->mpesaPaybill() ?: '918885',
            'mpesa_paybill' => $billing->mpesaPaybill() ?: '918885',
            'paybill_account' => PaybillAccountReferenceResolver::fromClientName($clientName),
            'paybill_account_number' => PaybillAccountReferenceResolver::fromClientName($clientName),
            'payment_instructions' => $billing->paymentInstructions(),
        ];

        return [
            'document_type' => $documentType,
            'invoice_number' => $number,
            'display_number' => 'No. '.$number,
            'status' => 'draft',
            'currency' => 'KES',
            'issue_date' => '2026-03-03',
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => $total,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'total' => $total,
            'amount_paid' => $paid,
            'penalty_amount' => 0.0,
            'balance_due' => $balance,
            'notes' => null,
            'product_name' => null,
            'issuer' => array_merge($issuer, [
                'phone' => $issuer['phone'] ?: '0110 098 548 / 0722 295 194',
                'email' => $issuer['email'] ?: 'info@pradytec.com',
                'website' => $issuer['website'] ?: 'www.pradytec.com',
                'address' => $issuer['address'] ?: 'Nakuru, Barnabas, Opp. Epic Academy',
                'tagline' => $issuer['tagline'] ?: 'DOING I.T DIFFERENTLY',
            ]),
            'document' => [
                'type' => $documentType,
                'title' => app(DocumentIdentityService::class)->resolveTitle($documentType),
                'number' => $number,
                'display_number' => 'No. '.$number,
                'issue_date' => '2026-03-03',
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'draft',
                'lifecycle_label' => null,
                'delivery_status' => 'not_sent',
            ],
            'client' => [
                'name' => $clientName,
                'salutation' => 'Ms.',
                'email' => null,
                'phone' => null,
                'address' => null,
            ],
            'tenant' => [
                'company_name' => $clientName,
                'billing_contact_name' => null,
                'billing_email' => null,
                'billing_phone' => null,
                'billing_address' => null,
                'billing_tax_pin' => null,
            ],
            'project' => ['name' => null],
            'line_items' => $lineItems,
            'payments' => [],
            'payment_options' => $paymentOptions,
            'totals' => [
                'subtotal' => $total,
                'tax' => 0.0,
                'discount' => 0.0,
                'total' => $total,
                'paid' => $paid,
                'balance' => $balance,
            ],
            'delivery' => ['status' => 'not_sent', 'finalized' => false],
            'conversion_links' => [
                'linked_invoice_number' => null,
                'converted_invoice_number' => null,
                'source_quotation_number' => null,
                'source_proforma_number' => null,
                'converted_at' => null,
            ],
            'presentation' => $presentation->resolve($invoice),
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
