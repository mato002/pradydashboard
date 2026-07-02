<?php

namespace App\Jobs\Billing;

use App\Domain\Billing\PaymentsGatewayTransactionImporter;
use App\Jobs\OperationalJob;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\Queue\QueueName;
use Carbon\Carbon;

class SyncPaymentsGatewayTransactionsJob extends OperationalJob
{
    public function __construct()
    {
        $this->onQueue(QueueName::PAYMENTS);
    }

    public function handle(
        PaymentsGatewayClient $client,
        PaymentsGatewayTransactionImporter $importer,
    ): void {
        if (! config('payment_gateway.sync_enabled', true) || ! $client->isConfigured()) {
            return;
        }

        $hours = max(1, (int) config('payment_gateway.sync_lookback_hours', 24));
        $response = $client->listTransactions([
            'from_date' => Carbon::now()->subHours($hours)->toDateString(),
            'per_page' => 100,
        ]);

        if ($response['unavailable'] ?? false) {
            return;
        }

        foreach ($this->extractTransactions($response['data'] ?? []) as $transaction) {
            $importer->import($transaction);
        }

        if (config('payment_gateway.auto_reconcile_enabled', true)) {
            ReconcilePaymentsBatchJob::dispatch();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractTransactions(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return array_values(array_filter($data['data'], 'is_array'));
        }

        if (array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        return [];
    }
}
