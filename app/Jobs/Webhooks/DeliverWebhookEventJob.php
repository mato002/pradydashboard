<?php

namespace App\Jobs\Webhooks;

use App\Jobs\OperationalJob;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\Queue\QueueName;

class DeliverWebhookEventJob extends OperationalJob
{
    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [60, 300, 900, 1800, 3600];

    public function __construct(
        public string $eventUuid,
        public bool $force = false,
        public ?string $deliveryUuid = null,
    ) {
        $this->onQueue(QueueName::WEBHOOKS);
    }

    public function handle(PaymentsGatewayClient $client): void
    {
        $lockKey = $this->deliveryUuid
            ? 'webhook:delivery:'.$this->deliveryUuid
            : 'webhook:event:'.$this->eventUuid;

        $this->withLock($lockKey, 60, function () use ($client): void {
            if ($this->deliveryUuid) {
                $client->redispatchWebhookDelivery($this->deliveryUuid);

                return;
            }

            $client->redispatchWebhookEvent($this->eventUuid, $this->force);
        });
    }
}
