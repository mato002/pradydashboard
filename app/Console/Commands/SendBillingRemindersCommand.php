<?php

namespace App\Console\Commands;

use App\Jobs\Billing\ProcessBillingRemindersJob;
use Illuminate\Console\Command;

class SendBillingRemindersCommand extends Command
{
    protected $signature = 'billing:send-reminders';

    protected $description = 'Send automated payment reminder emails for overdue invoices';

    public function handle(): int
    {
        ProcessBillingRemindersJob::dispatch();

        $this->info(__('Billing reminder job dispatched to the queue.'));

        return self::SUCCESS;
    }
}
