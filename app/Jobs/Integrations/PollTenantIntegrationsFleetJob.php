<?php

namespace App\Jobs\Integrations;

use App\Jobs\OperationalJob;
use App\Models\TenantProjectServiceIntegration;
use App\Support\Queue\QueueName;

class PollTenantIntegrationsFleetJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::INTEGRATIONS);
        $this->timeout = 300;
    }

    public function handle(): void
    {
        $this->withLock(
            'integrations:poll-fleet',
            300,
            function (): void {
                TenantProjectServiceIntegration::query()
                    ->where('enabled', true)
                    ->whereNotNull('endpoint_url')
                    ->orderBy('id')
                    ->pluck('id')
                    ->each(fn (int $id) => PollTenantSystemApiJob::dispatch($id, 'pull_system_info'));
            },
        );
    }
}
