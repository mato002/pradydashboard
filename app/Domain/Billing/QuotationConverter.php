<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Facades\DB;

class QuotationConverter
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly DocumentFinalizer $documentFinalizer,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function convert(TenantInvoice $quotation): TenantInvoice
    {
        if ($quotation->document_type !== BillingDocumentType::QUOTATION) {
            throw new \InvalidArgumentException(__('Only quotations can be converted.'));
        }

        if ($quotation->approval_status !== 'approved') {
            throw new \InvalidArgumentException(__('Quotation must be approved before conversion.'));
        }

        if ($quotation->converted_invoice_id) {
            return TenantInvoice::query()->findOrFail($quotation->converted_invoice_id);
        }

        return DB::transaction(function () use ($quotation): TenantInvoice {
            $quotation->load('lineItems');

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $quotation->tenant_id,
                'tenant_project_subscription_id' => $quotation->tenant_project_subscription_id,
                'invoice_number' => $this->numberGenerator->next(BillingDocumentType::INVOICE),
                'document_type' => BillingDocumentType::INVOICE,
                'currency' => $quotation->currency,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'amount_due' => $quotation->amount_due,
                'amount_paid' => 0,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'issued_at' => now(),
                'due_date' => $quotation->due_date,
                'product_name' => $quotation->product_name,
                'notes' => $quotation->notes,
                'source_quotation_id' => $quotation->id,
                'generated_by' => auth()->user()?->email,
            ]);

            foreach ($quotation->lineItems as $line) {
                TenantInvoiceLineItem::query()->create([
                    'tenant_invoice_id' => $invoice->id,
                    'item_type' => $line->item_type,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'discount' => $line->discount,
                    'tax_rate' => $line->tax_rate,
                    'tax_amount' => $line->tax_amount,
                    'line_total' => $line->line_total,
                    'related_model_type' => $line->related_model_type,
                    'related_model_id' => $line->related_model_id,
                ]);
            }

            $quotation->update([
                'converted_at' => now(),
                'converted_invoice_id' => $invoice->id,
            ]);

            $this->activityLogger->log(
                'quotation.converted',
                ActivityLogCategory::BILLING,
                __('Quotation :number converted to invoice :inv', [
                    'number' => $quotation->invoice_number,
                    'inv' => $invoice->invoice_number,
                ]),
                $invoice,
                null,
                ['quotation_id' => $quotation->id],
            );

            return $invoice->fresh(['lineItems']);
        });
    }

    public function approve(TenantInvoice $quotation): void
    {
        if ($quotation->document_type !== BillingDocumentType::QUOTATION) {
            throw new \InvalidArgumentException(__('Not a quotation.'));
        }

        $quotation->update(['approval_status' => 'approved']);

        $this->activityLogger->log(
            'quotation.approved',
            ActivityLogCategory::BILLING,
            __('Quotation :number approved', ['number' => $quotation->invoice_number]),
            $quotation,
        );
    }
}
