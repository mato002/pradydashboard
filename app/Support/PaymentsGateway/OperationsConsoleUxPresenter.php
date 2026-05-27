<?php

namespace App\Support\PaymentsGateway;

use Illuminate\Support\Carbon;

class OperationsConsoleUxPresenter
{
    public function __construct(
        protected OperationsTenantDirectory $tenantDirectory,
    ) {}

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, mixed>
     */
    public function enrich(array $console): array
    {
        if ($console['gatewayUnavailable'] ?? false) {
            return array_merge($console, $this->emptyUxPayload());
        }

        $healthBanner = $this->buildHealthBanner($console);
        $incidents = $this->buildIncidents($console);
        $activityStream = $this->buildActivityStream($console);
        $workers = $this->buildWorkers($console);
        $reconciliationUrgency = $this->buildReconciliationUrgency($console);

        $quickActions = $this->buildQuickActions($console);
        $bulkActions = $this->buildBulkActions($quickActions);

        return array_merge($console, [
            'healthBanner' => $healthBanner,
            'incidents' => $this->enrichIncidentsForBulk($incidents, $quickActions),
            'activityStream' => $activityStream,
            'workers' => $workers,
            'reconciliationUrgency' => $reconciliationUrgency,
            'quickActions' => $quickActions,
            'bulkActions' => $bulkActions,
            'operationalTone' => fn (string $state): string => match ($state) {
                'green' => 'success',
                'yellow' => 'warning',
                'red' => 'danger',
                default => 'neutral',
            },
            'incidentSeverityTone' => fn (string $severity): string => match ($severity) {
                'critical' => 'danger',
                'high' => 'danger',
                'medium' => 'warning',
                'low' => 'neutral',
                default => 'neutral',
            },
            'ageLabel' => fn (?string $timestamp): string => $this->ageLabel($timestamp),
        ]);
    }

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, mixed>
     */
    protected function buildHealthBanner(array $console): array
    {
        $readiness = (string) ($console['readiness']['last_production_readiness_status'] ?? 'unknown');
        $queueStatus = (string) ($console['queue']['worker_status'] ?? 'unknown');
        $alertCount = (int) ($console['alerts']['critical'] ?? 0)
            + (int) ($console['alerts']['high_risk'] ?? 0);
        $failedWebhooks = (int) ($console['webhooks']['counts']['failed_deliveries'] ?? 0)
            + (int) ($console['webhooks']['counts']['failed_events'] ?? 0);
        $deadLetters = (int) ($console['queue']['dead_letters'] ?? 0);
        $unmatched = count($console['reconciliation']['unmatched'] ?? []);

        $overallState = $this->worstState([
            $this->stateFromReadiness($readiness),
            $this->stateFromQueue($queueStatus, $deadLetters),
            $alertCount > 0 ? 'red' : 'green',
            $failedWebhooks > 0 ? 'yellow' : 'green',
            $unmatched > 0 ? 'yellow' : 'green',
        ]);

        return [
            'overall' => [
                'state' => $overallState,
                'label' => __('Overall gateway health'),
                'value' => ucfirst($readiness !== '—' ? $readiness : __('Unknown')),
            ],
            'queue' => [
                'state' => $this->stateFromQueue($queueStatus, $deadLetters),
                'label' => __('Queue health'),
                'value' => ucfirst($queueStatus),
            ],
            'treasury_alerts' => [
                'state' => $alertCount > 0 ? 'red' : 'green',
                'label' => __('Treasury alerts'),
                'value' => (string) $alertCount,
            ],
            'failed_webhooks' => [
                'state' => $failedWebhooks > 0 ? 'yellow' : 'green',
                'label' => __('Failed webhooks'),
                'value' => (string) $failedWebhooks,
            ],
            'dead_letters' => [
                'state' => $deadLetters > 0 ? 'red' : 'green',
                'label' => __('Dead letters'),
                'value' => (string) $deadLetters,
            ],
            'unmatched_reconciliation' => [
                'state' => $unmatched > 0 ? 'yellow' : 'green',
                'label' => __('Unresolved reconciliation'),
                'value' => (string) $unmatched,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, mixed>
     */
    protected function buildIncidents(array $console): array
    {
        $failedWebhookItems = $this->tenantDirectory->enrichMany(array_merge(
            $console['webhooks']['failed_deliveries'] ?? [],
            $console['webhooks']['failed_events'] ?? [],
        ));

        return [
            'failed_webhooks' => $this->incidentPanel(
                title: __('Failed webhooks'),
                emptyMessage: __('No failed webhook deliveries'),
                items: $this->mapIncidentItems($failedWebhookItems, 'high', 'failed_at', 'created_at'),
                action: 'redispatch_webhook',
            ),
            'dead_letters' => $this->incidentPanel(
                title: __('Dead letters'),
                emptyMessage: __('No queue dead letters'),
                items: $this->mapIncidentItems(
                    $this->tenantDirectory->enrichMany($console['queue']['dead_letter_items'] ?? []),
                    'critical',
                    'created_at',
                    'failed_at',
                ),
                action: 'replay_dead_letter',
            ),
            'failed_callbacks' => $this->incidentPanel(
                title: __('Failed callbacks'),
                emptyMessage: __('No failed callback logs'),
                items: $this->mapIncidentItems(
                    $this->tenantDirectory->enrichMany($console['callbacks']['failed'] ?? []),
                    'high',
                    'received_at',
                    'processed_at',
                ),
                action: 'retry_callback',
            ),
            'unmatched_transactions' => $this->incidentPanel(
                title: __('Unmatched transactions'),
                emptyMessage: __('No unmatched transactions'),
                items: $this->mapIncidentItems(
                    $this->tenantDirectory->enrichMany($console['reconciliation']['unmatched'] ?? []),
                    'high',
                    'created_at',
                    'detected_at',
                ),
                action: null,
            ),
            'critical_alerts' => $this->incidentPanel(
                title: __('Critical treasury alerts'),
                emptyMessage: __('No unresolved treasury alerts'),
                items: $this->mapIncidentItems(
                    $this->tenantDirectory->enrichMany(
                        collect($console['alerts']['items'] ?? [])
                            ->filter(fn (array $alert): bool => in_array((string) ($alert['severity'] ?? ''), ['critical', 'high', 'high_risk'], true))
                            ->values()
                            ->all()
                    ),
                    'critical',
                    'created_at',
                ),
                action: 'acknowledge_alert',
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{title: string, empty_message: string, count: int, items: list<array<string, mixed>>, action: string|null}
     */
    protected function incidentPanel(string $title, string $emptyMessage, array $items, ?string $action): array
    {
        return [
            'title' => $title,
            'empty_message' => $emptyMessage,
            'count' => count($items),
            'items' => $items,
            'action' => $action,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function mapIncidentItems(array $items, string $defaultSeverity, string ...$timestampFields): array
    {
        return collect($items)->map(function (array $item) use ($defaultSeverity, $timestampFields): array {
            $timestamp = null;

            foreach ($timestampFields as $field) {
                if (filled($item[$field] ?? null)) {
                    $timestamp = (string) $item[$field];
                    break;
                }
            }

            return [
                'uuid' => (string) ($item['uuid'] ?? $item['transaction_uuid'] ?? ''),
                'title' => (string) ($item['title'] ?? $item['event_type'] ?? $item['callback_type'] ?? $item['unmatched_reason'] ?? $item['source_type'] ?? __('Incident')),
                'description' => (string) ($item['description'] ?? $item['processing_error'] ?? $item['error_message'] ?? $item['reason'] ?? $item['variance_reason'] ?? ''),
                'severity' => (string) ($item['severity'] ?? $defaultSeverity),
                'type' => (string) ($item['type'] ?? $item['source_type'] ?? '—'),
                'queue' => (string) ($item['queue'] ?? $item['queue_name'] ?? '—'),
                'status' => (string) ($item['status'] ?? '—'),
                'timestamp' => $timestamp,
                'age' => $this->ageLabel($timestamp),
                'tenant_name' => (string) ($item['tenant_name'] ?? '—'),
                'payment_profile_label' => (string) ($item['payment_profile_label'] ?? '—'),
                'paybill_label' => (string) ($item['paybill_label'] ?? '—'),
                'webhook_endpoint' => (string) ($item['webhook_endpoint'] ?? '—'),
                'tenant_mapping_url' => $item['tenant_mapping_url'] ?? null,
                'detail_url' => $item['detail_url'] ?? null,
                'webhook_event_uuid' => (string) ($item['webhook_event_uuid'] ?? $item['uuid'] ?? ''),
                'delivery_uuid' => (string) ($item['uuid'] ?? ''),
            ];
        })->values()->all();
    }

    /**
     * @param  array<string, mixed>  $console
     * @return list<array<string, mixed>>
     */
    protected function buildActivityStream(array $console): array
    {
        $entries = [];

        foreach ($console['transactions']['recent'] ?? [] as $item) {
            $type = strtolower((string) ($item['transaction_type'] ?? ''));

            if (! in_array($type, ['stk', 'b2c', 'c2b'], true)) {
                continue;
            }

            $entries[] = [
                'type' => $type,
                'title' => strtoupper($type).' · '.ucfirst((string) ($item['status'] ?? 'unknown')),
                'subtitle' => ($item['currency'] ?? 'KES').' '.number_format((float) ($item['amount'] ?? 0), 2),
                'timestamp' => $item['created_at'] ?? null,
                'severity' => in_array((string) ($item['status'] ?? ''), ['failed', 'timeout'], true) ? 'high' : 'low',
                'url' => filled($item['uuid'] ?? null)
                    ? route('settings.payments-gateway.transactions.show', $item['uuid'])
                    : null,
            ];
        }

        foreach ($console['callbacks']['recent'] ?? [] as $item) {
            $entries[] = [
                'type' => 'callback',
                'title' => __('Callback').' · '.strtoupper((string) ($item['callback_type'] ?? '—')),
                'subtitle' => ucfirst((string) ($item['processing_status'] ?? $item['status'] ?? 'unknown')),
                'timestamp' => $item['received_at'] ?? $item['created_at'] ?? null,
                'severity' => in_array((string) ($item['processing_status'] ?? $item['status'] ?? ''), ['failed'], true) ? 'high' : 'low',
                'url' => filled($item['uuid'] ?? null)
                    ? route('settings.payments-gateway.callback-logs.show', $item['uuid'])
                    : null,
            ];
        }

        foreach ($console['alerts']['items'] ?? [] as $item) {
            $entries[] = [
                'type' => 'alert',
                'title' => (string) ($item['title'] ?? __('Treasury alert')),
                'subtitle' => ucfirst((string) ($item['severity'] ?? 'unknown')),
                'timestamp' => $item['created_at'] ?? null,
                'severity' => (string) ($item['severity'] ?? 'medium'),
                'url' => null,
            ];
        }

        foreach ($console['webhooks']['failed_deliveries'] ?? [] as $item) {
            $entries[] = [
                'type' => 'webhook_failure',
                'title' => __('Webhook delivery failed'),
                'subtitle' => (string) ($item['target_url'] ?? $item['http_status'] ?? '—'),
                'timestamp' => $item['created_at'] ?? $item['failed_at'] ?? null,
                'severity' => 'high',
                'url' => filled($item['uuid'] ?? null)
                    ? route('settings.payments-gateway.webhook-deliveries.show', $item['uuid'])
                    : null,
            ];
        }

        return collect($entries)
            ->filter(fn (array $entry): bool => filled($entry['timestamp'] ?? null))
            ->sortByDesc(fn (array $entry): int => Carbon::parse((string) $entry['timestamp'])->timestamp)
            ->take(15)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, mixed>
     */
    protected function buildWorkers(array $console): array
    {
        $missing = $console['missingApis'] ?? [];
        $overview = $console['queue']['overview'] ?? [];
        $items = $console['queue']['worker_items'] ?? [];
        $apiAvailable = ! in_array('GET /api/v1/operations/queue/workers', $missing, true);

        $active = 0;
        $stale = 0;
        $offline = 0;

        $mapped = collect($items)->map(function (array $worker) use (&$active, &$stale, &$offline): array {
            $status = strtolower((string) ($worker['status'] ?? 'offline'));

            match ($status) {
                'active', 'healthy' => $active++,
                'stale', 'degraded' => $stale++,
                default => $offline++,
            };

            $lastSeenAt = $worker['last_seen_at'] ?? $worker['last_heartbeat_at'] ?? null;
            $queueNames = $worker['queue_names'] ?? [];

            if (is_string($queueNames)) {
                $queueNames = [$queueNames];
            }

            return [
                'name' => (string) ($worker['worker_name'] ?? __('Worker')),
                'status' => $status,
                'badge' => match ($status) {
                    'active', 'healthy' => 'success',
                    'stale', 'degraded' => 'warning',
                    default => 'danger',
                },
                'queue_names' => filled($queueNames) ? implode(', ', $queueNames) : '—',
                'last_seen_at' => $lastSeenAt,
                'last_seen_label' => is_string($lastSeenAt) ? $lastSeenAt : '—',
                'age_seconds' => isset($worker['age_seconds']) ? (string) $worker['age_seconds'] : '—',
                'heartbeat_age' => $this->ageLabel(is_string($lastSeenAt) ? $lastSeenAt : null),
            ];
        })->values()->all();

        if (! $apiAvailable || $mapped === []) {
            $active = (int) ($overview['active_workers'] ?? $overview['workers']['active'] ?? $active);
            $stale = (int) ($overview['stale_workers'] ?? $overview['workers']['stale_or_offline'] ?? $stale);
            $offline = max(0, (int) ($overview['offline_workers'] ?? $overview['workers']['offline'] ?? $offline));
        }

        return [
            'api_available' => $apiAvailable,
            'active' => $active,
            'stale' => $stale,
            'offline' => $offline,
            'items' => $mapped,
        ];
    }

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, mixed>
     */
    protected function buildReconciliationUrgency(array $console): array
    {
        $unmatchedCount = count($console['reconciliation']['unmatched'] ?? []);
        $varianceCount = (int) ($console['reconciliation']['variance_count'] ?? 0);
        $settlementStatus = (string) ($console['reconciliation']['settlement_status'] ?? '—');
        $largeVariances = collect($console['reconciliation']['unmatched'] ?? [])
            ->filter(fn (array $item): bool => abs((float) ($item['variance_amount'] ?? $item['amount'] ?? 0)) >= 1000)
            ->count();

        $state = $this->worstState([
            $unmatchedCount > 0 ? 'yellow' : 'green',
            $largeVariances > 0 ? 'red' : 'green',
            in_array(strtolower($settlementStatus), ['variance', 'failed', 'blocked'], true) ? 'red' : 'green',
        ]);

        return [
            'state' => $state,
            'unmatched_count' => $unmatchedCount,
            'large_variances' => max($largeVariances, $varianceCount > 0 ? 1 : 0),
            'variance_count' => $varianceCount,
            'settlement_status' => $settlementStatus,
            'settlement_variance_unresolved' => in_array(strtolower($settlementStatus), ['variance', 'open', 'failed'], true),
        ];
    }

    /**
     * @param  array<string, mixed>  $console
     * @return array<string, array{available: bool, label: string, endpoint: string|null, warning: string|null}>
     */
    protected function buildQuickActions(array $console): array
    {
        $missing = $console['missingApis'] ?? [];
        $deadLettersAvailable = ! in_array('GET /api/v1/operations/queue/dead-letters', $missing, true);
        $treasuryAlertsAvailable = ! in_array('GET /api/v1/operations/treasury-alerts', $missing, true);

        return [
            'redispatch_webhook' => [
                'available' => true,
                'label' => __('Redispatch webhook'),
                'endpoint' => 'POST /api/v1/webhook-deliveries/{uuid}/redispatch',
                'warning' => __('Queues another delivery attempt to the tenant webhook endpoint.'),
            ],
            'replay_dead_letter' => [
                'available' => $deadLettersAvailable,
                'label' => __('Replay dead letter'),
                'endpoint' => 'POST /api/v1/operations/queue/dead-letters/{uuid}/replay',
                'warning' => __('Replays the underlying queue job. Confirm the root cause is resolved first.'),
            ],
            'discard_dead_letter' => [
                'available' => $deadLettersAvailable,
                'label' => __('Discard dead letter'),
                'endpoint' => 'POST /api/v1/operations/queue/dead-letters/{uuid}/discard',
                'warning' => __('Discarding a dead letter marks it as handled and it will not be replayed automatically.'),
            ],
            'retry_callback' => [
                'available' => true,
                'label' => __('Retry callback'),
                'endpoint' => 'POST /api/v1/operations/queue/callbacks/{uuid}/retry',
                'warning' => __('Reprocesses the callback on payments.pradytecai.com.'),
            ],
            'acknowledge_alert' => [
                'available' => $treasuryAlertsAvailable,
                'label' => __('Acknowledge alert'),
                'endpoint' => 'POST /api/v1/treasury/alerts/{uuid}/acknowledge',
                'warning' => __('Marks the alert as acknowledged without resolving the underlying issue.'),
            ],
            'resolve_alert' => [
                'available' => $treasuryAlertsAvailable,
                'label' => __('Resolve alert'),
                'endpoint' => 'POST /api/v1/treasury/alerts/{uuid}/resolve',
                'warning' => __('Closes the alert after remediation is complete.'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $incidents
     * @param  array<string, array{available: bool, label: string, endpoint: string|null, warning: string|null}>  $quickActions
     * @return array<string, mixed>
     */
    protected function enrichIncidentsForBulk(array $incidents, array $quickActions): array
    {
        foreach ($incidents as $panelKey => &$panel) {
            $panel['items'] = collect($panel['items'] ?? [])->map(function (array $item) use ($panelKey, $quickActions): array {
                $bulkUuid = match ($panelKey) {
                    'failed_webhooks' => (string) ($item['delivery_uuid'] ?? ''),
                    default => (string) ($item['uuid'] ?? ''),
                };

                $selectable = match ($panelKey) {
                    'failed_webhooks' => filled($bulkUuid) && ($quickActions['redispatch_webhook']['available'] ?? false),
                    'dead_letters' => filled($bulkUuid) && ($quickActions['replay_dead_letter']['available'] ?? false),
                    'failed_callbacks' => filled($bulkUuid) && ($quickActions['retry_callback']['available'] ?? false),
                    'critical_alerts' => filled($bulkUuid) && (
                        ($quickActions['acknowledge_alert']['available'] ?? false)
                        || ($quickActions['resolve_alert']['available'] ?? false)
                    ),
                    default => false,
                };

                return array_merge($item, [
                    'bulk_uuid' => $bulkUuid,
                    'bulk_selectable' => $selectable,
                    'investigate_url' => $this->investigateUrl($panelKey, $item, $bulkUuid),
                ]);
            })->values()->all();
        }
        unset($panel);

        return $incidents;
    }

    protected function investigateUrl(string $panelKey, array $item, string $bulkUuid = ''): ?string
    {
        return match ($panelKey) {
            'dead_letters' => filled($item['uuid'] ?? null)
                ? route('settings.payments-gateway.operations-console.dead-letters.show', $item['uuid'])
                : null,
            'failed_callbacks' => filled($item['uuid'] ?? null)
                ? route('settings.payments-gateway.operations-console.callback-logs.show', $item['uuid'])
                : null,
            'failed_webhooks' => filled($item['event_type'] ?? null) && ! filled($item['http_status'] ?? $item['response_status'] ?? null)
                ? route('settings.payments-gateway.operations-console.webhook-events.show', $item['uuid'] ?? '')
                : (filled($item['uuid'] ?? null) || filled($bulkUuid)
                    ? route('settings.payments-gateway.operations-console.webhook-deliveries.show', $item['uuid'] ?? $bulkUuid)
                    : null),
            'critical_alerts' => filled($item['uuid'] ?? null)
                ? route('settings.payments-gateway.operations-console.treasury-alerts.show', $item['uuid'])
                : null,
            'unmatched_transactions' => filled($item['uuid'] ?? null)
                ? route('settings.payments-gateway.operations-console.unmatched-transactions.show', $item['uuid'])
                : null,
            default => null,
        };
    }

    /**
     * @param  array<string, array{available: bool, label: string, endpoint: string|null, warning: string|null}>  $quickActions
     * @return array<string, array{enabled: bool, actions: array<string, array{label: string, requires_confirm: bool, confirm: string|null, accepts_comments: bool}>}>
     */
    protected function buildBulkActions(array $quickActions): array
    {
        $alertActions = [];

        if ($quickActions['acknowledge_alert']['available'] ?? false) {
            $alertActions['alerts.acknowledge'] = [
                'label' => __('Acknowledge selected'),
                'requires_confirm' => false,
                'confirm' => null,
                'accepts_comments' => true,
            ];
        }

        if ($quickActions['resolve_alert']['available'] ?? false) {
            $alertActions['alerts.resolve'] = [
                'label' => __('Resolve selected'),
                'requires_confirm' => true,
                'confirm' => __('Resolve selected treasury alerts after remediation is complete?'),
                'accepts_comments' => true,
            ];
        }

        return [
            'failed_webhooks' => [
                'enabled' => (bool) ($quickActions['redispatch_webhook']['available'] ?? false),
                'actions' => [
                    'webhook_deliveries.redispatch' => [
                        'label' => __('Redispatch selected'),
                        'requires_confirm' => true,
                        'confirm' => __('Redispatch selected webhook deliveries? This queues another signed POST to each tenant endpoint.'),
                        'accepts_comments' => false,
                    ],
                ],
            ],
            'dead_letters' => [
                'enabled' => (bool) ($quickActions['replay_dead_letter']['available'] ?? false),
                'actions' => [
                    'dead_letters.replay' => [
                        'label' => __('Replay selected'),
                        'requires_confirm' => false,
                        'confirm' => null,
                        'accepts_comments' => false,
                    ],
                    'dead_letters.discard' => [
                        'label' => __('Discard selected'),
                        'requires_confirm' => true,
                        'confirm' => __('Discarding selected dead letters marks them as handled and they will not be replayed automatically.'),
                        'accepts_comments' => false,
                    ],
                ],
            ],
            'failed_callbacks' => [
                'enabled' => (bool) ($quickActions['retry_callback']['available'] ?? false),
                'actions' => [
                    'callbacks.retry' => [
                        'label' => __('Retry selected'),
                        'requires_confirm' => false,
                        'confirm' => null,
                        'accepts_comments' => false,
                    ],
                ],
            ],
            'critical_alerts' => [
                'enabled' => $alertActions !== [],
                'actions' => $alertActions,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyUxPayload(): array
    {
        $emptyPanel = fn (string $title, string $empty): array => [
            'title' => $title,
            'empty_message' => $empty,
            'count' => 0,
            'items' => [],
            'action' => null,
        ];

        return [
            'healthBanner' => [],
            'incidents' => [
                'failed_webhooks' => $emptyPanel(__('Failed webhooks'), __('No failed webhook deliveries')),
                'dead_letters' => $emptyPanel(__('Dead letters'), __('No queue dead letters')),
                'failed_callbacks' => $emptyPanel(__('Failed callbacks'), __('No failed callback logs')),
                'unmatched_transactions' => $emptyPanel(__('Unmatched transactions'), __('No unmatched transactions')),
                'critical_alerts' => $emptyPanel(__('Critical treasury alerts'), __('No unresolved treasury alerts')),
            ],
            'activityStream' => [],
            'workers' => ['api_available' => false, 'active' => 0, 'stale' => 0, 'offline' => 0, 'items' => []],
            'reconciliationUrgency' => [
                'state' => 'red',
                'unmatched_count' => 0,
                'large_variances' => 0,
                'variance_count' => 0,
                'settlement_status' => '—',
                'settlement_variance_unresolved' => false,
            ],
            'quickActions' => [],
            'bulkActions' => [],
            'operationalTone' => fn (string $state): string => 'neutral',
            'incidentSeverityTone' => fn (string $severity): string => 'neutral',
            'ageLabel' => fn (?string $timestamp): string => '—',
        ];
    }

    /**
     * @param  list<string>  $states
     */
    protected function worstState(array $states): string
    {
        if (in_array('red', $states, true)) {
            return 'red';
        }

        if (in_array('yellow', $states, true)) {
            return 'yellow';
        }

        return 'green';
    }

    protected function stateFromReadiness(string $status): string
    {
        return match (strtolower($status)) {
            'pass', 'ok', 'healthy' => 'green',
            'warn', 'warning' => 'yellow',
            'blocked', 'fail', 'failed' => 'red',
            default => 'yellow',
        };
    }

    protected function stateFromQueue(string $workerStatus, int $deadLetters): string
    {
        if ($deadLetters > 0) {
            return 'red';
        }

        return match (strtolower($workerStatus)) {
            'healthy', 'active', 'ok' => 'green',
            'degraded', 'stale' => 'yellow',
            'offline', 'failed' => 'red',
            default => 'yellow',
        };
    }

    protected function ageLabel(?string $timestamp): string
    {
        if (! filled($timestamp)) {
            return '—';
        }

        return Carbon::parse($timestamp)->diffForHumans();
    }
}
