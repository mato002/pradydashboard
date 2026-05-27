<?php

namespace App\Support\PaymentsGateway;

use App\Services\PaymentsGateway\PaymentsGatewayClient;

class IncidentInvestigationPresenter
{
    public function __construct(
        protected OperationsTenantDirectory $tenantDirectory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function presentDeadLetter(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getDeadLetter($uuid),
            uuid: $uuid,
            unavailableMessage: __('Dead letter could not be loaded from Payments Gateway.'),
            quickActions: ['replay' => true, 'discard' => true],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForDeadLetter($resource),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentCallbackLog(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getCallbackLog($uuid),
            uuid: $uuid,
            unavailableMessage: __('Callback log could not be loaded from Payments Gateway.'),
            quickActions: ['retry' => true],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForCallbackLog($resource),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentWebhookDelivery(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getWebhookDelivery($uuid),
            uuid: $uuid,
            unavailableMessage: __('Webhook delivery could not be loaded from Payments Gateway.'),
            quickActions: ['redispatch' => true],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForWebhookDelivery($resource),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentWebhookEvent(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getWebhookEvent($uuid),
            uuid: $uuid,
            unavailableMessage: __('Webhook event could not be loaded from Payments Gateway.'),
            quickActions: ['redispatch_event' => true],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForWebhookEvent($resource),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentTreasuryAlert(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getTreasuryAlert($uuid),
            uuid: $uuid,
            unavailableMessage: __('Treasury alert could not be loaded from Payments Gateway.'),
            apiUnavailableMessage: __('Alert detail API not available yet.'),
            quickActions: ['acknowledge' => true, 'resolve' => true],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForTreasuryAlert($resource),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function presentUnmatchedTransaction(PaymentsGatewayClient $client, string $uuid): array
    {
        return $this->presentDetail(
            client: $client,
            response: $client->getUnmatchedTransaction($uuid),
            uuid: $uuid,
            unavailableMessage: __('Unmatched transaction could not be loaded from Payments Gateway.'),
            apiUnavailableMessage: __('Unmatched transaction detail API not available yet.'),
            quickActions: [],
            legacyRelatedLinks: fn (array $resource): array => $this->legacyRelatedLinksForUnmatchedTransaction($resource),
        );
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $response
     * @param  callable(array<string, mixed>): list<array{label: string, url: string}>  $legacyRelatedLinks
     * @param  array<string, bool>  $quickActions
     * @return array<string, mixed>
     */
    protected function presentDetail(
        PaymentsGatewayClient $client,
        array $response,
        string $uuid,
        string $unavailableMessage,
        callable $legacyRelatedLinks,
        array $quickActions,
        ?string $apiUnavailableMessage = null,
    ): array {
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);
        $apiUnavailable = ($response['status'] ?? 0) === 404;
        $loaded = ! $gatewayUnavailable && ! $apiUnavailable && ($response['ok'] ?? false);

        $payload = $loaded ? ($client->extractResource($response) ?? []) : null;
        $investigation = is_array($payload) ? ($payload['investigation'] ?? null) : null;
        $resource = is_array($payload)
            ? $this->tenantDirectory->enrich(collect($payload)->except('investigation')->all())
            : null;

        $tenantImpact = $this->resolveTenantImpact($investigation, $resource);
        $riskLevel = (string) ($investigation['risk_level'] ?? 'medium');
        $relatedLinks = $resource !== null
            ? $this->mergeRelatedLinks($investigation, $legacyRelatedLinks($resource))
            : [];
        $relatedRecords = is_array($investigation['related_records'] ?? null)
            ? $investigation['related_records']
            : [];

        return [
            'uuid' => $uuid,
            'resource' => $resource,
            'investigation' => $investigation,
            'tenantImpact' => $tenantImpact,
            'riskLevel' => $riskLevel,
            'relatedLinks' => $relatedLinks,
            'relatedRecords' => $relatedRecords,
            'recommendedActions' => $this->buildRecommendedActions($investigation, $uuid, $quickActions, $riskLevel),
            'gatewayUnavailable' => $gatewayUnavailable,
            'apiUnavailable' => $apiUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? ((string) ($response['error'] ?? $response['message'] ?? __('Payments Gateway unavailable.')))
                : ($apiUnavailable ? ($apiUnavailableMessage ?? $unavailableMessage) : null),
            'unavailableMessage' => $unavailableMessage,
            'quickActions' => collect($quickActions)->map(fn (bool $enabled): bool => $enabled && $loaded)->all(),
            'formatTimestamp' => fn (?string $value): string => filled($value)
                ? \Illuminate\Support\Carbon::parse($value)->format('M j, Y H:i:s')
                : '—',
            'formatJson' => fn (mixed $value): ?string => $this->formatJson($value),
            'riskVariant' => fn (string $level): string => match (strtolower($level)) {
                'low' => 'success',
                'medium' => 'warning',
                'high', 'critical' => 'danger',
                default => 'neutral',
            },
        ];
    }

    /**
     * @param  array<string, mixed>|null  $investigation
     * @param  array<string, mixed>|null  $resource
     * @return array<string, mixed>
     */
    protected function resolveTenantImpact(?array $investigation, ?array $resource): array
    {
        $impact = is_array($investigation['tenant_impact'] ?? null)
            ? $investigation['tenant_impact']
            : [];

        $tenantUuid = (string) ($impact['tenant_uuid'] ?? $resource['tenant_uuid'] ?? '');
        $profileUuid = (string) ($impact['payment_profile_uuid'] ?? $resource['payment_profile_uuid'] ?? '');
        $accountUuid = (string) ($impact['paybill_account_uuid'] ?? $resource['paybill_account_uuid'] ?? '');

        $enriched = $this->tenantDirectory->enrich(array_filter([
            'tenant_uuid' => $tenantUuid ?: null,
            'payment_profile_uuid' => $profileUuid ?: null,
            'paybill_account_uuid' => $accountUuid ?: null,
        ]));

        return [
            'tenant_uuid' => $tenantUuid ?: '—',
            'payment_profile_uuid' => $profileUuid ?: '—',
            'paybill_account_uuid' => $accountUuid ?: '—',
            'tenant_name' => (string) ($enriched['tenant_name'] ?? '—'),
            'payment_profile_label' => (string) ($enriched['payment_profile_label'] ?? '—'),
            'paybill_label' => (string) ($enriched['paybill_label'] ?? '—'),
            'tenant_mapping_url' => $enriched['tenant_mapping_url'] ?? null,
            'scoped' => (bool) ($impact['scoped'] ?? filled($tenantUuid)),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $investigation
     * @param  list<array{label: string, url: string}>  $legacyLinks
     * @return list<array{label: string, url: string, type?: string, status?: string|null}>
     */
    protected function mergeRelatedLinks(?array $investigation, array $legacyLinks): array
    {
        $links = [];

        foreach ($investigation['related_records'] ?? [] as $record) {
            if (! is_array($record)) {
                continue;
            }

            $type = (string) ($record['type'] ?? '');
            $recordUuid = (string) ($record['uuid'] ?? '');
            $url = $this->urlForRecordType($type, $recordUuid);

            if ($url === null) {
                continue;
            }

            $links[] = [
                'label' => (string) ($record['label'] ?? str_replace('_', ' ', ucfirst($type))),
                'url' => $url,
                'type' => $type,
                'status' => $record['status'] ?? null,
            ];
        }

        foreach ($legacyLinks as $link) {
            $links[] = $link;
        }

        $seen = [];

        return array_values(array_filter($links, function (array $link) use (&$seen): bool {
            $key = ($link['url'] ?? '').':'.($link['label'] ?? '');

            if ($key === ':' || isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;

            return filled($link['url'] ?? null);
        }));
    }

    /**
     * @param  array<string, mixed>|null  $investigation
     * @param  array<string, bool>  $quickActions
     * @return list<array<string, mixed>>
     */
    protected function buildRecommendedActions(?array $investigation, string $uuid, array $quickActions, string $riskLevel): array
    {
        $codes = is_array($investigation['recommended_next_actions'] ?? null)
            ? $investigation['recommended_next_actions']
            : [];

        if ($codes === []) {
            return $this->fallbackRecommendedActions($quickActions, $uuid, $riskLevel);
        }

        $actions = [];

        foreach ($codes as $code) {
            if (! is_string($code)) {
                continue;
            }

            $definition = $this->actionDefinition($code, $uuid, $quickActions);

            if ($definition === null) {
                continue;
            }

            $definition = $this->enrichNavigationAction($definition, $code, $investigation);

            $actions[] = array_merge($definition, [
                'code' => $code,
                'risk_level' => $riskLevel,
            ]);
        }

        return $actions;
    }

    /**
     * @param  array<string, bool>  $quickActions
     * @return list<array<string, mixed>>
     */
    protected function fallbackRecommendedActions(array $quickActions, string $uuid, string $riskLevel): array
    {
        $codes = [];

        if ($quickActions['replay'] ?? false) {
            $codes[] = 'replay_dead_letter';
        }

        if ($quickActions['discard'] ?? false) {
            $codes[] = 'discard_dead_letter';
        }

        if ($quickActions['retry'] ?? false) {
            $codes[] = 'retry_callback';
        }

        if ($quickActions['redispatch'] ?? false) {
            $codes[] = 'redispatch_webhook_delivery';
        }

        if ($quickActions['redispatch_event'] ?? false) {
            $codes[] = 'redispatch_webhook_event';
        }

        if ($quickActions['acknowledge'] ?? false) {
            $codes[] = 'acknowledge_alert';
        }

        if ($quickActions['resolve'] ?? false) {
            $codes[] = 'resolve_alert';
        }

        return collect($codes)
            ->map(function (string $code) use ($uuid, $quickActions, $riskLevel): ?array {
                $definition = $this->actionDefinition($code, $uuid, $quickActions);

                if ($definition === null) {
                    return null;
                }

                return array_merge($definition, [
                    'code' => $code,
                    'risk_level' => $riskLevel,
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>|null  $investigation
     * @return array<string, mixed>
     */
    protected function enrichNavigationAction(array $definition, string $code, ?array $investigation): array
    {
        $typeMap = [
            'review_payment_transaction' => 'payment_transaction',
            'review_callback_log' => 'callback_log',
            'review_delivery_errors' => 'webhook_delivery',
            'review_delivery_history' => 'webhook_delivery',
        ];

        $recordType = $typeMap[$code] ?? null;

        if ($recordType === null) {
            return $definition;
        }

        $record = collect($investigation['related_records'] ?? [])
            ->first(fn (mixed $item): bool => is_array($item) && ($item['type'] ?? '') === $recordType);

        if (! is_array($record) || ! filled($record['uuid'] ?? null)) {
            return $definition;
        }

        $url = $this->urlForRecordType($recordType, (string) $record['uuid']);

        if ($url === null) {
            return $definition;
        }

        return array_merge($definition, [
            'available' => true,
            'action_type' => 'navigate',
            'action_url' => $url,
        ]);
    }

    /**
     * @param  array<string, bool>  $quickActions
     * @return array<string, mixed>|null
     */
    protected function actionDefinition(string $code, string $uuid, array $quickActions): ?array
    {
        $definitions = [
            'replay_dead_letter' => [
                'label' => __('Replay dead letter'),
                'reason' => __('Re-queue the failed job after the root cause is resolved.'),
                'available' => (bool) ($quickActions['replay'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.dead-letters.replay', $uuid),
                'confirm' => __('Replays the underlying queue job. Confirm the root cause is resolved first.'),
            ],
            'discard_dead_letter' => [
                'label' => __('Discard dead letter'),
                'reason' => __('Mark the dead letter handled when replay is not appropriate.'),
                'available' => (bool) ($quickActions['discard'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.dead-letters.discard', $uuid),
                'confirm' => __('Discarding a dead letter marks it as handled and it will not be replayed automatically.'),
            ],
            'retry_callback' => [
                'label' => __('Retry callback'),
                'reason' => __('Reprocess the callback on payments.pradytecai.com.'),
                'available' => (bool) ($quickActions['retry'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.callback-logs.retry', $uuid),
                'confirm' => __('Reprocesses the callback on payments.pradytecai.com.'),
            ],
            'redispatch_webhook_delivery' => [
                'label' => __('Redispatch webhook delivery'),
                'reason' => __('Queue another signed POST to the tenant webhook endpoint.'),
                'available' => (bool) ($quickActions['redispatch'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.webhook-deliveries.redispatch', $uuid),
                'confirm' => __('Redispatch this webhook delivery? This queues another signed POST to the tenant endpoint.'),
            ],
            'redispatch_webhook_event' => [
                'label' => __('Redispatch webhook event'),
                'reason' => __('Retry all deliveries for this webhook event.'),
                'available' => (bool) ($quickActions['redispatch_event'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.webhook-events.redispatch', $uuid),
                'confirm' => __('Redispatch this webhook event?'),
            ],
            'acknowledge_alert' => [
                'label' => __('Acknowledge alert'),
                'reason' => __('Signal ownership without closing the underlying issue.'),
                'available' => (bool) ($quickActions['acknowledge'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.treasury-alerts.acknowledge', $uuid),
                'confirm' => __('Marks the alert as acknowledged without resolving the underlying issue.'),
                'accepts_comments' => true,
            ],
            'resolve_alert' => [
                'label' => __('Resolve alert'),
                'reason' => __('Close the alert after remediation is complete.'),
                'available' => (bool) ($quickActions['resolve'] ?? false),
                'action_type' => 'post',
                'action_url' => route('settings.payments-gateway.operations-console.treasury-alerts.resolve', $uuid),
                'confirm' => __('Closes the alert after remediation is complete.'),
                'accepts_comments' => true,
            ],
            'review_payment_transaction' => [
                'label' => __('Review payment transaction'),
                'reason' => __('Inspect the linked payment transaction for variance or failure context.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_callback_log' => [
                'label' => __('Review callback log'),
                'reason' => __('Inspect callback processing details and payload evidence.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_source_record' => [
                'label' => __('Review source record'),
                'reason' => __('Open the upstream record that produced this dead letter.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_delivery_errors' => [
                'label' => __('Review delivery errors'),
                'reason' => __('Inspect failed webhook delivery attempts and tenant responses.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_tenant_endpoint' => [
                'label' => __('Review tenant endpoint'),
                'reason' => __('Validate tenant webhook endpoint configuration and reachability.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_suggested_matches' => [
                'label' => __('Review suggested matches'),
                'reason' => __('Compare reconciliation suggestions before resolving the unmatched item.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'resolve_unmatched_transaction' => [
                'label' => __('Resolve unmatched transaction'),
                'reason' => __('Apply a treasury reconciliation resolution on the gateway.'),
                'available' => false,
                'action_type' => 'none',
            ],
            'run_reconciliation' => [
                'label' => __('Run reconciliation'),
                'reason' => __('Refresh reconciliation runs to validate whether the variance persists.'),
                'available' => false,
                'action_type' => 'navigate',
                'action_url' => route('settings.payments-gateway.operations-console').'#reconciliation',
            ],
            'review_audit_trail' => [
                'label' => __('Review audit trail'),
                'reason' => __('Inspect gateway audit history for prior remediation attempts.'),
                'available' => false,
                'action_type' => 'none',
            ],
            'monitor_replay' => [
                'label' => __('Monitor replay'),
                'reason' => __('Wait for the in-flight dead letter replay to finish.'),
                'available' => false,
                'action_type' => 'none',
            ],
            'review_delivery_history' => [
                'label' => __('Review delivery history'),
                'reason' => __('Confirm prior delivery attempts before redispatching again.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
            'review_resolution_history' => [
                'label' => __('Review resolution history'),
                'reason' => __('Inspect prior reconciliation actions taken on this item.'),
                'available' => false,
                'action_type' => 'navigate',
            ],
        ];

        if (! isset($definitions[$code])) {
            return [
                'label' => str_replace('_', ' ', ucfirst($code)),
                'reason' => __('Follow gateway guidance for this incident class.'),
                'available' => false,
                'action_type' => 'none',
            ];
        }

        return $definitions[$code];
    }

    protected function urlForRecordType(string $type, string $uuid): ?string
    {
        if ($uuid === '') {
            return null;
        }

        return match ($type) {
            'payment_transaction' => route('settings.payments-gateway.transactions.show', $uuid),
            'callback_log' => route('settings.payments-gateway.operations-console.callback-logs.show', $uuid),
            'webhook_event' => route('settings.payments-gateway.operations-console.webhook-events.show', $uuid),
            'webhook_delivery' => route('settings.payments-gateway.operations-console.webhook-deliveries.show', $uuid),
            'dead_letter' => route('settings.payments-gateway.operations-console.dead-letters.show', $uuid),
            'unmatched_transaction' => route('settings.payments-gateway.operations-console.unmatched-transactions.show', $uuid),
            'treasury_alert' => route('settings.payments-gateway.operations-console.treasury-alerts.show', $uuid),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForDeadLetter(array $resource): array
    {
        $links = [];
        $referenceFields = [
            'payment_transaction_uuid' => ['label' => __('View transaction'), 'route' => 'settings.payments-gateway.transactions.show'],
            'callback_log_uuid' => ['label' => __('View callback log'), 'route' => 'settings.payments-gateway.operations-console.callback-logs.show'],
            'webhook_event_uuid' => ['label' => __('View webhook event'), 'route' => 'settings.payments-gateway.operations-console.webhook-events.show'],
            'webhook_delivery_uuid' => ['label' => __('View webhook delivery'), 'route' => 'settings.payments-gateway.operations-console.webhook-deliveries.show'],
        ];

        foreach ($referenceFields as $field => $meta) {
            if (filled($resource[$field] ?? null)) {
                $links[] = [
                    'label' => $meta['label'],
                    'url' => route($meta['route'], $resource[$field]),
                ];
            }
        }

        $relatedUuid = (string) ($resource['related_uuid'] ?? $resource['source_uuid'] ?? '');
        $relatedType = strtolower((string) ($resource['related_type'] ?? $resource['source_type'] ?? ''));

        if (filled($relatedUuid)) {
            $url = match ($relatedType) {
                'mpesa_callback' => route('settings.payments-gateway.operations-console.callback-logs.show', $relatedUuid),
                'webhook_delivery' => route('settings.payments-gateway.operations-console.webhook-deliveries.show', $relatedUuid),
                'webhook_event' => route('settings.payments-gateway.operations-console.webhook-events.show', $relatedUuid),
                default => null,
            };

            if ($url !== null) {
                $links[] = ['label' => __('View source record'), 'url' => $url];
            }
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForCallbackLog(array $resource): array
    {
        $transactionUuid = (string) ($resource['payment_transaction_uuid'] ?? '');

        if (! filled($transactionUuid)) {
            return [];
        }

        return [[
            'label' => __('View transaction'),
            'url' => route('settings.payments-gateway.transactions.show', $transactionUuid),
        ]];
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForWebhookDelivery(array $resource): array
    {
        $eventUuid = (string) ($resource['webhook_event_uuid'] ?? '');

        if (! filled($eventUuid)) {
            return [];
        }

        return [[
            'label' => __('View webhook event'),
            'url' => route('settings.payments-gateway.operations-console.webhook-events.show', $eventUuid),
        ]];
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForWebhookEvent(array $resource): array
    {
        $links = [];
        $transactionUuid = (string) ($resource['payment_transaction_uuid'] ?? $resource['transaction_uuid'] ?? '');

        if (filled($transactionUuid)) {
            $links[] = [
                'label' => __('View transaction'),
                'url' => route('settings.payments-gateway.transactions.show', $transactionUuid),
            ];
        }

        foreach ($resource['deliveries'] ?? [] as $delivery) {
            if (! is_array($delivery) || ! filled($delivery['uuid'] ?? null)) {
                continue;
            }

            $links[] = [
                'label' => __('View delivery').' · '.substr((string) $delivery['uuid'], 0, 8),
                'url' => route('settings.payments-gateway.operations-console.webhook-deliveries.show', $delivery['uuid']),
            ];
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForTreasuryAlert(array $resource): array
    {
        $links = [];

        foreach ($resource['related_references'] ?? [] as $record) {
            if (! is_array($record)) {
                continue;
            }

            $url = $this->urlForRecordType((string) ($record['type'] ?? ''), (string) ($record['uuid'] ?? ''));

            if ($url === null) {
                continue;
            }

            $links[] = [
                'label' => (string) ($record['label'] ?? __('Related record')),
                'url' => $url,
            ];
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $resource
     * @return list<array{label: string, url: string}>
     */
    protected function legacyRelatedLinksForUnmatchedTransaction(array $resource): array
    {
        $links = [];
        $transactionUuid = (string) ($resource['payment_transaction_uuid'] ?? $resource['transaction_uuid'] ?? '');
        $callbackUuid = (string) ($resource['source_uuid'] ?? $resource['callback_log_uuid'] ?? '');

        if (filled($transactionUuid)) {
            $links[] = [
                'label' => __('View transaction'),
                'url' => route('settings.payments-gateway.transactions.show', $transactionUuid),
            ];
        }

        if (filled($callbackUuid)) {
            $links[] = [
                'label' => __('View callback log'),
                'url' => route('settings.payments-gateway.operations-console.callback-logs.show', $callbackUuid),
            ];
        }

        return $links;
    }

    protected function formatJson(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
