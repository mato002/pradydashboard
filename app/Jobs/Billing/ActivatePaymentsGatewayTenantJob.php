<?php

namespace App\Jobs\Billing;

use App\Jobs\OperationalJob;
use App\Models\Tenant;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\Queue\QueueName;
use Illuminate\Support\Facades\Log;

class ActivatePaymentsGatewayTenantJob extends OperationalJob
{
    public function __construct(
        public int $tenantId,
    ) {
        $this->onQueue(QueueName::PAYMENTS);
    }

    public function handle(PaymentsGatewayClient $client): void
    {
        if (! $client->isConfigured()) {
            return;
        }

        $tenant = Tenant::query()->find($this->tenantId);
        $uuid = $tenant?->payments_gateway_tenant_uuid;

        if (! $tenant || ! filled($uuid)) {
            return;
        }

        $response = $client->activateTenant($uuid);

        if (! ($response['ok'] ?? false)) {
            Log::warning('Payments Gateway tenant activation failed', [
                'tenant_id' => $tenant->id,
                'uuid' => $uuid,
                'status' => $response['status'] ?? null,
                'message' => $response['message'] ?? $response['error'] ?? null,
            ]);
        }
    }
}
