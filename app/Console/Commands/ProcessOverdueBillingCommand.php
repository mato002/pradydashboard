<?php

namespace App\Console\Commands;

use App\Domain\Billing\OverdueBillingProcessor;
use Illuminate\Console\Command;

class ProcessOverdueBillingCommand extends Command
{
    protected $signature = 'billing:process-overdue';

    protected $description = 'Apply reminders, penalties, and suspension rules for overdue invoices';

    public function handle(OverdueBillingProcessor $processor): int
    {
        $counts = $processor->process();

        $this->info(__('Reminders: :r · Penalties: :p · Suspensions: :s', [
            'r' => $counts['reminders'],
            'p' => $counts['penalties'],
            's' => $counts['suspensions'],
        ]));

        return self::SUCCESS;
    }
}
