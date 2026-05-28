<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\OverdueBillingProcessor;
use App\Jobs\OperationalJob;
use App\Support\Queue\QueueName;

class ProcessOverdueBillingJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::BILLING);
        $this->timeout = 600;
    }

    public function handle(OverdueBillingProcessor $processor): void
    {
        $this->withLock(
            'billing:process-overdue',
            config('redis_cache.locks.billing_recurring_run', 300),
            fn () => $processor->process(),
        );
    }
}
