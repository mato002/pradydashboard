<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\PaybillAccountReferenceResolver;

class DocumentSnapshotBuilder
{
    public function __construct(
        private readonly BillingSettings $billingSettings,
        private readonly DocumentIssuerResolver $issuerResolver,
        private readonly StatementDataBuilder $statementDataBuilder,
        private readonly DocumentIdentityService $identity,
        private readonly DocumentPresentationResolver $presentation,
    ) {}

    /**
     * Immutable payload captured at document finalization.
     *
     * @return array<string, mixed>
     */
    public function build(TenantInvoice $invoice): array
    {
        $invoice->loadMissing([
            'tenant',
            'lineItems',
            'projectSubscription.project',
            'payments',
            'linkedInvoice',
            'convertedInvoice',
            'sourceQuotation',
        ]);

        $documentType = $invoice->document_type ?? BillingDocumentType::INVOICE;
        $clientName = $invoice->tenant?->company_name
            ?? $invoice->manual_client_name
            ?? __('Client');
        $clientEmail = $invoice->tenant?->billing_email ?? $invoice->manual_client_email;
        $clientPhone = $invoice->tenant?->billing_phone ?? $invoice->manual_client_phone;
        $clientAddress = $invoice->tenant?->billing_address ?? $invoice->manual_client_address;
        $contactName = $invoice->tenant?->billing_contact_name ?? $invoice->manual_client_name;

        $issuer = $this->issuerResolver->resolve();
        $statementData = $this->resolveStatementData($invoice);
        $presentation = $this->presentation->resolve($invoice);
        $sourceProformaNumber = $this->resolveSourceProformaNumber($invoice);

        $lineItems = $invoice->lineItems->map(fn ($line) => [
            'description' => $line->description,
            'item_type' => $line->item_type,
            'quantity' => (float) $line->quantity,
            'unit_price' => (float) $line->unit_price,
            'discount' => (float) $line->discount,
            'tax_rate' => (float) $line->tax_rate,
            'tax' => (float) $line->tax_amount,
            'tax_amount' => (float) $line->tax_amount,
            'line_total' => (float) $line->line_total,
        ])->values()->all();

        $paymentOptions = [
            'bank_name' => $this->billingSettings->bankName(),
            'account_name' => $this->billingSettings->bankAccountName(),
            'account_number' => $this->billingSettings->bankAccountNumber(),
            'bank_account_number' => $this->billingSettings->bankAccountNumber(),
            'bank_branch' => $this->billingSettings->bankBranch(),
            'paybill' => $this->billingSettings->mpesaPaybill(),
            'mpesa_paybill' => $this->billingSettings->mpesaPaybill(),
            'paybill_account' => PaybillAccountReferenceResolver::fromClientName($clientName),
            'paybill_account_number' => PaybillAccountReferenceResolver::fromClientName($clientName),
            'payment_instructions' => $this->billingSettings->paymentInstructions(),
        ];

        $conversionLinks = [
            'linked_invoice_number' => $invoice->linkedInvoice?->invoice_number,
            'source_quotation_number' => $invoice->sourceQuotation?->invoice_number,
            'source_proforma_number' => $sourceProformaNumber,
            'converted_invoice_number' => $invoice->convertedInvoice?->invoice_number,
            'converted_at' => optional($invoice->converted_at)->toDateString(),
        ];

        $totals = $this->resolveTotals($invoice);

        $documentBlock = [
            'type' => $documentType,
            'title' => $this->identity->resolveTitle($documentType),
            'number' => $invoice->invoice_number,
            'display_number' => $this->identity->formatDisplayNumber($invoice->invoice_number),
            'issue_date' => optional($invoice->issue_date)->toDateString(),
            'due_date' => optional($invoice->due_date)->toDateString(),
            'status' => $invoice->status,
            'lifecycle_label' => $invoice->lifecycleLabel(),
            'delivery_status' => $invoice->delivery_status,
        ];

        $clientBlock = [
            'name' => $clientName,
            'email' => $clientEmail,
            'phone' => $clientPhone,
            'address' => $clientAddress,
            'contact_name' => $contactName,
            'tax_pin' => $invoice->tenant?->billing_tax_pin,
        ];

        $snapshot = [
            // Legacy flat keys (preserved)
            'document_type' => $documentType,
            'invoice_number' => $invoice->invoice_number,
            'display_number' => $documentBlock['display_number'],
            'status' => $invoice->status,
            'approval_status' => $invoice->approval_status,
            'currency' => $invoice->currency,
            'issue_date' => $documentBlock['issue_date'],
            'due_date' => $documentBlock['due_date'],
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount'],
            'tax_amount' => $totals['tax'],
            'total' => $totals['total'],
            'amount_paid' => $totals['paid'],
            'penalty_amount' => (float) $invoice->penalty_amount,
            'balance_due' => $totals['balance'],
            'notes' => $invoice->notes,
            'product_name' => $invoice->product_name,
            'issuer' => $issuer,
            'document' => $documentBlock,
            'client' => $clientBlock,
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
            'line_items' => $lineItems,
            'payments' => $invoice->payments->map(fn ($p) => [
                'amount' => (float) $p->amount,
                'paid_at' => optional($p->paid_at)->toDateString(),
                'method' => $p->method,
                'reference' => $p->reference,
            ])->values()->all(),
            'payment_options' => $paymentOptions,
            'totals' => $totals,
            'delivery' => [
                'status' => $invoice->delivery_status,
                'finalized' => $invoice->finalized_at !== null,
            ],
            'conversion_links' => $conversionLinks,
            'presentation' => $presentation,
            'captured_at' => now()->toIso8601String(),
        ];

        if ($statementData !== null) {
            $statementBlock = [
                'period_start' => $statementData['period_start'],
                'period_end' => $statementData['period_end'],
                'opening_balance' => $statementData['opening_balance'],
                'rows' => $statementData['rows'],
                'closing_balance' => $statementData['closing_balance'],
            ];

            $snapshot['statement'] = $statementBlock;
            $snapshot['opening_balance'] = $statementBlock['opening_balance'];
            $snapshot['period_start'] = $statementBlock['period_start'];
            $snapshot['period_end'] = $statementBlock['period_end'];
            $snapshot['statement_rows'] = $statementBlock['rows'];
            $snapshot['closing_balance'] = $statementBlock['closing_balance'];
        }

        return $snapshot;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveStatementData(TenantInvoice $invoice): ?array
    {
        if ($invoice->document_type !== BillingDocumentType::STATEMENT) {
            return null;
        }

        if ($invoice->finalized_at) {
            $persisted = $invoice->relationLoaded('generatedDocuments')
                ? $invoice->generatedDocuments->sortByDesc('id')->first()
                : $invoice->generatedDocuments()->latest('id')->first();

            $stored = $persisted?->data_snapshot['statement'] ?? null;
            if (is_array($stored) && ! empty($stored['rows'])) {
                return $stored;
            }
        }

        return $this->statementDataBuilder->buildFromInvoice($invoice);
    }

    private function resolveSourceProformaNumber(TenantInvoice $invoice): ?string
    {
        if ($invoice->document_type !== BillingDocumentType::INVOICE) {
            return null;
        }

        if ($invoice->created_source === 'proforma_conversion') {
            return TenantInvoice::query()
                ->where('document_type', BillingDocumentType::PROFORMA)
                ->where('converted_invoice_id', $invoice->id)
                ->value('invoice_number');
        }

        return null;
    }

    /**
     * @return array{subtotal: float, tax: float, discount: float, total: float, paid: float, balance: float}
     */
    private function resolveTotals(TenantInvoice $invoice): array
    {
        if (($invoice->document_type ?? BillingDocumentType::INVOICE) === BillingDocumentType::RECEIPT) {
            return $this->resolveReceiptTotals($invoice);
        }

        return [
            'subtotal' => (float) $invoice->subtotal,
            'tax' => (float) $invoice->tax_amount,
            'discount' => (float) $invoice->discount_amount,
            'total' => (float) $invoice->invoiceTotal(),
            'paid' => (float) $invoice->amount_paid,
            'balance' => $invoice->balanceDue(),
        ];
    }

    /**
     * Receipt PAID/BALANCE reflect this payment and the linked invoice balance when applicable.
     *
     * @return array{subtotal: float, tax: float, discount: float, total: float, paid: float, balance: float}
     */
    private function resolveReceiptTotals(TenantInvoice $invoice): array
    {
        $receiptAmount = (float) $invoice->total;
        $linked = $invoice->linkedInvoice;

        if ($linked) {
            $linkedTotal = $linked->invoiceTotal() + (float) $linked->penalty_amount;
            $paidOnLinked = (float) $linked->amount_paid;

            $balance = $invoice->exists
                ? max(0, $linkedTotal - $paidOnLinked)
                : max(0, $linkedTotal - $paidOnLinked - $receiptAmount);

            return [
                'subtotal' => $receiptAmount,
                'tax' => 0.0,
                'discount' => 0.0,
                'total' => $receiptAmount,
                'paid' => $receiptAmount,
                'balance' => $balance,
            ];
        }

        return [
            'subtotal' => $receiptAmount,
            'tax' => 0.0,
            'discount' => 0.0,
            'total' => $receiptAmount,
            'paid' => $receiptAmount,
            'balance' => 0.0,
        ];
    }
}
