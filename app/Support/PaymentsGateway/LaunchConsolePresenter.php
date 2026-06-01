<?php

namespace App\Support\PaymentsGateway;

use App\Services\PaymentsGateway\PaymentsGatewayClient;

class LaunchConsolePresenter
{
    public const SEVERITY_PASS = 'pass';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_BLOCKED = 'blocked';

    /**
     * @param  array{
     *     paybill_account_uuid?: string|null,
     *     environment?: string|null,
     *     validation_run_uuid?: string|null
     * }  $filters
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
                __('Launch console could not load because payments.pradytecai.com is unavailable.')
            );
        }

        $missingApis = [];
        $paybillUuid = filled($filters['paybill_account_uuid'] ?? null)
            ? (string) $filters['paybill_account_uuid']
            : null;
        $environment = filled($filters['environment'] ?? null)
            ? (string) $filters['environment']
            : 'production';

        $operationalStatus = $this->buildOperationalStatus($client, $missingApis);
        $paybillReadiness = $paybillUuid !== null
            ? $this->buildPaybillReadiness($client, $paybillUuid, $environment, $missingApis)
            : null;
        $validationHistory = $this->buildValidationHistory($client, array_filter([
            'paybill_account_uuid' => $paybillUuid,
            'per_page' => 8,
        ]), $missingApis);
        $validationRun = filled($filters['validation_run_uuid'] ?? null)
            ? $this->buildValidationRunDetail($client, (string) $filters['validation_run_uuid'], $missingApis)
            : null;
        $escalation = $this->buildEscalationWorkspace($client, $missingApis);

        return [
            'gatewayUnavailable' => false,
            'gatewayMessage' => null,
            'missingApis' => array_values(array_unique($missingApis)),
            'filters' => [
                'paybill_account_uuid' => $paybillUuid ?? '',
                'environment' => $environment,
                'validation_run_uuid' => (string) ($filters['validation_run_uuid'] ?? ''),
            ],
            'isLiveEnvironment' => strtolower($environment) === 'production',
            'operationalStatus' => $operationalStatus,
            'paybillReadiness' => $paybillReadiness,
            'validationHistory' => $validationHistory,
            'validationRun' => $validationRun,
            'escalation' => $escalation,
            'environments' => GatewayFormOptions::paymentEnvironments(),
        ];
    }

    /**
     * @param  list<string>  $missingApis
     * @return array<string, mixed>
     */
    public function buildOperationalStatus(PaymentsGatewayClient $client, array &$missingApis = []): array
    {
        $readinessStatus = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getOperationsReadinessStatus(),
            'GET /api/v1/operations/readiness/status',
            $missingApis
        );

