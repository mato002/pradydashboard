<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\View\View;

class GatewayHealthController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function __invoke(PaymentsGatewayClient $client): View
    {
        $legacyHealth = $client->health('/api/health');
        $versionedHealth = $client->health('/api/v1/health');

        $checks = [
            [
                'label' => __('Legacy health endpoint'),
                'path' => '/api/health',
                'response' => $legacyHealth,
            ],
            [
                'label' => __('Versioned health endpoint'),
                'path' => '/api/v1/health',
                'response' => $versionedHealth,
            ],
        ];

        $anyReachable = collect($checks)->contains(fn (array $check) => ($check['response']['ok'] ?? false));
        $gatewayUnavailable = ! $anyReachable && collect($checks)->every(fn (array $check) => ($check['response']['unavailable'] ?? false));

        return view('settings.integrations.payments-gateway.health', [
            'checks' => $checks,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? $this->gatewayUnavailableMessage($versionedHealth)
                : null,
            'baseUrl' => config('payment_gateway.base_url'),
            'configured' => $client->isConfigured(),
        ]);
    }
}
