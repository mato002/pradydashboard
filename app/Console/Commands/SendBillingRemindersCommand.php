<?php

namespace App\Console\Commands;

use App\Domain\Billing\CollectionReminderService;
use Illuminate\Console\Command;

class SendBillingRemindersCommand extends Command
{
    protected $signature = 'billing:send-reminders';

    protected $description = 'Send payment reminders for overdue invoices per automation rules';

    public function handle(CollectionReminderService $reminders): int
    {
        $counts = $reminders->processAutomatedReminders();

        $this->info(sprintf(
            'Reminders sent: %d, skipped: %d',
            $counts['reminders'],
            $counts['skipped'],
        ));

        return self::SUCCESS;
    }
}
