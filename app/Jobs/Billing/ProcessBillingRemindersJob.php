<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\CollectionReminderService;
use App\Jobs\OperationalJob;
use App\Support\Queue\QueueName;

class ProcessBillingRemindersJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::BILLING);
        $this->timeout = 600;
    }

    public function handle(CollectionReminderService $reminders): void
    {
        $this->withLock(
            'billing:send-reminders',
            config('redis_cache.locks.billing_recurring_run', 300),
            fn () => $reminders->processAutomatedReminders(),
        );
    }
}
