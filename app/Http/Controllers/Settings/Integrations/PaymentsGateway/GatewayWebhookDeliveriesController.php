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

class GatewayWebhookDeliveriesController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $filterKeys = [
            'tenant_uuid',
            'status',
            'event_uuid',
            'from_date',
            'to_date',
        ];

        $filters = $this->monitoringFilters($request, $filterKeys);

        if (filled($filters['event_uuid'] ?? null)) {
            $filters['webhook_event_uuid'] = $filters['event_uuid'];
            unset($filters['event_uuid']);
        }

        $response = $client->listWebhookDeliveries(array_merge($filters, $this->monitoringPagination($request)));
        $list = $this->monitoringListResponse($client, $response);

        return view('settings.integrations.payments-gateway.webhook-deliveries.index', [
            ...$list,
            'filters' => array_merge(array_fill_keys($filterKeys, ''), $request->only($filterKeys)),
            'deliveryStatuses' => GatewayFormOptions::webhookDeliveryStatuses(),
        ]);
    }

    public function show(string $deliveryUuid, PaymentsGatewayClient $client): View
    {
        $response = $client->getWebhookDelivery($deliveryUuid);
        $delivery = $client->extractResource($response);
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.webhook-deliveries.show', [
            'delivery' => $delivery,
            'deliveryUuid' => $deliveryUuid,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($response) : null,
        ]);
    }

    public function redispatch(string $deliveryUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        DeliverWebhookEventJob::dispatch('', false, $deliveryUuid);

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.webhook-deliveries.show',
            ['deliveryUuid' => $deliveryUuid],
            __('Webhook delivery redispatch queued for the Payments Gateway.')
        );
    }
}
