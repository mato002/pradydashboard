<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\View\View;

class PaymentsGatewayOverviewController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function __invoke(PaymentsGatewayClient $client): View
    {
        $healthResponse = $client->health('/api/v1/health');
        $legacyHealthResponse = $client->health('/api/health');
        $statsResponse = $client->getOverviewStats();
        $stats = $client->extractData($statsResponse);

        $tenantsResponse = $client->listTenants();
        $tenants = $client->extractItems($tenantsResponse);

        $gatewayUnavailable = ($healthResponse['unavailable'] ?? false)
            && ($legacyHealthResponse['unavailable'] ?? false)
            && ($tenantsResponse['unavailable'] ?? false);

        $contractWarning = null;

        if ($stats === null && ! ($statsResponse['unavailable'] ?? false) && ($statsResponse['ok'] ?? false) === false) {
            $contractWarning = $this->gatewayContractOutdatedMessage();
        }

        if ($stats === null && ! $gatewayUnavailable) {
            $counts = $this->aggregateGatewayCounts($client, $tenants);
            $activeTenants = collect($tenants)->where('status', 'active')->count();
            $totalTenants = count($tenants);

            if ($contractWarning === null && ! ($statsResponse['ok'] ?? false)) {
                $contractWarning = $this->gatewayContractOutdatedMessage();
            }
        } else {
            $totalTenants = (int) ($stats['total_tenants'] ?? $stats['tenants_count'] ?? count($tenants));
            $activeTenants = (int) ($stats['active_tenants'] ?? $stats['active_tenants_count'] ?? collect($tenants)->where('status', 'active')->count());
            $counts = [
                'total_profiles' => (int) ($stats['total_payment_profiles'] ?? $stats['payment_profiles_count'] ?? 0),
                'total_paybills' => (int) ($stats['total_paybill_accounts'] ?? $stats['paybill_accounts_count'] ?? 0),
            ];
        }

        $kpis = [
            'gateway_status' => [
                'value' => ($healthResponse['ok'] ?? false) ? __('Reachable') : __('Unavailable'),
                'sublabel' => ($healthResponse['ok'] ?? false)
                    ? __(':ms ms', ['ms' => $healthResponse['response_time_ms'] ?? 0])
                    : $this->gatewayUnavailableMessage($healthResponse),
                'tone' => ($healthResponse['ok'] ?? false) ? 'emerald' : 'rose',
            ],
            'total_tenants' => [
                'value' => (string) $totalTenants,
                'sublabel' => __('Registered tenant profiles'),
                'tone' => 'indigo',
            ],
            'active_tenants' => [
                'value' => (string) $activeTenants,
                'sublabel' => __('Status: active'),
                'tone' => 'emerald',
            ],
            'total_payment_profiles' => [
                'value' => (string) $counts['total_profiles'],
                'sublabel' => __('Across all tenants'),
                'tone' => 'violet',
            ],
            'total_paybill_accounts' => [
                'value' => (string) $counts['total_paybills'],
                'sublabel' => __('M-Pesa PayBill accounts'),
                'tone' => 'amber',
            ],
            'failed_callbacks' => [
                'value' => isset($stats['failed_callbacks']) ? (string) $stats['failed_callbacks'] : '—',
                'sublabel' => isset($stats['failed_callbacks']) ? __('From gateway stats') : __('Phase 2B monitoring'),
                'tone' => 'sky',
            ],
            'reconciliation_issues' => [
                'value' => isset($stats['reconciliation_issues']) ? (string) $stats['reconciliation_issues'] : '—',
                'sublabel' => isset($stats['reconciliation_issues']) ? __('From gateway stats') : __('Phase 2B reconciliation'),
                'tone' => 'rose',
            ],
            'last_sync_time' => [
                'value' => now()->format('H:i'),
                'sublabel' => now()->format('M j, Y'),
                'tone' => 'indigo',
            ],
        ];

        return view('settings.integrations.payments-gateway.overview', [
            'kpis' => $kpis,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($tenantsResponse) : null,
            'gatewayContractWarning' => $contractWarning,
            'healthResponse' => $healthResponse,
        ]);
    }
}
