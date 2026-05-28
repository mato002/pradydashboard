<?php

namespace App\Console\Commands;

use App\Jobs\Billing\ProcessRecurringBillingJob;
use Illuminate\Console\Command;

class ProcessRecurringBillingCommand extends Command
{
    protected $signature = 'billing:process-recurring';

    protected $description = 'Generate invoices from due recurring billing schedules';

    public function handle(): int
    {
        ProcessRecurringBillingJob::dispatch();

        $this->info(__('Recurring billing job dispatched to the queue.'));

        return self::SUCCESS;
    }
}
