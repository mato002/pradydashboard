<?php

namespace App\Support\PaymentsGateway;

use App\Services\PaymentsGateway\PaymentsGatewayClient;

class OperationsConsolePresenter
{
    public function __construct(
        protected OperationsConsoleUxPresenter $uxPresenter,
    ) {}
    /**
     * @param  array{transaction_type?: string|null, transaction_status?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function build(PaymentsGatewayClient $client, array $filters = []): array
    {
        if (! $client->isConfigured()) {
            return $this->unavailablePayload(__('Payments Gateway admin token is not configured.'));
        }

        $health = $client->health('/api/v1/health');

        if ($health['unavailable'] ?? false) {
            return $this->unavailablePayload(
                __('Operations console could not load because payments.pradytecai.com is unavailable.')
            );
        }

        $missingApis = [];

        $transactionSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getTransactionOperationsSummary(),
            'GET /api/v1/operations/transactions/summary',
            $missingApis
        );

        $transactionListFilters = array_filter([
            'per_page' => 8,
            'transaction_type' => $filters['transaction_type'] ?? null,
            'status' => $filters['transaction_status'] ?? null,
        ], fn (mixed $value) => filled($value));

        $recentTransactions = $client->extractItems($client->listTransactions($transactionListFilters));
        $statusCounts = $this->normalizeStatusCounts(
            $transactionSummary['status_counts'] ?? null,
            $recentTransactions,
            ['pending', 'processing', 'success', 'failed', 'timeout']
        );

        $callbackSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getCallbackLogsOperationsSummary(),
            'GET /api/v1/operations/callback-logs/summary',
            $missingApis
        );

        $recentCallbacks = $client->extractItems($client->listCallbackLogs(['per_page' => 6]));
        $failedCallbacks = $client->extractItems($client->listCallbackLogs([
            'processing_status' => 'failed',
            'per_page' => 5,
        ]));
        $duplicateCallbacks = $client->extractItems($client->listCallbackLogs([
            'processing_status' => 'duplicate',
            'per_page' => 5,
        ]));

        $callbackCounts = [
            'failed' => (int) ($callbackSummary['failed'] ?? $callbackSummary['failed_count'] ?? count($failedCallbacks)),
            'duplicate' => (int) ($callbackSummary['duplicate'] ?? $callbackSummary['duplicate_count'] ?? count($duplicateCallbacks)),
            'unmatched' => (int) ($callbackSummary['unmatched'] ?? $callbackSummary['unmatched_count'] ?? 0),
            'malformed' => (int) ($callbackSummary['malformed'] ?? $callbackSummary['malformed_count'] ?? 0),
        ];

        $webhookSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getWebhookOperationsSummary(),
            'GET /api/v1/operations/webhooks/summary',
            $missingApis
        );

        $pendingWebhookEvents = $client->extractItems($client->listWebhookEvents([
            'status' => 'pending',
            'per_page' => 5,
        ]));
        $failedWebhookEvents = $client->extractItems($client->listWebhookEvents([
            'status' => 'failed',
            'per_page' => 5,
        ]));
        $failedWebhookDeliveries = $client->extractItems($client->listWebhookDeliveries([
            'status' => 'failed',
            'per_page' => 5,
        ]));

        $webhookCounts = [
            'pending_events' => (int) ($webhookSummary['pending_events'] ?? $webhookSummary['pending_event_count'] ?? count($pendingWebhookEvents)),
            'failed_events' => (int) ($webhookSummary['failed_events'] ?? $webhookSummary['failed_event_count'] ?? count($failedWebhookEvents)),
            'failed_deliveries' => (int) ($webhookSummary['failed_deliveries'] ?? $webhookSummary['failed_delivery_count'] ?? count($failedWebhookDeliveries)),
        ];

        $queueOverview = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getQueueOperationsOverview(),
            'GET /api/v1/operations/queue/overview',
            $missingApis
        );

        $reconciliationRunsResponse = $client->listReconciliationRuns(['per_page' => 5]);
        $reconciliationRuns = $this->resolveListSection(
            $client,
            $reconciliationRunsResponse,
            'GET /api/v1/operations/reconciliation/runs',
            $missingApis
        );

        $unmatchedResponse = $client->listUnmatchedTransactions(['per_page' => 5]);
        $unmatchedTransactions = $this->resolveListSection(
            $client,
            $unmatchedResponse,
            'GET /api/v1/operations/reconciliation/unmatched',
            $missingApis
        );

        $alertsSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->listTreasuryAlerts(['per_severity_limit' => 8]),
            'GET /api/v1/operations/treasury-alerts',
            $missingApis
        );

        $treasuryAlerts = $this->flattenTreasuryAlerts($client, $alertsSummary, $missingApis);

        $deadLetterItems = $this->resolveListSection(
            $client,
            $client->listDeadLetters(['per_page' => 5, 'status' => 'pending']),
            'GET /api/v1/operations/queue/dead-letters',
            $missingApis
        );

        $workerItems = $this->resolveListSection(
            $client,
            $client->listQueueWorkers(['per_page' => 10]),
            'GET /api/v1/operations/queue/workers',
            $missingApis
        );

        $readinessStatus = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getOperationsReadinessStatus(),
            'GET /api/v1/operations/readiness/status',
            $missingApis
        );

        return $this->uxPresenter->enrich([
            'gatewayUnavailable' => false,
            'gatewayMessage' => null,
            'missingApis' => array_values(array_unique($missingApis)),
            'filters' => [
                'transaction_type' => (string) ($filters['transaction_type'] ?? ''),
                'transaction_status' => (string) ($filters['transaction_status'] ?? ''),
            ],
            'transactions' => [
                'status_counts' => $statusCounts,
                'recent' => $recentTransactions,
            ],
            'callbacks' => [
                'counts' => $callbackCounts,
                'recent' => $recentCallbacks,
                'failed' => $failedCallbacks,
            ],
            'webhooks' => [
                'counts' => $webhookCounts,
                'pending_events' => $pendingWebhookEvents,
                'failed_events' => $failedWebhookEvents,
                'failed_deliveries' => $failedWebhookDeliveries,
            ],
            'queue' => [
                'overview' => $queueOverview,
                'worker_status' => (string) ($queueOverview['worker_status'] ?? $queueOverview['workers_status'] ?? '—'),
                'dead_letters' => (int) ($queueOverview['dead_letters'] ?? $queueOverview['dead_letter_count'] ?? 0),
                'stuck_jobs' => (int) ($queueOverview['stuck_jobs'] ?? $queueOverview['stuck_job_count'] ?? 0),
                'failed_jobs' => (int) ($queueOverview['failed_jobs'] ?? $queueOverview['failed_job_count'] ?? 0),
                'dead_letter_items' => $deadLetterItems,
                'worker_items' => $workerItems,
            ],
            'reconciliation' => [
                'runs' => $reconciliationRuns,
                'unmatched' => $unmatchedTransactions,
                'variance_count' => (int) ($reconciliationRuns[0]['variance_count'] ?? $reconciliationRuns[0]['variances'] ?? 0),
                'settlement_status' => (string) ($reconciliationRuns[0]['settlement_status'] ?? '—'),
            ],
            'alerts' => [
                'items' => $treasuryAlerts,
                'critical' => collect($treasuryAlerts)->where('severity', 'critical')->count(),
                'high_risk' => collect($treasuryAlerts)->whereIn('severity', ['high', 'high_risk'])->count(),
                'unresolved_fraud' => collect($treasuryAlerts)->filter(
                    fn (array $alert): bool => ($alert['category'] ?? null) === 'fraud'
                        && ! in_array((string) ($alert['status'] ?? ''), ['resolved', 'acknowledged'], true)
                )->count(),
            ],
            'readiness' => [
                'last_production_readiness_status' => (string) ($readinessStatus['production_readiness_status'] ?? $readinessStatus['last_production_readiness_status'] ?? '—'),
                'last_go_live_dry_run_status' => (string) ($readinessStatus['go_live_dry_run_status'] ?? $readinessStatus['last_go_live_dry_run_status'] ?? '—'),
                'last_run_at' => $readinessStatus['last_run_at'] ?? $readinessStatus['generated_at'] ?? null,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function unavailablePayload(string $message): array
    {
        return $this->uxPresenter->enrich([
            'gatewayUnavailable' => true,
            'gatewayMessage' => $message,
            'missingApis' => [],
            'filters' => ['transaction_type' => '', 'transaction_status' => ''],
            'transactions' => ['status_counts' => [], 'recent' => []],
            'callbacks' => ['counts' => [], 'recent' => [], 'failed' => []],
            'webhooks' => ['counts' => [], 'pending_events' => [], 'failed_events' => [], 'failed_deliveries' => []],
            'queue' => ['overview' => [], 'worker_status' => '—', 'dead_letters' => 0, 'stuck_jobs' => 0, 'failed_jobs' => 0, 'dead_letter_items' => [], 'worker_items' => []],
            'reconciliation' => ['runs' => [], 'unmatched' => [], 'variance_count' => 0, 'settlement_status' => '—'],
            'alerts' => ['items' => [], 'critical' => 0, 'high_risk' => 0, 'unresolved_fraud' => 0],
            'readiness' => [
                'last_production_readiness_status' => '—',
                'last_go_live_dry_run_status' => '—',
                'last_run_at' => null,
            ],
        ]);
    }

    /**
     * @param  callable(): array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $fetch
     * @param  list<string>  $missingApis
     * @return array<string, mixed>
     */
    protected function fetchOperationsData(
        PaymentsGatewayClient $client,
        callable $fetch,
        string $endpoint,
        array &$missingApis,
    ): array {
        $response = $fetch();

        if (($response['status'] ?? 0) === 404) {
            $missingApis[] = $endpoint;

            return [];
        }

        if ($response['unavailable'] ?? false) {
            return [];
        }

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        return $client->extractData($response) ?? [];
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $response
     * @param  list<string>  $missingApis
     * @return list<array<string, mixed>>
     */
    protected function resolveListSection(
        PaymentsGatewayClient $client,
        array $response,
        string $endpoint,
        array &$missingApis,
    ): array {
        if (($response['status'] ?? 0) === 404) {
            $missingApis[] = $endpoint;

            return [];
        }

        if ($response['unavailable'] ?? false) {
            return [];
        }

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        return $client->extractItems($response);
    }

    /**
     * @param  array<string, int>|null  $summaryCounts
     * @param  list<array<string, mixed>>  $recentItems
     * @param  list<string>  $expectedStatuses
     * @return array<string, int>
     */
    protected function normalizeStatusCounts(?array $summaryCounts, array $recentItems, array $expectedStatuses): array
    {
        if (is_array($summaryCounts) && $summaryCounts !== []) {
            $normalized = [];

            foreach ($expectedStatuses as $status) {
                $normalized[$status] = (int) ($summaryCounts[$status] ?? $summaryCounts[$status.'_count'] ?? 0);
            }

            return $normalized;
        }

        $counts = array_fill_keys($expectedStatuses, 0);

        foreach ($recentItems as $item) {
            $status = strtolower((string) ($item['status'] ?? ''));

            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  list<string>  $missingApis
     * @return list<array<string, mixed>>
     */
    protected function flattenTreasuryAlerts(
        PaymentsGatewayClient $client,
        array $summary,
        array &$missingApis,
    ): array {
        if (isset($summary['groups']) && is_array($summary['groups'])) {
            $alerts = [];

            foreach ($summary['groups'] as $items) {
                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $item) {
                    if (is_array($item)) {
                        $alerts[] = $item;
                    }
                }
            }

            if ($alerts !== []) {
                return $alerts;
            }
        }

        $fallback = $this->resolveListSection(
            $client,
            $client->listTreasuryAlertsDetailed(['per_page' => 8, 'status' => 'open']),
            'GET /api/v1/treasury/alerts',
            $missingApis
        );

        return $fallback;
    }
}
