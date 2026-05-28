<?php

namespace App\Services\PaymentsGateway;

use App\Jobs\Webhooks\DeliverWebhookEventJob;
use InvalidArgumentException;

class OperationsBulkActionService
{
    public const ACTION_DEAD_LETTERS_REPLAY = 'dead_letters.replay';

    public const ACTION_DEAD_LETTERS_DISCARD = 'dead_letters.discard';

    public const ACTION_CALLBACKS_RETRY = 'callbacks.retry';

    public const ACTION_ALERTS_ACKNOWLEDGE = 'alerts.acknowledge';

    public const ACTION_ALERTS_RESOLVE = 'alerts.resolve';

    public const ACTION_WEBHOOK_DELIVERIES_REDISPATCH = 'webhook_deliveries.redispatch';

    /**
     * @return list<string>
     */
    public static function supportedActions(): array
    {
        return [
            self::ACTION_DEAD_LETTERS_REPLAY,
            self::ACTION_DEAD_LETTERS_DISCARD,
            self::ACTION_CALLBACKS_RETRY,
            self::ACTION_ALERTS_ACKNOWLEDGE,
            self::ACTION_ALERTS_RESOLVE,
            self::ACTION_WEBHOOK_DELIVERIES_REDISPATCH,
        ];
    }

    /**
     * @param  list<string>  $uuids
     * @return array{total: int, succeeded: int, failed: int, errors: list<array{uuid: string, message: string}>}
     */
    public function execute(
        PaymentsGatewayClient $client,
        string $action,
        array $uuids,
        ?string $comments = null,
    ): array {
        if (! in_array($action, self::supportedActions(), true)) {
            throw new InvalidArgumentException(__('Unsupported bulk action: :action', ['action' => $action]));
        }

        $uuids = array_values(array_unique(array_filter($uuids, fn (mixed $uuid): bool => filled($uuid))));

        $summary = [
            'total' => count($uuids),
            'succeeded' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $payload = array_filter([
            'comments' => $comments,
        ]);

        foreach ($uuids as $uuid) {
            $response = match ($action) {
                self::ACTION_DEAD_LETTERS_REPLAY => $client->replayDeadLetter($uuid),
                self::ACTION_DEAD_LETTERS_DISCARD => $client->discardDeadLetter($uuid),
                self::ACTION_CALLBACKS_RETRY => $client->retryCallback($uuid),
                self::ACTION_ALERTS_ACKNOWLEDGE => $client->acknowledgeTreasuryAlert($uuid, $payload),
                self::ACTION_ALERTS_RESOLVE => $client->resolveTreasuryAlert($uuid, $payload),
                self::ACTION_WEBHOOK_DELIVERIES_REDISPATCH => $this->queueWebhookRedispatch($uuid),
            };

            if ($response['ok'] ?? false) {
                $summary['succeeded']++;
                continue;
            }

            $summary['failed']++;
            $summary['errors'][] = [
                'uuid' => $uuid,
                'message' => (string) ($response['error'] ?? $response['message'] ?? __('Gateway request failed.')),
            ];
        }

        return $summary;
    }

    /**
     * @return array{ok: bool}
     */
    private function queueWebhookRedispatch(string $deliveryUuid): array
    {
        DeliverWebhookEventJob::dispatch('', false, $deliveryUuid);

        return ['ok' => true];
    }
}
