<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\PaymentsGatewayTransactionImporter;
use App\Jobs\OperationalJob;
use App\Models\TenantPayment;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\Queue\QueueName;
use Carbon\Carbon;

class RetryFailedPaymentsJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::PAYMENTS);
    }

    public function handle(
        PaymentsGatewayClient $client,
        PaymentsGatewayTransactionImporter $importer,
    ): void {
        if ($client->isConfigured()) {
            $response = $client->listTransactions([
                'status' => 'failed',
                'from_date' => Carbon::now()->subDays(7)->toDateString(),
                'per_page' => 50,
            ]);

            if (! ($response['unavailable'] ?? false)) {
                foreach ($this->extractList($response['data'] ?? []) as $transaction) {
                    $importer->import($transaction);
                }
            }

            TenantPayment::query()
                ->where('status', 'failed')
                ->whereNotNull('gateway_transaction_uuid')
                ->limit(25)
                ->pluck('gateway_transaction_uuid')
                ->each(function (string $uuid) use ($client, $importer): void {
                    $response = $client->getTransaction($uuid);
                    $transaction = $client->extractResource($response);
                    if (is_array($transaction)) {
                        $importer->import($transaction);
                    }
                });
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractList(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $items = $data['data'] ?? $data;

        return is_array($items) && array_is_list($items)
            ? array_values(array_filter($items, 'is_array'))
            : [];
    }
}
