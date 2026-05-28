<?php

namespace App\Console\Commands;

use App\Jobs\Billing\ProcessOverdueBillingJob;
use Illuminate\Console\Command;

class ProcessOverdueBillingCommand extends Command
{
    protected $signature = 'billing:process-overdue';

    protected $description = 'Apply reminders, penalties, and suspension rules for overdue invoices';

    public function handle(): int
    {
        ProcessOverdueBillingJob::dispatch();

        $this->info(__('Overdue billing job dispatched to the queue.'));

        return self::SUCCESS;
    }
}
