<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithGatewayMonitoring;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayTransactionsController extends Controller
{
    use InteractsWithGatewayMonitoring;
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client): View
    {
        $filterKeys = [
            'tenant_uuid',
            'payment_profile_uuid',
            'paybill_account_uuid',
            'transaction_type',
            'status',
            'phone_number',
            'mpesa_receipt_number',
            'from_date',
            'to_date',
        ];

        $filters = $this->monitoringFilters($request, $filterKeys);
        $response = $client->listTransactions(array_merge($filters, $this->monitoringPagination($request)));
        $list = $this->monitoringListResponse($client, $response);

        return view('settings.integrations.payments-gateway.transactions.index', [
            ...$list,
            'filters' => array_merge(array_fill_keys($filterKeys, ''), $request->only($filterKeys)),
            'transactionTypes' => GatewayFormOptions::transactionTypes(),
            'transactionStatuses' => GatewayFormOptions::transactionStatuses(),
        ]);
    }

    public function show(string $transactionUuid, PaymentsGatewayClient $client): View
    {
        $response = $client->getTransaction($transactionUuid);
        $transaction = $client->extractResource($response);
        $gatewayUnavailable = (bool) ($response['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.transactions.show', [
            'transaction' => $transaction,
            'transactionUuid' => $transactionUuid,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($response) : null,
        ]);
    }
}
