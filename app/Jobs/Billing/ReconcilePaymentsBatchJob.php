<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\PaymentAutoReconciliationService;
use App\Jobs\OperationalJob;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Queue\QueueName;

class ReconcilePaymentsBatchJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::PAYMENTS);
    }

    public function handle(PaymentAutoReconciliationService $autoReconciliation): void
    {
        $payments = TenantPayment::query()
            ->where('status', 'successful')
            ->where('reconciliation_status', PaymentReconciliationStatus::UNRECONCILED)
            ->whereNotNull('tenant_id')
            ->where('amount', '>', 0)
            ->orderBy('paid_at')
            ->limit(50)
            ->get();

        foreach ($payments as $payment) {
            $autoReconciliation->tryReconcile($payment);
        }
    }
}
