<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\DocumentTemplate;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Models\TenantProjectSubscription;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualDocumentCreator
{
    /** @var list<string> */
    public const LINE_ITEM_TYPES = [
        'custom', 'subscription', 'module', 'integration', 'usage',
        'setup_fee', 'support', 'hosting', 'payment',
    ];

    public function __construct(
        private readonly ManualLineItemCalculator $calculator,
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly InvoicePaymentRecorder $paymentRecorder,
        private readonly ReceiptGenerator $receiptGenerator,
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantInvoice
    {
        $type = (string) ($data['document_type'] ?? BillingDocumentType::INVOICE);

        if ($type === BillingDocumentType::RECEIPT) {
            return $this->createReceipt($data);
        }

        return $this->createBillableDocument($type, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createBillableDocument(string $documentType, array $data): TenantInvoice
    {
        $lines = $this->normalizeLines($data['line_items'] ?? []);
        if ($lines === []) {
            throw ValidationException::withMessages([
                'line_items' => [__('At least one line item is required.')],
            ]);
        }

        $totals = $this->calculator->compute($lines);
        if ($totals['total'] < 0) {
            throw ValidationException::withMessages([
                'line_items' => [__('Total cannot be negative.')],
            ]);
        }

        $tenant = $this->resolveTenant($data);
        $amountPaid = max(0, (float) ($data['amount_paid'] ?? 0));
        $total = $totals['total'];
        $amountDue = max(0, $total - $amountPaid);

        return DB::transaction(function () use ($documentType, $data, $tenant, $totals, $amountPaid, $amountDue, $total): TenantInvoice {
            $subscription = $this->resolveSubscription($tenant, $data);

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $tenant?->id,
                'tenant_project_subscription_id' => $subscription?->id,
                'invoice_number' => $this->numberGenerator->next($documentType),
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
                'product_name' => $subscription?->project?->name ?? ($data['product_name'] ?? null),
                'manual_client_name' => $tenant ? null : ($data['manual_client_name'] ?? null),
                'manual_client_email' => $data['manual_client_email'] ?? $tenant?->billing_email,
                'manual_client_phone' => $data['manual_client_phone'] ?? $tenant?->billing_phone,
                'manual_client_address' => $data['manual_client_address'] ?? $tenant?->billing_address,
                'document_template_id' => $this->resolveTemplateId($documentType, $data),
                'approval_status' => $documentType === BillingDocumentType::QUOTATION ? 'pending' : null,
                'created_source' => 'manual',
                'generated_by' => Auth::user()?->email ?? 'manual',
            ]);

            foreach ($totals['lines'] as $line) {
                TenantInvoiceLineItem::query()->create([
                    'tenant_invoice_id' => $invoice->id,
                    'item_type' => $line['item_type'] ?? 'custom',
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => $line['discount'],
                    'tax_rate' => $line['tax_rate'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                ]);
            }

            if (in_array($documentType, [BillingDocumentType::PROFORMA, BillingDocumentType::QUOTATION], true)) {
                $invoice->status = 'draft';
            } else {
                $invoice->syncPaymentStatus();
                $invoice->amount_due = max(0, round($invoice->balanceDue(), 2));
                if ($invoice->status !== 'draft') {
                    $invoice->save();
                }
            }

            $this->logCreated($invoice);

            return $invoice->fresh(['lineItems', 'tenant', 'projectSubscription.project']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createReceipt(array $data): TenantInvoice
    {
        if (! empty($data['linked_invoice_id'])) {
            return $this->createLinkedReceipt($data);
        }

        return $this->createStandaloneReceipt($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createLinkedReceipt(array $data): TenantInvoice
    {
        $source = TenantInvoice::query()->findOrFail((int) $data['linked_invoice_id']);
        if ($source->document_type === BillingDocumentType::RECEIPT) {
            throw ValidationException::withMessages([
                'linked_invoice_id' => [__('Cannot link a receipt to another receipt.')],
            ]);
        }

        $amount = (float) ($data['amount_received'] ?? $data['amount'] ?? 0);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount_received' => [__('Amount received must be greater than zero.')],
            ]);
        }

        return DB::transaction(function () use ($source, $data, $amount): TenantInvoice {
            $this->paymentRecorder->record($source, [
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'method' => $data['payment_method'] ?? $data['method'] ?? 'manual',
                'reference' => $data['payment_reference'] ?? $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $receipt = TenantInvoice::query()
                ->where('document_type', BillingDocumentType::RECEIPT)
                ->where('linked_invoice_id', $source->id)
                ->latest('id')
                ->first();

            if (! $receipt) {
                $payment = $source->payments()->latest('id')->first();
                if ($payment) {
                    $receipt = $this->receiptGenerator->generateForPayment($source->fresh(), $payment);
                }
            }

            if ($receipt) {
                $receipt->update([
                    'created_source' => 'manual',
                    'document_template_id' => $this->resolveTemplateId(BillingDocumentType::RECEIPT, $data) ?? $receipt->document_template_id,
                ]);
                $this->activityLogger->log(
                    'manual.receipt_created',
                    ActivityLogCategory::BILLING,
                    __('Manual receipt :number linked to invoice :inv', [
                        'number' => $receipt->invoice_number,
                        'inv' => $source->invoice_number,
                    ]),
                    $receipt,
                );

                return $receipt->fresh(['lineItems', 'tenant']);
            }

            throw ValidationException::withMessages([
                'linked_invoice_id' => [__('Payment recorded but receipt document was not generated.')],
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createStandaloneReceipt(array $data): TenantInvoice
    {
        $amount = (float) ($data['amount_received'] ?? $data['amount'] ?? 0);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount_received' => [__('Amount received must be greater than zero.')],
            ]);
        }

        $lines = [[
            'item_type' => 'payment',
            'description' => $data['line_description'] ?? __('Payment received'),
            'quantity' => 1,
            'unit_price' => $amount,
            'discount' => 0,
            'tax_rate' => 0,
        ]];

        $totals = $this->calculator->compute($lines);
        $tenant = $this->resolveTenant($data);

        return DB::transaction(function () use ($data, $tenant, $totals, $amount): TenantInvoice {
            $receipt = TenantInvoice::query()->create([
                'tenant_id' => $tenant?->id,
                'invoice_number' => $this->numberGenerator->next(BillingDocumentType::RECEIPT),
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
                'issued_at' => now(),
                'due_date' => $data['payment_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? $data['method'] ?? null,
                'manual_client_name' => $tenant ? null : ($data['manual_client_name'] ?? null),
                'manual_client_email' => $data['manual_client_email'] ?? $tenant?->billing_email,
                'manual_client_phone' => $data['manual_client_phone'] ?? $tenant?->billing_phone,
                'manual_client_address' => $data['manual_client_address'] ?? $tenant?->billing_address,
                'document_template_id' => $this->resolveTemplateId(BillingDocumentType::RECEIPT, $data),
                'created_source' => 'manual',
                'generated_by' => Auth::user()?->email ?? 'manual',
            ]);

            foreach ($totals['lines'] as $line) {
                TenantInvoiceLineItem::query()->create([
                    'tenant_invoice_id' => $receipt->id,
                    'item_type' => $line['item_type'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => $line['discount'],
                    'tax_rate' => $line['tax_rate'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                ]);
            }

            $this->activityLogger->log(
                'manual.receipt_created',
                ActivityLogCategory::BILLING,
                __('Manual standalone receipt :number created', ['number' => $receipt->invoice_number]),
                $receipt,
            );

            return $receipt->fresh(['lineItems', 'tenant']);
        });
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
            if (! in_array($itemType, self::LINE_ITEM_TYPES, true)) {
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
            if (empty(trim((string) ($data['manual_client_name'] ?? '')))) {
                throw ValidationException::withMessages([
                    'manual_client_name' => [__('Client name is required when no tenant is selected.')],
                ]);
            }

            return null;
        }

        return Tenant::query()->findOrFail((int) $data['tenant_id']);
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveTemplateId(string $documentType, array $data): ?int
    {
        if (! empty($data['document_template_id'])) {
            $id = (int) $data['document_template_id'];
            $exists = DocumentTemplate::query()
                ->whereKey($id)
                ->where('type', $documentType)
                ->where('active', true)
                ->exists();
            if ($exists) {
                return $id;
            }
        }

        return DocumentTemplate::query()
            ->where('type', $documentType)
            ->where('active', true)
            ->orderByDesc('is_default')
            ->value('id');
    }

    private function logCreated(TenantInvoice $invoice): void
    {
        $action = match ($invoice->document_type) {
            BillingDocumentType::PROFORMA => 'manual.proforma_created',
            BillingDocumentType::QUOTATION => 'manual.quotation_created',
            BillingDocumentType::RECEIPT => 'manual.receipt_created',
            default => 'manual.invoice_created',
        };

        $this->activityLogger->log(
            $action,
            ActivityLogCategory::BILLING,
            __('Manual :type :number created', [
                'type' => $invoice->document_type,
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
        );
    }
}
