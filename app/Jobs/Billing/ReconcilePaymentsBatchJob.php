<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\PaymentAutoReconciliationService;
use App\Jobs\OperationalJob;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Queue\QueueName;
use Illuminate\Support\Facades\Cache;

class ReconcilePaymentsBatchJob extends OperationalJob
{
    public const BATCH_LIMIT = 50;

    public const LAST_RUN_CACHE_KEY = 'billing:reconciliation:last_run';

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
            ->limit(self::BATCH_LIMIT)
            ->get();

        $matched = 0;

        foreach ($payments as $payment) {
            if ($autoReconciliation->tryReconcile($payment)) {
                $matched++;
            }
        }

        Cache::put(self::LAST_RUN_CACHE_KEY, [
            'at' => now()->toIso8601String(),
            'processed' => $payments->count(),
            'matched' => $matched,
        ], now()->addDays(14));
    }
}
