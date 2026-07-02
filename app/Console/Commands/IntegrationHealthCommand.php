<?php

namespace App\Console\Commands;

use App\Support\Operations\IntegrationHealthService;
use Illuminate\Console\Command;

class IntegrationHealthCommand extends Command
{
    protected $signature = 'ops:health {--json : Output results as JSON}';

    protected $description = 'Verify integration readiness (Redis, queues, Payments Gateway, infrastructure, tenants)';

    public function handle(IntegrationHealthService $health): int
    {
        $checks = $health->checks();

        if ($this->option('json')) {
            $this->line(json_encode([
                'blocking' => $health->hasBlockingIssues(),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $health->hasBlockingIssues() ? self::FAILURE : self::SUCCESS;
        }

        $this->info(__('Integration health check'));
        $this->newLine();

        $rows = collect($checks)->map(fn (array $check) => [
            $check['label'],
            strtoupper($check['status']),
            $check['message'],
        ])->all();

        $this->table([__('Component'), __('Status'), __('Details')], $rows);

        if ($health->hasBlockingIssues()) {
            $this->newLine();
            $this->error(__('Blocking issues detected — resolve P0 items before production use.'));
            $this->line(__('Tip: run `composer dev` to start server + queue worker + logs together.'));

            return self::FAILURE;
        }

        $warnings = array_filter($checks, fn (array $c) => $c['status'] === 'warn');
        if ($warnings !== []) {
            $this->newLine();
            $this->warn(__('Non-blocking warnings present — review before go-live.'));
        } else {
            $this->newLine();
            $this->info(__('All integration checks passed.'));
        }

        return self::SUCCESS;
    }
}
