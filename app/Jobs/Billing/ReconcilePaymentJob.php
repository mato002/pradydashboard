<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\PaymentReconciliationService;
use App\Jobs\OperationalJob;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Queue\QueueName;
use Illuminate\Validation\ValidationException;

class ReconcilePaymentJob extends OperationalJob
{
    public function __construct(
        public int $paymentId,
        public int $invoiceId,
        public ?float $amount = null,
    ) {
        $this->onQueue(QueueName::PAYMENTS);
    }

    public function handle(PaymentReconciliationService $reconciliation): void
    {
        $payment = TenantPayment::query()->find($this->paymentId);
        $invoice = TenantInvoice::query()->find($this->invoiceId);

        if (! $payment || ! $invoice) {
            return;
        }

        try {
            $reconciliation->matchToInvoice($payment, $invoice, $this->amount);
        } catch (ValidationException $e) {
            // Idempotent skip when already matched or invalid state.
        }
    }
}
