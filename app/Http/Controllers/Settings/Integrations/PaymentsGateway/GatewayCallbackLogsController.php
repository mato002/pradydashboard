<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayCallbackLogsController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $filterKeys = [
            'callback_type',
            'processing_status',
            'tenant_uuid',
            'mpesa_receipt_number',
            'checkout_request_id',
            'conversation_id',
            'from_date',
            'to_date',
        ];

        $filters = $this->monitoringFilters($request, $filterKeys);
        $response = $client->listCallbackLogs(array_merge($filters, $this->monitoringPagination($request)));
        $list = $this->monitoringListResponse($client, $response);

        return view('settings.integrations.payments-gateway.callback-logs.index', [
            ...$list,
            'filters' => array_merge(array_fill_keys($filterKeys, ''), $request->only($filterKeys)),
            'callbackTypes' => GatewayFormOptions::callbackTypes(),
            'processingStatuses' => GatewayFormOptions::callbackProcessingStatuses(),
        ]);
    }

    public function show(string $callbackLogUuid, PaymentsGatewayClient $client): View
    {
        $response = $client->getCallbackLog($callbackLogUuid);
        $callbackLog = $client->extractResource($response);
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.callback-logs.show', [
            'callbackLog' => $callbackLog,
            'callbackLogUuid' => $callbackLogUuid,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($response) : null,
        ]);
    }
}
