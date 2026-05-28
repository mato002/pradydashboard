<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Jobs\Webhooks\DeliverWebhookEventJob;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayWebhookEventsController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $filterKeys = [
            'tenant_uuid',
            'event_type',
            'status',
            'transaction_uuid',
            'from_date',
            'to_date',
        ];

        $filters = $this->monitoringFilters($request, $filterKeys);
        $response = $client->listWebhookEvents(array_merge($filters, $this->monitoringPagination($request)));
        $list = $this->monitoringListResponse($client, $response);

        return view('settings.integrations.payments-gateway.webhook-events.index', [
            ...$list,
            'filters' => array_merge(array_fill_keys($filterKeys, ''), $request->only($filterKeys)),
            'eventTypes' => GatewayFormOptions::webhookEventTypes(),
            'eventStatuses' => GatewayFormOptions::webhookEventStatuses(),
        ]);
    }

    public function show(string $eventUuid, PaymentsGatewayClient $client): View
    {
        $response = $client->getWebhookEvent($eventUuid);
        $event = $client->extractResource($response);
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);

        $deliveriesResponse = $client->listWebhookDeliveries([
            'webhook_event_uuid' => $eventUuid,
            'per_page' => 25,
        ]);
        $deliveries = $gatewayUnavailable ? [] : $client->extractItems($deliveriesResponse);

        return view('settings.integrations.payments-gateway.webhook-events.show', [
            'event' => $event,
            'eventUuid' => $eventUuid,
            'deliveries' => $deliveries,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($response) : null,
        ]);
    }

    public function redispatch(Request $request, string $eventUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        DeliverWebhookEventJob::dispatch($eventUuid, $request->boolean('force'));

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.webhook-events.show',
            ['eventUuid' => $eventUuid],
            __('Webhook redispatch queued for the Payments Gateway.')
        );
    }
}
