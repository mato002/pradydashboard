<?php

namespace App\Domain\Billing;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantProjectSubscription;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Collection;

/**
 * Builds an in-memory invoice snapshot for live manual-document preview.
 */
class ManualDocumentPreviewBuilder
{
    public function __construct(
        private readonly ManualLineItemCalculator $calculator,
        private readonly DocumentSnapshotBuilder $snapshotBuilder,
        private readonly DocumentIdentityService $identity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function build(array $data): array
    {
        $type = (string) ($data['document_type'] ?? BillingDocumentType::INVOICE);
        $draftNumber = 'DRAFT';

        $invoice = $type === BillingDocumentType::RECEIPT
            ? $this->buildReceiptInvoice($data, $draftNumber)
            : $this->buildBillableInvoice($type, $data, $draftNumber);

        $snapshot = $this->snapshotBuilder->build($invoice);
        $displayNumber = $this->identity->formatDisplayNumber($draftNumber);

        $snapshot['invoice_number'] = $draftNumber;
        $snapshot['display_number'] = $displayNumber;
        $snapshot['document']['number'] = $draftNumber;
        $snapshot['document']['display_number'] = $displayNumber;

        return $snapshot;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildBillableInvoice(string $documentType, array $data, string $draftNumber): TenantInvoice
    {
        $lines = $this->normalizeLines($data['line_items'] ?? []);
        $totals = $lines !== []
            ? $this->calculator->compute($lines)
            : ['lines' => [], 'subtotal' => 0.0, 'discount_amount' => 0.0, 'tax_amount' => 0.0, 'total' => 0.0];

        $tenant = $this->resolveTenant($data);
        $amountPaid = max(0, (float) ($data['amount_paid'] ?? 0));
        $total = $totals['total'];
        $amountDue = max(0, $total - $amountPaid);

        $invoice = new TenantInvoice([
            'tenant_id' => $tenant?->id,
            'invoice_number' => $draftNumber,
            'document_type' => $documentType,
            'currency' => (string) ($data['currency'] ?? $tenant?->billing_preferred_currency ?? $tenant?->tenant_currency ?? 'KES'),
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_amount'],
            'tax_amount' => $totals['tax_amount'],
            'total' => $total,
            'amount_due' => $amountDue,
            'amount_paid' => $amountPaid,
            'status' => 'draft',
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'manual_client_name' => $tenant ? null : ($data['manual_client_name'] ?? __('Client name')),
            'manual_client_email' => $data['manual_client_email'] ?? $tenant?->billing_email,
            'manual_client_phone' => $data['manual_client_phone'] ?? $tenant?->billing_phone,
            'manual_client_address' => $data['manual_client_address'] ?? $tenant?->billing_address,
            'approval_status' => $documentType === BillingDocumentType::QUOTATION ? 'pending' : null,
        ]);

        if ($tenant) {
            $invoice->setRelation('tenant', $tenant);
        }

        $subscription = $this->resolveSubscription($tenant, $data);
        if ($subscription) {
            $invoice->setRelation('projectSubscription', $subscription);
            $invoice->tenant_project_subscription_id = $subscription->id;
        }

        $invoice->setRelation('lineItems', $this->lineItemModels($totals['lines']));
        $invoice->setRelation('payments', collect());

        return $invoice;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildReceiptInvoice(array $data, string $draftNumber): TenantInvoice
    {
        if (! empty($data['linked_invoice_id'])) {
            return $this->buildLinkedReceiptInvoice($data, $draftNumber);
        }

        $amount = max(0, (float) ($data['amount_received'] ?? 0));
        $lines = [[
            'item_type' => 'payment',
            'description' => trim((string) ($data['line_description'] ?? '')) ?: __('Payment received'),
            'quantity' => 1,
            'unit_price' => $amount,
            'discount' => 0,
            'tax_rate' => 0,
        ]];
        $totals = $this->calculator->compute($lines);
        $tenant = $this->resolveTenant($data);

        $invoice = new TenantInvoice([
            'tenant_id' => $tenant?->id,
            'invoice_number' => $draftNumber,
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => (string) ($data['currency'] ?? 'KES'),
            'subtotal' => $totals['total'],
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $totals['total'],
            'amount_due' => 0,
            'amount_paid' => $totals['total'],
            'status' => 'paid',
            'issue_date' => $data['payment_date'] ?? $data['issue_date'] ?? now()->toDateString(),
            'due_date' => $data['payment_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'manual_client_name' => $tenant ? null : ($data['manual_client_name'] ?? __('Client name')),
            'manual_client_email' => $data['manual_client_email'] ?? $tenant?->billing_email,
            'manual_client_phone' => $data['manual_client_phone'] ?? $tenant?->billing_phone,
            'manual_client_address' => $data['manual_client_address'] ?? $tenant?->billing_address,
        ]);

        if ($tenant) {
            $invoice->setRelation('tenant', $tenant);
        }

        $invoice->setRelation('lineItems', $this->lineItemModels($totals['lines']));
        $invoice->setRelation('payments', collect());

        return $invoice;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildLinkedReceiptInvoice(array $data, string $draftNumber): TenantInvoice
    {
        $source = TenantInvoice::query()
            ->with(['tenant', 'lineItems'])
            ->findOrFail((int) $data['linked_invoice_id']);

        $amount = max(0, (float) ($data['amount_received'] ?? 0));
        $lines = [[
            'item_type' => 'payment',
            'description' => trim((string) ($data['line_description'] ?? '')) ?: __('Payment against invoice :number', ['number' => $source->invoice_number]),
            'quantity' => 1,
            'unit_price' => $amount > 0 ? $amount : (float) $source->balanceDue(),
            'discount' => 0,
            'tax_rate' => 0,
        ]];
        $totals = $this->calculator->compute($lines);
        $paymentAmount = (float) $totals['total'];
        $linkedTotal = $source->invoiceTotal() + (float) $source->penalty_amount;
        $previouslyPaid = (float) $source->amount_paid;
        $remainingBalance = max(0, $linkedTotal - $previouslyPaid - $paymentAmount);

        $invoice = new TenantInvoice([
            'tenant_id' => $source->tenant_id,
            'linked_invoice_id' => $source->id,
            'invoice_number' => $draftNumber,
            'document_type' => BillingDocumentType::RECEIPT,
            'currency' => $source->currency,
            'subtotal' => $paymentAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $paymentAmount,
            'amount_due' => $remainingBalance,
            'amount_paid' => $paymentAmount,
            'status' => 'paid',
            'issue_date' => $data['payment_date'] ?? $data['issue_date'] ?? now()->toDateString(),
            'due_date' => $data['payment_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'manual_client_name' => $source->manual_client_name,
            'manual_client_email' => $source->manual_client_email,
            'manual_client_phone' => $source->manual_client_phone,
            'manual_client_address' => $source->manual_client_address,
        ]);

        if ($source->tenant) {
            $invoice->setRelation('tenant', $source->tenant);
        }

        $invoice->setRelation('linkedInvoice', $source);
        $invoice->setRelation('lineItems', $this->lineItemModels($totals['lines']));
        $invoice->setRelation('payments', collect());

        return $invoice;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return Collection<int, TenantInvoiceLineItem>
     */
    private function lineItemModels(array $lines): Collection
    {
        return collect($lines)->map(fn (array $line) => new TenantInvoiceLineItem([
            'item_type' => $line['item_type'] ?? 'custom',
            'description' => $line['description'] ?? '',
            'quantity' => $line['quantity'] ?? 1,
            'unit_price' => $line['unit_price'] ?? 0,
            'discount' => $line['discount'] ?? 0,
            'tax_rate' => $line['tax_rate'] ?? 0,
            'tax_amount' => $line['tax_amount'] ?? 0,
            'line_total' => $line['line_total'] ?? 0,
        ]));
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return array<int, array<string, mixed>>
     */
    private function normalizeLines(array $raw): array
    {
        $lines = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                continue;
            }
            $itemType = (string) ($row['item_type'] ?? 'custom');
            if (! in_array($itemType, ManualDocumentCreator::LINE_ITEM_TYPES, true)) {
                $itemType = 'custom';
            }
            $lines[] = [
                'item_type' => $itemType,
                'description' => $description,
                'quantity' => $row['quantity'] ?? 1,
                'unit_price' => $row['unit_price'] ?? 0,
                'discount' => $row['discount'] ?? 0,
                'tax_rate' => $row['tax_rate'] ?? 0,
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveTenant(array $data): ?Tenant
    {
        if (empty($data['tenant_id'])) {
            return null;
        }

        return Tenant::query()->find((int) $data['tenant_id']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSubscription(?Tenant $tenant, array $data): ?TenantProjectSubscription
    {
        if (! $tenant || empty($data['tenant_project_subscription_id'])) {
            return null;
        }

        return TenantProjectSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->whereKey((int) $data['tenant_project_subscription_id'])
            ->with('project')
            ->first();
    }
}
