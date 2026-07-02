<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\SubscriptionBillingService;
use App\Jobs\OperationalJob;
use App\Support\Queue\QueueName;

class RenewSubscriptionsJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::BILLING);
    }

    public function handle(SubscriptionBillingService $billing): void
    {
        $billing->renewFleet();
    }
}
