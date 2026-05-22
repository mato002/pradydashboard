<?php

namespace App\Domain\Billing;

/**
 * Demo snapshot for template previews only (no real tenant data).
 *
 * @return array<string, mixed>
 */
class SampleFinancialDocumentSnapshot
{
    public static function proforma(): array
    {
        return [
            'document_type' => 'proforma',
            'invoice_number' => 'PRO-2026-0000',
            'status' => 'draft',
            'currency' => 'KES',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 10000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 1600.0,
            'total' => 11600.0,
            'amount_paid' => 0.0,
            'penalty_amount' => 0.0,
            'balance_due' => 11600.0,
            'notes' => null,
            'product_name' => __('Sample project'),
            'tenant' => [
                'company_name' => __('Sample client Ltd'),
                'billing_contact_name' => __('Sample contact'),
                'billing_email' => 'billing@example.com',
                'billing_phone' => '+254 000 000000',
                'billing_address' => __('Nairobi, Kenya'),
                'billing_tax_pin' => '—',
            ],
            'project' => ['name' => __('Sample project')],
            'line_items' => [
                [
                    'description' => __('Monthly platform subscription'),
                    'item_type' => 'subscription',
                    'quantity' => 1.0,
                    'unit_price' => 10000.0,
                    'discount' => 0.0,
                    'tax_rate' => 16.0,
                    'tax_amount' => 1600.0,
                    'line_total' => 11600.0,
                ],
            ],
            'payments' => [],
            'payment_options' => [
                'bank_name' => 'Example Bank',
                'bank_account_number' => '0000000000',
                'bank_branch' => 'HQ',
                'mpesa_paybill' => '123456',
                'paybill_account_number' => 'INVREF',
            ],
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
