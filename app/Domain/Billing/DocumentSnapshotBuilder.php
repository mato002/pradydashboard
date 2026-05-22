<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;

class DocumentSnapshotBuilder
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
    ) {}

    /**
     * Immutable payload captured at document finalization.
     *
     * @return array<string, mixed>
     */
    public function build(TenantInvoice $invoice): array
    {
        $invoice->loadMissing(['tenant', 'lineItems', 'projectSubscription.project', 'payments']);

        $clientName = $invoice->tenant?->company_name
            ?? $invoice->manual_client_name
            ?? __('Client');
        $clientEmail = $invoice->tenant?->billing_email ?? $invoice->manual_client_email;
        $clientPhone = $invoice->tenant?->billing_phone ?? $invoice->manual_client_phone;
        $clientAddress = $invoice->tenant?->billing_address ?? $invoice->manual_client_address;
        $contactName = $invoice->tenant?->billing_contact_name ?? $invoice->manual_client_name;

        return [
            'document_type' => $invoice->document_type ?? 'invoice',
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'issue_date' => optional($invoice->issue_date)->toDateString(),
            'due_date' => optional($invoice->due_date)->toDateString(),
            'subtotal' => (float) $invoice->subtotal,
            'discount_amount' => (float) $invoice->discount_amount,
            'tax_amount' => (float) $invoice->tax_amount,
            'total' => $invoice->invoiceTotal(),
            'amount_paid' => (float) $invoice->amount_paid,
            'penalty_amount' => (float) $invoice->penalty_amount,
            'balance_due' => $invoice->balanceDue(),
            'notes' => $invoice->notes,
            'product_name' => $invoice->product_name,
            'tenant' => [
                'company_name' => $clientName,
                'billing_contact_name' => $contactName,
                'billing_email' => $clientEmail,
                'billing_phone' => $clientPhone,
                'billing_address' => $clientAddress,
                'billing_tax_pin' => $invoice->tenant?->billing_tax_pin,
            ],
            'project' => [
                'name' => $invoice->projectSubscription?->project?->name,
            ],
            'line_items' => $invoice->lineItems->map(fn ($line) => [
                'description' => $line->description,
                'item_type' => $line->item_type,
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'discount' => (float) $line->discount,
                'tax_rate' => (float) $line->tax_rate,
                'tax_amount' => (float) $line->tax_amount,
                'line_total' => (float) $line->line_total,
            ])->values()->all(),
            'payments' => $invoice->payments->map(fn ($p) => [
                'amount' => (float) $p->amount,
                'paid_at' => optional($p->paid_at)->toDateString(),
                'method' => $p->method,
                'reference' => $p->reference,
            ])->values()->all(),
            'payment_options' => [
                'bank_name' => $this->billingSettings->bankName(),
                'bank_account_number' => $this->billingSettings->bankAccountNumber(),
                'bank_branch' => $this->billingSettings->bankBranch(),
                'mpesa_paybill' => $this->billingSettings->mpesaPaybill(),
                'paybill_account_number' => $this->billingSettings->paybillAccountNumber(),
            ],
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
