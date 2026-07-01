<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\TenantInvoice;
use App\Models\TenantInvoiceLineItem;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Facades\DB;

class ProformaConverter
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly DocumentTemplateResolver $templateResolver,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function convert(TenantInvoice $proforma): TenantInvoice
    {
        if ($proforma->document_type !== BillingDocumentType::PROFORMA) {
            throw new \InvalidArgumentException(__('Only proforma invoices can be converted.'));
        }

        if (in_array($proforma->status, ['cancelled', 'void'], true)) {
            throw new \InvalidArgumentException(__('Cancelled proforma invoices cannot be converted.'));
        }

        if ($proforma->isExpired()) {
            throw new \InvalidArgumentException(__('Expired proforma invoices cannot be converted.'));
        }

        if ($proforma->converted_invoice_id) {
            return TenantInvoice::query()->findOrFail($proforma->converted_invoice_id);
        }

        return DB::transaction(function () use ($proforma): TenantInvoice {
            $proforma->load('lineItems');

            $invoice = TenantInvoice::query()->create([
                'tenant_id' => $proforma->tenant_id,
                'tenant_project_subscription_id' => $proforma->tenant_project_subscription_id,
                'invoice_number' => $this->numberGenerator->next(BillingDocumentType::INVOICE),
                'document_type' => BillingDocumentType::INVOICE,
                'currency' => $proforma->currency,
                'subtotal' => $proforma->subtotal,
                'discount_amount' => (float) ($proforma->discount_amount ?? 0),
                'tax_amount' => (float) ($proforma->tax_amount ?? 0),
                'total' => $proforma->total,
                'amount_due' => $proforma->amount_due,
                'amount_paid' => 0,
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'issued_at' => now(),
                'due_date' => $proforma->due_date,
                'product_name' => $proforma->product_name,
                'notes' => $proforma->notes,
                'manual_client_name' => $proforma->manual_client_name,
                'manual_client_email' => $proforma->manual_client_email,
                'manual_client_phone' => $proforma->manual_client_phone,
                'manual_client_address' => $proforma->manual_client_address,
                'document_template_id' => $this->templateResolver->defaultInvoiceTemplateId() ?? $proforma->document_template_id,
                'created_source' => 'proforma_conversion',
                'generated_by' => auth()->user()?->email,
            ]);

            foreach ($proforma->lineItems as $line) {
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
                ]);
            }

            $proforma->update([
                'converted_at' => now(),
                'converted_invoice_id' => $invoice->id,
            ]);

            $this->activityLogger->log(
                'proforma.converted',
                ActivityLogCategory::BILLING,
                __('Proforma :number converted to invoice :inv', [
                    'number' => $proforma->invoice_number,
                    'inv' => $invoice->invoice_number,
                ]),
                $invoice,
                null,
                ['proforma_id' => $proforma->id],
            );

            return $invoice->fresh(['lineItems']);
        });
    }
}
