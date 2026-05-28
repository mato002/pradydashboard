<?php

namespace App\Jobs\Integrations;

use App\Domain\Tenancy\TenantSystemApiClient;
use App\Jobs\OperationalJob;
use App\Models\TenantProjectServiceIntegration;
use App\Support\Queue\QueueName;

class PollTenantSystemApiJob extends OperationalJob
{
    public function __construct(
        public int $integrationId,
        public string $action = 'pull_system_info',
    ) {
        $this->onQueue(QueueName::INTEGRATIONS);
    }

    public function handle(TenantSystemApiClient $client): void
    {
        $integration = TenantProjectServiceIntegration::query()->find($this->integrationId);
        if (! $integration) {
            return;
        }

        $this->withLock(
            'integrations:poll:'.$this->integrationId.':'.$this->action,
            120,
            function () use ($client, $integration): void {
                match ($this->action) {
                    'test_connection' => $client->testConnection($integration),
                    'pull_version' => $client->pullVersionInfo($integration),
                    'pull_usage' => $client->pullUsageStats($integration),
                    'heartbeat' => $client->recordHeartbeat($integration),
                    default => $client->pullSystemInfo($integration),
                };
            },
        );
    }
}
