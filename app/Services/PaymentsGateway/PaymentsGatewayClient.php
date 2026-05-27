<?php

namespace App\Services\PaymentsGateway;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentsGatewayClient
{
    /**
     * @param  array<string, mixed>  $query
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, query: $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function post(string $path, array $payload = []): array
    {
        return $this->request('post', $path, payload: $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function patch(string $path, array $payload = []): array
    {
        return $this->request('patch', $path, payload: $payload);
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function health(string $path = '/api/health'): array
    {
        return $this->get($path);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function listTenants(array $query = []): array
    {
        return $this->get('/api/v1/tenants', $query);
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function getTenant(string $tenantUuid): array
    {
        return $this->get('/api/v1/tenants/'.$tenantUuid);
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function listPaymentProfiles(string $tenantUuid): array
    {
        return $this->get('/api/v1/tenants/'.$tenantUuid.'/payment-profiles');
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function getPaymentProfile(string $profileUuid): array
    {
        return $this->get('/api/v1/payment-profiles/'.$profileUuid);
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    public function listPaybillAccounts(string $profileUuid): array
    {
        return $this->get('/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function createTenant(array $payload): array
    {
        return $this->request('post', '/api/v1/tenants', payload: $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function updateTenant(string $tenantUuid, array $payload): array
    {
        return $this->request('patch', '/api/v1/tenants/'.$tenantUuid, payload: $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function suspendTenant(string $tenantUuid): array
    {
        return $this->post('/api/v1/tenants/'.$tenantUuid.'/suspend');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function activateTenant(string $tenantUuid): array
    {
        return $this->post('/api/v1/tenants/'.$tenantUuid.'/activate');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function createPaymentProfile(string $tenantUuid, array $payload): array
    {
        return $this->post('/api/v1/tenants/'.$tenantUuid.'/payment-profiles', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function updatePaymentProfile(string $profileUuid, array $payload): array
    {
        return $this->patch('/api/v1/payment-profiles/'.$profileUuid, $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function suspendPaymentProfile(string $profileUuid): array
    {
        return $this->post('/api/v1/payment-profiles/'.$profileUuid.'/suspend');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function activatePaymentProfile(string $profileUuid): array
    {
        return $this->post('/api/v1/payment-profiles/'.$profileUuid.'/activate');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function createPaybillAccount(string $profileUuid, array $payload): array
    {
        return $this->post('/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts', $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getPaybillAccount(string $accountUuid): array
    {
        return $this->get('/api/v1/paybill-accounts/'.$accountUuid);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function updatePaybillAccount(string $accountUuid, array $payload): array
    {
        return $this->patch('/api/v1/paybill-accounts/'.$accountUuid, $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function suspendPaybillAccount(string $accountUuid): array
    {
        return $this->post('/api/v1/paybill-accounts/'.$accountUuid.'/suspend');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function activatePaybillAccount(string $accountUuid): array
    {
        return $this->post('/api/v1/paybill-accounts/'.$accountUuid.'/activate');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function listGatewayApiKeys(string $profileUuid): array
    {
        return $this->get('/api/v1/payment-profiles/'.$profileUuid.'/api-keys');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function createGatewayApiKey(string $profileUuid, array $payload): array
    {
        return $this->post('/api/v1/payment-profiles/'.$profileUuid.'/api-keys', $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function revokeGatewayApiKey(string $keyUuid): array
    {
        return $this->post('/api/v1/api-keys/'.$keyUuid.'/revoke');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getOverviewStats(): array
    {
        return $this->get('/api/v1/stats/overview');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getTenantSummary(string $tenantUuid): array
    {
        return $this->get('/api/v1/tenants/'.$tenantUuid.'/summary');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getPaymentProfileSummary(string $profileUuid): array
    {
        return $this->get('/api/v1/payment-profiles/'.$profileUuid.'/summary');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function listWebhookEndpoints(string $profileUuid): array
    {
        return $this->get('/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getWebhookEndpoint(string $endpointUuid): array
    {
        return $this->get('/api/v1/webhook-endpoints/'.$endpointUuid);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function createWebhookEndpoint(string $profileUuid, array $payload): array
    {
        return $this->post('/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function updateWebhookEndpoint(string $endpointUuid, array $payload): array
    {
        return $this->patch('/api/v1/webhook-endpoints/'.$endpointUuid, $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function disableWebhookEndpoint(string $endpointUuid): array
    {
        return $this->post('/api/v1/webhook-endpoints/'.$endpointUuid.'/disable');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function enableWebhookEndpoint(string $endpointUuid): array
    {
        return $this->post('/api/v1/webhook-endpoints/'.$endpointUuid.'/enable');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function testWebhookEndpoint(string $endpointUuid): array
    {
        return $this->post('/api/v1/webhook-endpoints/'.$endpointUuid.'/test');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listTransactions(array $filters = []): array
    {
        return $this->get('/api/v1/transactions', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getTransaction(string $transactionUuid): array
    {
        return $this->get('/api/v1/transactions/'.$transactionUuid);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function searchTransactions(array $params): array
    {
        return $this->get('/api/v1/transactions/search', $params);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listCallbackLogs(array $filters = []): array
    {
        return $this->get('/api/v1/callback-logs', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getCallbackLog(string $callbackLogUuid): array
    {
        return $this->get('/api/v1/callback-logs/'.$callbackLogUuid);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listWebhookEvents(array $filters = []): array
    {
        return $this->get('/api/v1/webhook-events', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getWebhookEvent(string $eventUuid): array
    {
        return $this->get('/api/v1/webhook-events/'.$eventUuid);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function redispatchWebhookEvent(string $eventUuid, bool $force = false): array
    {
        return $this->post('/api/v1/webhook-events/'.$eventUuid.'/redispatch', [
            'force' => $force,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listWebhookDeliveries(array $filters = []): array
    {
        return $this->get('/api/v1/webhook-deliveries', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getWebhookDelivery(string $deliveryUuid): array
    {
        return $this->get('/api/v1/webhook-deliveries/'.$deliveryUuid);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function redispatchWebhookDelivery(string $deliveryUuid): array
    {
        return $this->post('/api/v1/webhook-deliveries/'.$deliveryUuid.'/redispatch');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function getProductionReadiness(array $params = []): array
    {
        return $this->get('/api/v1/operations/production-readiness', $params);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function getGoLiveDryRun(string $paybillAccountUuid, array $params = []): array
    {
        return $this->get('/api/v1/operations/go-live-dry-run/'.$paybillAccountUuid, $params);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getTransactionOperationsSummary(): array
    {
        return $this->get('/api/v1/operations/transactions/summary');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getCallbackLogsOperationsSummary(): array
    {
        return $this->get('/api/v1/operations/callback-logs/summary');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getWebhookOperationsSummary(): array
    {
        return $this->get('/api/v1/operations/webhooks/summary');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getQueueOperationsOverview(): array
    {
        return $this->get('/api/v1/operations/queue/overview');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listReconciliationRuns(array $filters = []): array
    {
        return $this->get('/api/v1/operations/reconciliation/runs', $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listUnmatchedTransactions(array $filters = []): array
    {
        return $this->get('/api/v1/operations/reconciliation/unmatched', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getUnmatchedTransaction(string $uuid): array
    {
        return $this->get('/api/v1/treasury/reconciliation/unmatched/'.$uuid);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listTreasuryAlerts(array $filters = []): array
    {
        return $this->get('/api/v1/operations/treasury-alerts', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getOperationsReadinessStatus(): array
    {
        return $this->get('/api/v1/operations/readiness/status');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listDeadLetters(array $filters = []): array
    {
        return $this->get('/api/v1/operations/queue/dead-letters', $filters);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getDeadLetter(string $uuid): array
    {
        return $this->get('/api/v1/operations/queue/dead-letters/'.$uuid);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function replayDeadLetter(string $uuid, bool $sync = false): array
    {
        return $this->post('/api/v1/operations/queue/dead-letters/'.$uuid.'/replay', [
            'sync' => $sync,
        ]);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function discardDeadLetter(string $uuid): array
    {
        return $this->post('/api/v1/operations/queue/dead-letters/'.$uuid.'/discard');
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function retryCallback(string $callbackLogUuid): array
    {
        return $this->post('/api/v1/operations/queue/callbacks/'.$callbackLogUuid.'/retry');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listQueueWorkers(array $filters = []): array
    {
        return $this->get('/api/v1/operations/queue/workers', $filters);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function acknowledgeTreasuryAlert(string $alertUuid, array $payload = []): array
    {
        return $this->post('/api/v1/treasury/alerts/'.$alertUuid.'/acknowledge', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function resolveTreasuryAlert(string $alertUuid, array $payload = []): array
    {
        return $this->post('/api/v1/treasury/alerts/'.$alertUuid.'/resolve', $payload);
    }

    /** @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool} */
    public function getTreasuryAlert(string $alertUuid): array
    {
        return $this->get('/api/v1/treasury/alerts/'.$alertUuid);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    public function listTreasuryAlertsDetailed(array $filters = []): array
    {
        return $this->get('/api/v1/treasury/alerts', $filters);
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $response
     * @return array{current_page: int, last_page: int, per_page: int, total: int, from: int|null, to: int|null}|null
     */
    public function extractPaginationMeta(array $response): ?array
    {
        $data = $this->extractData($response);

        if (! is_array($data) || ! isset($data['meta']) || ! is_array($data['meta'])) {
            return null;
        }

        $meta = $data['meta'];

        return [
            'current_page' => (int) ($meta['current_page'] ?? 1),
            'last_page' => (int) ($meta['last_page'] ?? 1),
            'per_page' => (int) ($meta['per_page'] ?? 15),
            'total' => (int) ($meta['total'] ?? 0),
            'from' => isset($meta['from']) ? (int) $meta['from'] : null,
            'to' => isset($meta['to']) ? (int) $meta['to'] : null,
        ];
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $response
     * @return list<array<string, mixed>>
     */
    public function extractItems(array $response): array
    {
        if (! ($response['ok'] ?? false)) {
            return [];
        }

        $data = $response['data'] ?? null;

        if (! is_array($data)) {
            return [];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return array_values($data['data']);
        }

        if (isset($data['uuid'])) {
            return [$data];
        }

        return array_is_list($data) ? $data : [];
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}  $response
     * @return array<string, mixed>|null
     */
    public function extractResource(array $response): ?array
    {
        if (! ($response['ok'] ?? false)) {
            return null;
        }

        $data = $response['data'] ?? null;

        if (! is_array($data)) {
            return null;
        }

        if (isset($data['data']) && is_array($data['data']) && array_is_list($data['data'])) {
            return $data['data'][0] ?? null;
        }

        return isset($data['uuid']) ? $data : null;
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}  $response
     * @return array<string, mixed>|null
     */
    public function extractData(array $response): ?array
    {
        if (! ($response['ok'] ?? false)) {
            return null;
        }

        $data = $response['data'] ?? null;

        return is_array($data) ? $data : null;
    }

    public function isConfigured(): bool
    {
        return filled(config('payment_gateway.admin_token'));
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    private function request(string $method, string $path, array $query = [], array $payload = []): array
    {
        if (! $this->isConfigured()) {
            return $this->failureResult(
                status: 0,
                error: __('Payments Gateway admin token is not configured.'),
                responseTimeMs: 0,
                unavailable: true,
            );
        }

        $started = microtime(true);
        $attempts = max(1, (int) config('payment_gateway.retry_attempts', 2));
        $lastError = null;
        $lastStatus = 0;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $pending = $this->httpClient();
                $response = match ($method) {
                    'post' => $pending->post($this->url($path), $payload),
                    'patch' => $pending->patch($this->url($path), $payload),
                    default => $pending->get($this->url($path), $query),
                };

                $responseTimeMs = (int) round((microtime(true) - $started) * 1000);

                return $this->normalizeResponse($response, $responseTimeMs, $path, $method);
            } catch (ConnectionException $e) {
                $lastError = $e->getMessage();
                $lastStatus = 0;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                $lastStatus = 0;
            }
        }

        $responseTimeMs = (int) round((microtime(true) - $started) * 1000);

        Log::warning('Payments Gateway request failed', [
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $lastStatus,
            'error' => $lastError,
            'response_time_ms' => $responseTimeMs,
        ]);

        return $this->failureResult(
            status: $lastStatus,
            error: $lastError ?: __('Payments Gateway unavailable'),
            responseTimeMs: $responseTimeMs,
            unavailable: true,
        );
    }

    private function httpClient(): PendingRequest
    {
        $timeout = max(5, (int) config('payment_gateway.timeout', 30));

        return Http::timeout($timeout)
            ->connectTimeout(min(10, $timeout))
            ->withOptions(['allow_redirects' => true, 'http_errors' => false])
            ->acceptJson()
            ->withToken((string) config('payment_gateway.admin_token'));
    }

    private function url(string $path): string
    {
        return config('payment_gateway.base_url').'/'.ltrim($path, '/');
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, response_time_ms: int, unavailable: bool}
     */
    private function normalizeResponse(Response $response, int $responseTimeMs, string $path, string $method): array
    {
        $body = $response->json();
        $successful = $response->successful() && (is_array($body) ? ($body['success'] ?? true) : true);

        if (! $successful) {
            $message = is_array($body) ? ($body['message'] ?? null) : null;

            Log::warning('Payments Gateway returned an error response', [
                'method' => strtoupper($method),
                'path' => $path,
                'status' => $response->status(),
                'message' => $message,
                'response_time_ms' => $responseTimeMs,
            ]);

            return $this->failureResult(
                status: $response->status(),
                error: $message ?: __('Payments Gateway request failed with HTTP :status.', ['status' => $response->status()]),
                responseTimeMs: $responseTimeMs,
                unavailable: $response->status() >= 500 || $response->status() === 0,
                data: is_array($body) ? ($body['data'] ?? null) : null,
                message: $message,
                errors: is_array($body) ? ($body['errors'] ?? null) : null,
            );
        }

        return [
            'ok' => true,
            'status' => $response->status(),
            'data' => is_array($body) ? ($body['data'] ?? null) : null,
            'message' => is_array($body) ? ($body['message'] ?? null) : null,
            'error' => null,
            'errors' => null,
            'response_time_ms' => $responseTimeMs,
            'unavailable' => false,
        ];
    }

    /**
     * @return array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}
     */
    private function failureResult(
        int $status,
        string $error,
        int $responseTimeMs,
        bool $unavailable,
        mixed $data = null,
        ?string $message = null,
        mixed $errors = null,
    ): array {
        return [
            'ok' => false,
            'status' => $status,
            'data' => $data,
            'message' => $message,
            'error' => $error,
            'errors' => $errors,
            'response_time_ms' => $responseTimeMs,
            'unavailable' => $unavailable,
        ];
    }
}
