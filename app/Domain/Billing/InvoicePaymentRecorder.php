<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Support\Facades\DB;

class InvoicePaymentRecorder
{
    public function __construct(
        private readonly ReceiptGenerator $receiptGenerator,
    ) {}

    public function record(TenantInvoice $invoice, array $data): TenantPayment
    {
        return DB::transaction(function () use ($invoice, $data): TenantPayment {
            $payment = TenantPayment::query()->create([
                'tenant_id' => $invoice->tenant_id,
                'tenant_invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'currency' => $invoice->currency ?? 'KES',
                'status' => 'successful',
                'paid_at' => $data['payment_date'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->refresh();
            $paid = (float) $invoice->payments()->sum('amount');
            $invoice->amount_paid = $paid;
            $invoice->syncPaymentStatus();
            $invoice->save();

            if ($invoice->status === 'paid' && $invoice->document_type === 'invoice') {
                $this->receiptGenerator->generateForPayment($invoice->fresh(), $payment);
            }

            return $payment;
        });
    }
}
