<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearApplicationDataCommand extends Command
{
    protected $signature = 'prady:clear-data
                            {--force : Skip confirmation}';

    protected $description = 'Remove all application data while keeping users (and framework tables)';

    /** @var list<string> */
    private const PRESERVED_TABLES = [
        'users',
        'password_reset_tokens',
        'sessions',
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Delete all tenants, servers, invoices, and other app data? Users will be kept.')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn (object $row): string => (string) array_values((array) $row)[0])
            ->reject(fn (string $name): bool => in_array($name, self::PRESERVED_TABLES, true))
            ->values()
            ->all();

        if ($tables === []) {
            $this->info('No application tables to clear.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                DB::table($table)->truncate();
                $this->line("  Cleared <info>{$table}</info>");
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $userCount = DB::table('users')->count();
        $this->newLine();
        $this->info("Done. {$userCount} user(s) kept. You can now add data through the admin UI.");

        return self::SUCCESS;
    }
}
