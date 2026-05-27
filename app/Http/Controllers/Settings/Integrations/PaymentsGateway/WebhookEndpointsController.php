<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Http\Requests\Settings\PaymentsGateway\StoreGatewayWebhookEndpointRequest;
use App\Http\Requests\Settings\PaymentsGateway\UpdateGatewayWebhookEndpointRequest;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebhookEndpointsController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(string $profileUuid, PaymentsGatewayClient $client): View
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);
        $endpointsResponse = $client->listWebhookEndpoints($profileUuid);
        $endpoints = $client->extractItems($endpointsResponse);

        $gatewayUnavailable = (bool) ($endpointsResponse['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.webhook-endpoints.index', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'endpoints' => $endpoints,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($endpointsResponse) : null,
        ]);
    }

    public function create(string $profileUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);

        if ($profileResponse['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profileUuid)
                ->with('gateway_error', $this->gatewayUnavailableMessage($profileResponse));
        }

        return view('settings.integrations.payments-gateway.webhook-endpoints.create', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'events' => GatewayFormOptions::webhookEvents(),
        ]);
    }

    public function store(StoreGatewayWebhookEndpointRequest $request, string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $payload = $request->validated();
        if (blank($payload['secret'] ?? null)) {
            unset($payload['secret']);
        }

        $response = $client->createWebhookEndpoint($profileUuid, $payload);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.webhook-endpoints.index',
            ['profileUuid' => $profileUuid],
            __('Webhook endpoint created on Payments Gateway.')
        );
    }

    public function edit(string $endpointUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        $endpointResponse = $client->getWebhookEndpoint($endpointUuid);
        $endpoint = $client->extractResource($endpointResponse);

        if ($endpointResponse['unavailable'] ?? false) {
            return redirect()
                ->back()
                ->with('gateway_error', $this->gatewayUnavailableMessage($endpointResponse));
        }

        if ($endpoint === null) {
            return redirect()
                ->back()
                ->with('gateway_error', __('Webhook endpoint could not be loaded from Payments Gateway.'));
        }

        $profileUuid = $this->resolveProfileUuidFromEndpoint($endpoint);
        $profile = $profileUuid !== ''
            ? $client->extractResource($client->getPaymentProfile($profileUuid))
            : null;

        $contractWarning = $this->mergeGatewayContractWarnings(
            $this->usesLegacyGatewayIdentifiers($endpoint) ? $this->gatewayContractOutdatedMessage() : null,
            $profileUuid === '' ? $this->gatewayContractOutdatedMessage() : null,
        );

        return view('settings.integrations.payments-gateway.webhook-endpoints.edit', [
            'endpoint' => $endpoint,
            'endpointUuid' => $endpointUuid,
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'events' => GatewayFormOptions::webhookEvents(),
            'gatewayContractWarning' => $contractWarning,
        ]);
    }

    public function update(UpdateGatewayWebhookEndpointRequest $request, string $endpointUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $payload = $request->validated();
        if (blank($payload['secret'] ?? null)) {
            unset($payload['secret']);
        }

        $response = $client->updateWebhookEndpoint($endpointUuid, $payload);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response);
        }

        $profileUuid = $this->resolveProfileUuidForEndpoint($client, $endpointUuid);

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.webhook-endpoints.index',
            ['profileUuid' => $profileUuid],
            __('Webhook endpoint updated on Payments Gateway.')
        );
    }

    public function disable(string $endpointUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $profileUuid = $this->resolveProfileUuidForEndpoint($client, $endpointUuid);
        $response = $client->disableWebhookEndpoint($endpointUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.webhook-endpoints.index', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.webhook-endpoints.index',
            ['profileUuid' => $profileUuid],
            __('Webhook endpoint disabled on Payments Gateway.')
        );
    }

    public function enable(string $endpointUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $profileUuid = $this->resolveProfileUuidForEndpoint($client, $endpointUuid);
        $response = $client->enableWebhookEndpoint($endpointUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.webhook-endpoints.index', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.webhook-endpoints.index',
            ['profileUuid' => $profileUuid],
            __('Webhook endpoint enabled on Payments Gateway.')
        );
    }

    private function resolveProfileUuidForEndpoint(PaymentsGatewayClient $client, string $endpointUuid): string
    {
        $endpoint = $client->extractResource($client->getWebhookEndpoint($endpointUuid));
        $profileUuid = $this->resolveProfileUuidFromEndpoint($endpoint);

        if ($profileUuid !== '') {
            return $profileUuid;
        }

        foreach ($client->extractItems($client->listTenants()) as $tenant) {
            foreach ($client->extractItems($client->listPaymentProfiles((string) $tenant['uuid'])) as $profile) {
                $endpoints = $client->extractItems($client->listWebhookEndpoints((string) $profile['uuid']));
                if ($this->findByUuid($endpoints, $endpointUuid) !== null) {
                    return (string) $profile['uuid'];
                }
            }
        }

        return '';
    }
}
