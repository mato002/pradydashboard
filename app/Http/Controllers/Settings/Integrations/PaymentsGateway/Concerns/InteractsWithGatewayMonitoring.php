<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns;

use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Http\Request;

trait InteractsWithGatewayMonitoring
{
    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    protected function monitoringFilters(Request $request, array $keys): array
    {
        return array_filter(
            $request->only($keys),
            fn (mixed $value) => filled($value)
        );
    }

    /**
     * @return array{page: int, per_page: int}
     */
    protected function monitoringPagination(Request $request): array
    {
        return [
            'page' => max(1, (int) $request->query('page', 1)),
            'per_page' => max(1, min(100, (int) $request->query('per_page', 15))),
        ];
    }

    /**
     * @param  array{ok: bool, status: int, data: mixed, message: ?string, error: ?string, errors: mixed, response_time_ms: int, unavailable: bool}  $response
     * @return array{
     *     items: list<array<string, mixed>>,
     *     pagination: array{current_page: int, last_page: int, per_page: int, total: int, from: int|null, to: int|null}|null,
     *     gatewayUnavailable: bool,
     *     gatewayMessage: string|null
     * }
     */
    protected function monitoringListResponse(PaymentsGatewayClient $client, array $response): array
    {
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);

        return [
            'items' => $gatewayUnavailable ? [] : $client->extractItems($response),
            'pagination' => $gatewayUnavailable ? null : $client->extractPaginationMeta($response),
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($response) : null,
        ];
    }

    protected function formatGatewayTimestamp(?string $value): string
    {
        if (! filled($value)) {
            return '—';
        }

        return \Illuminate\Support\Carbon::parse($value)->format('M j, Y H:i:s');
    }

    protected function formatGatewayJson(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                return $value;
            }
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '—';
    }

    protected function shortUuid(?string $uuid): string
    {
        if (! filled($uuid)) {
            return '—';
        }

        return substr($uuid, 0, 8).'…';
    }

    protected function monitoringStatusVariant(string $status): string
    {
        return match (strtolower($status)) {
            'success', 'processed', 'matched', 'delivered', 'active', 'enabled' => 'success',
            'pending', 'queued', 'processing', 'received' => 'warning',
            'failed', 'cancelled', 'timeout', 'duplicate', 'ignored' => 'danger',
            default => 'neutral',
        };
    }
}
