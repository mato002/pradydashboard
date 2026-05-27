<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Http\Requests\Settings\PaymentsGateway\StoreGatewayApiKeyRequest;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GatewayApiKeysController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(string $profileUuid, PaymentsGatewayClient $client): View
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);
        $keysResponse = $client->listGatewayApiKeys($profileUuid);
        $keys = $client->extractItems($keysResponse);

        $gatewayUnavailable = (bool) ($keysResponse['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.api-keys.index', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'keys' => $keys,
            'rawKey' => session()->pull('gateway_raw_api_key'),
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($keysResponse) : null,
        ]);
    }

    public function store(StoreGatewayApiKeyRequest $request, string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->createGatewayApiKey($profileUuid, $request->gatewayPayload());

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.api-keys.index', ['profileUuid' => $profileUuid]);
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $rawKey = $data['raw_key'] ?? null;

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.api-keys.index',
            ['profileUuid' => $profileUuid],
            __('Gateway API key generated. Copy the raw key now — it will not be shown again.'),
            filled($rawKey) ? ['gateway_raw_api_key' => $rawKey] : []
        );
    }

    public function revoke(string $profileUuid, string $keyUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->revokeGatewayApiKey($keyUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.api-keys.index', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.api-keys.index',
            ['profileUuid' => $profileUuid],
            __('Gateway API key revoked on Payments Gateway.')
        );
    }
}
