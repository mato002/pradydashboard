<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\CollectionReminderService;
use App\Jobs\OperationalJob;
use App\Models\TenantInvoice;
use App\Support\Queue\QueueName;

class SendPaymentReminderJob extends OperationalJob
{
    public function __construct(
        public int $invoiceId,
        public ?string $recipientOverride = null,
    ) {
        $this->onQueue(QueueName::EMAILS);
    }

    public function handle(CollectionReminderService $reminders): void
    {
        $this->withLock(
            'job:reminder:invoice:'.$this->invoiceId,
            60,
            function () use ($reminders): void {
                $invoice = TenantInvoice::query()->find($this->invoiceId);
                if (! $invoice || $invoice->balanceDue() <= 0.009) {
                    return;
                }

                $reminders->sendReminder($invoice, $this->recipientOverride);
            },
        );
    }
}
