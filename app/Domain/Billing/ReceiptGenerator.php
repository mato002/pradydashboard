<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\BillingAutomationRule;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\ActivityLogCategory;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Facades\DB;

class ReceiptGenerator
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
        private readonly DocumentFinalizer $documentFinalizer,
        private readonly InvoiceEmailDelivery $emailDelivery,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function generateForPayment(TenantInvoice $invoice, TenantPayment $payment): TenantInvoice
    {
        return DB::transaction(function () use ($invoice, $payment): TenantInvoice {
            $receipt = TenantInvoice::query()->create([
                'tenant_id' => $invoice->tenant_id,
                'tenant_project_subscription_id' => $invoice->tenant_project_subscription_id,
                'invoice_number' => $this->numberGenerator->next(BillingDocumentType::RECEIPT),
                'document_type' => BillingDocumentType::RECEIPT,
                'currency' => $invoice->currency,
                'subtotal' => $payment->amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $payment->amount,
                'amount_due' => 0,
                'amount_paid' => $payment->amount,
                'status' => 'paid',
                'issue_date' => optional($payment->paid_at)->toDateString() ?? now()->toDateString(),
                'issued_at' => now(),
                'due_date' => optional($payment->paid_at)->toDateString(),
                'product_name' => $invoice->product_name,
                'notes' => __('Receipt for invoice :number · Ref :ref', [
                    'number' => $invoice->invoice_number,
                    'ref' => $payment->reference ?? $payment->transaction_id ?? '—',
                ]),
                'payment_method' => $payment->method,
                'generated_by' => 'billing:auto-receipt',
            ]);

            $receipt->lineItems()->create([
                'item_type' => 'payment',
                'description' => __('Payment received — :method', ['method' => $payment->method]),
                'quantity' => 1,
                'unit_price' => $payment->amount,
                'discount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'line_total' => $payment->amount,
            ]);

            $document = $this->documentFinalizer->finalize($receipt);

            if (BillingAutomationRule::platform()->auto_send_receipts) {
                $this->emailDelivery->send($receipt, $document);
            }

            $this->activityLogger->log(
                'receipt.generated',
                ActivityLogCategory::BILLING,
                __('Receipt :number generated for invoice :inv', [
                    'number' => $receipt->invoice_number,
                    'inv' => $invoice->invoice_number,
                ]),
                $receipt,
                null,
                ['payment_id' => $payment->id, 'source_invoice_id' => $invoice->id],
            );

            return $receipt;
        });
    }
}
