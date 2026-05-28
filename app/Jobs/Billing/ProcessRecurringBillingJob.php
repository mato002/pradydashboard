<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\RecurringBillingProcessor;
use App\Jobs\OperationalJob;
use App\Support\Queue\QueueName;

class ProcessRecurringBillingJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::BILLING);
        $this->timeout = 600;
    }

    public function handle(RecurringBillingProcessor $processor): void
    {
        $this->withLock(
            'billing:process-recurring',
            config('redis_cache.locks.billing_recurring_run', 300),
            fn () => $processor->processDueSchedules(),
        );
    }
}
