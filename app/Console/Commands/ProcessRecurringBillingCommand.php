<?php

namespace App\Console\Commands;

use App\Domain\Billing\RecurringBillingProcessor;
use Illuminate\Console\Command;

class ProcessRecurringBillingCommand extends Command
{
    protected $signature = 'billing:process-recurring';

    protected $description = 'Generate invoices from due recurring billing schedules';

    public function handle(RecurringBillingProcessor $processor): int
    {
        $generated = $processor->processDueSchedules();

        $this->info(__('Generated :count recurring invoice(s).', ['count' => $generated->count()]));

        return self::SUCCESS;
    }
}