        $queueOverview = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getQueueOperationsOverview(),
            'GET /api/v1/operations/queue/overview',
            $missingApis
        );

        $webhookSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getWebhookOperationsSummary(),
            'GET /api/v1/operations/webhooks/summary',
            $missingApis
        );

        $callbackSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getCallbackLogsOperationsSummary(),
            'GET /api/v1/operations/callback-logs/summary',
            $missingApis
        );

        $alertsSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->listTreasuryAlerts(['per_severity_limit' => 5]),
            'GET /api/v1/operations/treasury-alerts',
            $missingApis
        );

        $unmatched = $this->resolveListSection(
            $client,
            $client->listUnmatchedTransactions(['per_page' => 1]),
            'GET /api/v1/operations/reconciliation/unmatched',
            $missingApis
        );

        $workers = $this->resolveListSection(
            $client,
            $client->listQueueWorkers(['per_page' => 5]),
            'GET /api/v1/operations/queue/workers',
            $missingApis
        );

        $deadLetters = (int) ($queueOverview['dead_letters'] ?? $queueOverview['dead_letter_count'] ?? 0);
        $failedCallbacks = (int) ($callbackSummary['failed'] ?? $callbackSummary['failed_count'] ?? 0);
        $failedWebhooks = (int) ($webhookSummary['failed_deliveries'] ?? $webhookSummary['failed_delivery_count'] ?? 0);
        $criticalAlerts = (int) ($alertsSummary['counts_by_severity']['critical'] ?? 0);
        $blockedIssues = (int) ($readinessStatus['blocking_issue_count'] ?? $readinessStatus['blocked_count'] ?? 0);

        $workerHealth = collect($workers)->contains(
            fn (array $worker): bool => in_array(strtolower((string) ($worker['status'] ?? '')), ['offline', 'stale'], true)
        ) ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS;

        return [
            'gateway_reachable' => [
                'label' => __('Gateway reachable'),
                'value' => __('Reachable'),
                'severity' => self::SEVERITY_PASS,
            ],
            'production_readiness' => [
                'label' => __('Production readiness summary'),
                'value' => (string) ($readinessStatus['production_readiness_status'] ?? $readinessStatus['last_production_readiness_status'] ?? '—'),
                'severity' => $this->normalizeSeverity((string) ($readinessStatus['production_readiness_status'] ?? 'unknown')),
            ],
            'queue_health' => [
                'label' => __('Queue health'),
                'value' => (string) ($queueOverview['worker_status'] ?? $queueOverview['workers_status'] ?? '—'),
                'severity' => $this->normalizeSeverity((string) ($queueOverview['worker_status'] ?? 'unknown'), deadLetters: $deadLetters),
            ],
            'webhook_health' => [
                'label' => __('Webhook health'),
                'value' => (string) ($failedWebhooks > 0 ? __(':count failed deliveries', ['count' => $failedWebhooks]) : __('Healthy')),
                'severity' => $failedWebhooks > 0 ? self::SEVERITY_WARNING : self::SEVERITY_PASS,
            ],
            'dead_letters' => [
                'label' => __('Dead letters'),
                'value' => (string) $deadLetters,
                'severity' => $deadLetters > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
            ],
            'callback_failures' => [
                'label' => __('Callback failures'),
                'value' => (string) $failedCallbacks,
                'severity' => $failedCallbacks > 0 ? self::SEVERITY_WARNING : self::SEVERITY_PASS,
            ],
            'worker_heartbeat' => [
                'label' => __('Worker heartbeat health'),
                'value' => $workerHealth === self::SEVERITY_PASS ? __('Healthy') : __('Degraded'),
                'severity' => $workerHealth,
            ],
            'reconciliation_health' => [
                'label' => __('Reconciliation health'),
                'value' => count($unmatched) > 0
                    ? __(':count unmatched', ['count' => count($unmatched)])
                    : __('Clear'),
                'severity' => count($unmatched) > 0 ? self::SEVERITY_WARNING : self::SEVERITY_PASS,
            ],
            'treasury_alerts' => [
                'label' => __('Treasury alert severity'),
                'value' => $criticalAlerts > 0
                    ? __(':count critical', ['count' => $criticalAlerts])
                    : __('No critical alerts'),
                'severity' => $criticalAlerts > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
            ],
            'blocked_go_live' => [
                'label' => __('Blocked go-live issues'),
                'value' => (string) $blockedIssues,
                'severity' => $blockedIssues > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
            ],
            'overall_severity' => $this->worstSeverity([
                $this->normalizeSeverity((string) ($readinessStatus['production_readiness_status'] ?? 'unknown')),
                $deadLetters > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
                $criticalAlerts > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
                $blockedIssues > 0 ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
                $failedCallbacks > 0 ? self::SEVERITY_WARNING : self::SEVERITY_PASS,
            ]),
        ];
    }

    /**
     * @param  list<string>  $missingApis
     * @return array<string, mixed>|null
     */
    public function buildPaybillReadiness(
        PaymentsGatewayClient $client,
        string $paybillUuid,
        string $environment,
        array &$missingApis = [],
    ): ?array {
        $accountResponse = $client->getPaybillAccount($paybillUuid);

        if ($accountResponse['unavailable'] ?? false) {
            return null;
        }

        if (! ($accountResponse['ok'] ?? false)) {
            return [
                'account' => null,
                'checks' => [],
                'overall_severity' => self::SEVERITY_BLOCKED,
                'blockers' => [__('PayBill account could not be loaded from payments.pradytecai.com.')],
            ];
        }

        $account = $client->extractResource($accountResponse) ?? [];
        $callbackAssessment = CanonicalCallbackUrls::assessAccount($account);

        $readinessResponse = $client->getProductionReadiness([
            'paybill_account_uuid' => $paybillUuid,
            'test_oauth' => '1',
        ]);
        $readinessReport = ($readinessResponse['ok'] ?? false) && ! ($readinessResponse['unavailable'] ?? false)
            ? ($client->extractData($readinessResponse) ?? [])
            : [];

        if (($readinessResponse['status'] ?? 0) === 404) {
            $missingApis[] = 'GET /api/v1/operations/production-readiness';
        }

        $dryRunResponse = $client->getGoLiveDryRun($paybillUuid, ['strict' => '1']);
        $dryRunReport = ($dryRunResponse['ok'] ?? false) && ! ($dryRunResponse['unavailable'] ?? false)
            ? ($client->extractData($dryRunResponse) ?? [])
            : [];

        if (($dryRunResponse['status'] ?? 0) === 404) {
            $missingApis[] = 'GET /api/v1/operations/go-live-dry-run/{paybill_account_uuid}';
        }

        $checks = [
            $this->check(
                'callback_url_alignment',
                __('Callback URL alignment'),
                $callbackAssessment['needs_url_update'] ? self::SEVERITY_BLOCKED : self::SEVERITY_PASS,
                $callbackAssessment['needs_url_update']
                    ? __('PayBill callback URLs are not aligned to canonical /pay/* endpoints.')
                    : __('Callback URLs are aligned to canonical /pay/* endpoints.')
            ),
            $this->check(
                'canonical_pay_urls',
                __('Canonical /pay/* verification'),
                $callbackAssessment['overall_status'] === CanonicalCallbackUrls::STATUS_CANONICAL
                    ? self::SEVERITY_PASS
                    : ($callbackAssessment['overall_status'] === CanonicalCallbackUrls::STATUS_MISSING ? self::SEVERITY_WARNING : self::SEVERITY_BLOCKED),
                __('Overall callback URL health: :status', ['status' => str_replace('_', ' ', $callbackAssessment['overall_status'])])
            ),
            $this->check(
                'production_readiness',
                __('Production readiness result'),
                $this->normalizeSeverity((string) ($readinessReport['overall_status'] ?? 'unknown')),
                (string) ($readinessReport['overall_status'] ?? __('Not run'))
            ),
            $this->check(
                'go_live_dry_run',
                __('Go-live dry run result'),
                $this->normalizeDryRunSeverity($dryRunReport),
                (string) ($dryRunReport['overall_status'] ?? __('Not run'))
            ),
            $this->check(
                'webhook_endpoint',
                __('Webhook endpoint verification'),
                $this->normalizeSectionSeverity($readinessReport['webhooks'] ?? $readinessReport['treasury'] ?? []),
                __('Derived from gateway production readiness report.')
            ),
            $this->check(
                'oauth_verification',
                __('OAuth verification'),
                $this->normalizeSectionSeverity($readinessReport['daraja'] ?? []),
                (string) (($readinessReport['daraja']['message'] ?? null) ?: __('Derived from gateway Daraja readiness checks.'))
            ),
            $this->check(
                'queue_worker_health',
                __('Queue worker health'),
                $this->normalizeSectionSeverity($readinessReport['queue'] ?? []),
                (string) (($readinessReport['queue']['overall_status'] ?? null) ?: __('Derived from gateway queue checks.'))
            ),
            $this->check(
                'reconciliation_integrity',
                __('Reconciliation integrity'),
                $this->normalizeSectionSeverity($readinessReport['treasury'] ?? []),
                (string) (($readinessReport['treasury']['overall_status'] ?? null) ?: __('Derived from gateway treasury checks.'))
            ),
            $this->check(
                'payout_governance',
                __('Payout governance readiness'),
                $this->normalizeDryRunSeverity($dryRunReport, 'treasury'),
                __('Derived from go-live dry run treasury checks.')
            ),
            $this->check(
                'treasury_policy',
                __('Treasury policy readiness'),
                $this->normalizeSectionSeverity($readinessReport['treasury'] ?? []),
                __('Derived from gateway treasury policy checks.')
            ),
        ];

        $blockers = collect($checks)
            ->filter(fn (array $check): bool => $check['severity'] === self::SEVERITY_BLOCKED)
            ->pluck('message')
            ->filter()
            ->values()
            ->all();

        return [
            'account' => $account,
            'environment' => $environment,
            'checks' => $checks,
            'overall_severity' => $this->worstSeverity(collect($checks)->pluck('severity')->all()),
            'blockers' => $blockers,
            'production_readiness' => $readinessReport,
            'go_live_dry_run' => $dryRunReport,
            'callback_assessment' => $callbackAssessment,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $missingApis
     * @return array<string, mixed>
     */
    public function buildValidationHistory(PaymentsGatewayClient $client, array $filters, array &$missingApis = []): array
    {
        $response = $client->listValidationRuns($filters);

        if (($response['status'] ?? 0) === 404) {
            $missingApis[] = 'GET /api/v1/operations/validation-runs';

            return [
                'available' => false,
                'items' => [],
            ];
        }

        return [
            'available' => ($response['ok'] ?? false) && ! ($response['unavailable'] ?? false),
            'items' => ($response['ok'] ?? false) ? $client->extractItems($response) : [],
        ];
    }

    /**
     * @param  list<string>  $missingApis
     * @return array<string, mixed>|null
     */
    public function buildValidationRunDetail(
        PaymentsGatewayClient $client,
        string $validationRunUuid,
        array &$missingApis = [],
    ): ?array {
        $response = $client->getValidationRun($validationRunUuid);

        if (($response['status'] ?? 0) === 404) {
            $missingApis[] = 'GET /api/v1/operations/validation-runs/{uuid}';

            return null;
        }

        if (! ($response['ok'] ?? false) || ($response['unavailable'] ?? false)) {
            return null;
        }

        $run = $client->extractResource($response);

        return is_array($run) ? $this->normalizeValidationRun($run) : null;
    }

    /**
     * @param  list<string>  $missingApis
     * @return array<string, mixed>
     */
    public function buildEscalationWorkspace(PaymentsGatewayClient $client, array &$missingApis = []): array
    {
        $failedDeliveries = $client->extractItems($client->listWebhookDeliveries([
            'status' => 'failed',
            'per_page' => 5,
        ]));
        $deadLetters = $this->resolveListSection(
            $client,
            $client->listDeadLetters(['status' => 'pending', 'per_page' => 5]),
            'GET /api/v1/operations/queue/dead-letters',
            $missingApis
        );
        $unmatched = $this->resolveListSection(
            $client,
            $client->listUnmatchedTransactions(['per_page' => 5]),
            'GET /api/v1/operations/reconciliation/unmatched',
            $missingApis
        );
        $alertsSummary = $this->fetchOperationsData(
            $client,
            fn (): array => $client->listTreasuryAlerts(['per_severity_limit' => 5]),
            'GET /api/v1/operations/treasury-alerts',
            $missingApis
        );
        $failedCallbacks = $client->extractItems($client->listCallbackLogs([
            'processing_status' => 'failed',
            'per_page' => 5,
        ]));
        $queueOverview = $this->fetchOperationsData(
            $client,
            fn (): array => $client->getQueueOperationsOverview(),
            'GET /api/v1/operations/queue/overview',
            $missingApis
        );

        $alerts = $this->flattenTreasuryAlerts($client, $alertsSummary);

        return [
            'failed_webhooks' => $failedDeliveries,
            'dead_letters' => $deadLetters,
            'unmatched_reconciliation' => $unmatched,
            'treasury_alerts' => $alerts,
            'failed_callbacks' => $failedCallbacks,
            'queue_incidents' => [
                'dead_letters' => (int) ($queueOverview['dead_letters'] ?? 0),
                'stuck_jobs' => (int) ($queueOverview['stuck_jobs'] ?? 0),
                'failed_jobs' => (int) ($queueOverview['failed_jobs'] ?? 0),
            ],
            'blocked_deployment_issues' => (int) ($alertsSummary['blocking_issue_count'] ?? 0),
        ];
    }

    public function normalizeSeverity(string $status, int $deadLetters = 0): string
    {
        $status = strtolower(trim($status));

        if ($deadLetters > 0) {
            return self::SEVERITY_BLOCKED;
        }

        return match ($status) {
            'pass', 'success', 'active', 'healthy', 'ok', 'reachable' => self::SEVERITY_PASS,
            'warn', 'warning', 'pending', 'degraded' => self::SEVERITY_WARNING,
            'fail', 'failed', 'blocked', 'error', 'critical', 'offline', 'unreachable' => self::SEVERITY_BLOCKED,
            default => self::SEVERITY_WARNING,
        };
    }

    /**
     * @param  array<string, mixed>  $run
     * @return array<string, mixed>
     */
    public function normalizeValidationRun(array $run): array
    {
        $stages = collect($run['stages'] ?? $run['checklist_items'] ?? [])
            ->filter(fn ($stage) => is_array($stage))
            ->map(function (array $stage): array {
                $status = strtolower((string) ($stage['status'] ?? 'unknown'));

                return [
                    'key' => (string) ($stage['key'] ?? $stage['name'] ?? 'stage'),
                    'label' => (string) ($stage['label'] ?? $stage['name'] ?? __('Stage')),
                    'status' => $status,
                    'severity' => $this->normalizeSeverity($status),
                    'message' => $stage['message'] ?? null,
                    'evidence' => $stage['evidence'] ?? $stage['details'] ?? null,
                ];
            })
            ->values()
            ->all();

        return [
            'uuid' => (string) ($run['uuid'] ?? ''),
            'environment' => (string) ($run['environment'] ?? 'production'),
            'paybill_account_uuid' => (string) ($run['paybill_account_uuid'] ?? ''),
            'overall_status' => (string) ($run['overall_status'] ?? 'unknown'),
            'overall_severity' => $this->normalizeSeverity((string) ($run['overall_status'] ?? 'unknown')),
            'strict_mode' => (bool) ($run['strict_mode'] ?? false),
            'duration_ms' => (int) ($run['duration_ms'] ?? $run['duration_seconds'] ?? 0),
            'failed_stages' => collect($stages)->where('severity', self::SEVERITY_BLOCKED)->values()->all(),
            'warnings' => collect($stages)->where('severity', self::SEVERITY_WARNING)->values()->all(),
            'stages' => $stages,
            'reconciliation' => $run['reconciliation'] ?? null,
            'webhook_delivery' => $run['webhook_delivery'] ?? $run['webhook_state'] ?? null,
            'callback_ingestion' => $run['callback_ingestion'] ?? $run['callback_state'] ?? null,
            'recommendations' => $run['recommendations'] ?? $run['next_steps'] ?? [],
            'escalation_guidance' => $run['escalation_guidance'] ?? $run['escalation'] ?? null,
            'started_at' => $run['started_at'] ?? null,
            'completed_at' => $run['completed_at'] ?? null,
        ];
    }

    /**
     * @return array{key: string, label: string, severity: string, message: string|null}
     */
    protected function check(string $key, string $label, string $severity, ?string $message = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'severity' => $severity,
            'message' => $message,
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     */
    protected function normalizeSectionSeverity(array $section): string
    {
        return $this->normalizeSeverity((string) ($section['overall_status'] ?? 'unknown'));
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function normalizeDryRunSeverity(array $report, ?string $category = null): string
    {
        $status = (string) ($report['overall_status'] ?? 'unknown');

        if ($category !== null && is_array($report['checklist_items'] ?? null)) {
            $categoryItems = collect($report['checklist_items'])
                ->filter(fn ($item) => is_array($item) && (string) ($item['category'] ?? '') === $category);

            if ($categoryItems->contains(fn (array $item): bool => strtolower((string) ($item['status'] ?? '')) === 'fail')) {
                return self::SEVERITY_BLOCKED;
            }

            if ($categoryItems->contains(fn (array $item): bool => strtolower((string) ($item['status'] ?? '')) === 'warn')) {
                return self::SEVERITY_WARNING;
            }
        }

        return $this->normalizeSeverity($status);
    }

    /**
     * @param  list<string>  $severities
     */
    protected function worstSeverity(array $severities): string
    {
        if (in_array(self::SEVERITY_BLOCKED, $severities, true)) {
            return self::SEVERITY_BLOCKED;
        }

        if (in_array(self::SEVERITY_WARNING, $severities, true)) {
            return self::SEVERITY_WARNING;
        }

        return self::SEVERITY_PASS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function unavailablePayload(string $message): array
    {
        return [
            'gatewayUnavailable' => true,
            'gatewayMessage' => $message,
            'missingApis' => [],
            'filters' => [
                'paybill_account_uuid' => '',
                'environment' => 'production',
                'validation_run_uuid' => '',
            ],
            'isLiveEnvironment' => true,
            'operationalStatus' => [],
            'paybillReadiness' => null,
            'validationHistory' => ['available' => false, 'items' => []],
            'validationRun' => null,
            'escalation' => [
                'failed_webhooks' => [],
                'dead_letters' => [],
                'unmatched_reconciliation' => [],
                'treasury_alerts' => [],
                'failed_callbacks' => [],
                'queue_incidents' => ['dead_letters' => 0, 'stuck_jobs' => 0, 'failed_jobs' => 0],
                'blocked_deployment_issues' => 0,
            ],
            'environments' => GatewayFormOptions::paymentEnvironments(),
        ];
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

        if ($response['unavailable'] ?? false || ! ($response['ok'] ?? false)) {
            return [];
        }

        return $client->extractItems($response);
    }

    /**
     * @param  array<string, mixed>  $alertsSummary
     * @return list<array<string, mixed>>
     */
    protected function flattenTreasuryAlerts(PaymentsGatewayClient $client, array $alertsSummary): array
    {
        if (isset($alertsSummary['groups']) && is_array($alertsSummary['groups'])) {
            $items = [];

            foreach ($alertsSummary['groups'] as $group) {
                if (! is_array($group)) {
                    continue;
                }

                foreach ($group['alerts'] ?? $group['items'] ?? [] as $alert) {
                    if (is_array($alert)) {
                        $items[] = $alert;
                    }
                }
            }

            return $items;
        }

        return is_array($alertsSummary['items'] ?? null) ? $alertsSummary['items'] : [];
    }
}
