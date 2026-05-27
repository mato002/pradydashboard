<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Models\Tenant;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayLinkException;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayTenantAlreadyLinkedException;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayTenantNotLinkedException;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Services\PaymentsGateway\PaymentsGatewayTenantLinkService;
use App\Support\PaymentsGateway\GatewayFormOptions;
use App\Support\PaymentsGateway\TreasuryMappingPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantProfilesController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(Request $request, PaymentsGatewayClient $client, PaymentsGatewayTenantLinkService $linkService): View
    {
        $search = trim((string) $request->query('search', ''));
        $linkageFilter = $request->query('linkage');

        $tenantsQuery = Tenant::query()
            ->with('project')
            ->orderBy('company_name');

        if ($search !== '') {
            $tenantsQuery->where(function ($query) use ($search) {
                $query->where('company_name', 'like', '%'.$search.'%')
                    ->orWhere('tenant_key', 'like', '%'.$search.'%')
                    ->orWhere('tenant_domain', 'like', '%'.$search.'%')
                    ->orWhere('payments_gateway_tenant_uuid', 'like', '%'.$search.'%');
            });
        }

        if ($linkageFilter === 'linked') {
            $tenantsQuery->whereNotNull('payments_gateway_tenant_uuid');
        } elseif ($linkageFilter === 'unlinked') {
            $tenantsQuery->whereNull('payments_gateway_tenant_uuid');
        }

        $tenants = $tenantsQuery->get()->map(function (Tenant $tenant) use ($client, $linkService) {
            $row = [
                'id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'tenant_key' => $tenant->tenant_key,
                'tenant_domain' => $tenant->tenant_domain,
                'status' => $tenant->status,
                'project_name' => $tenant->project?->name,
                'linked' => $tenant->isPaymentsGatewayLinked(),
                'payments_gateway_tenant_uuid' => $tenant->payments_gateway_tenant_uuid,
                'payments_gateway_status' => $tenant->payments_gateway_status,
                'payments_gateway_linked_at' => $tenant->payments_gateway_linked_at,
                'payment_profiles_count' => null,
                'paybill_accounts_count' => null,
                'gateway_health' => $tenant->isPaymentsGatewayLinked()
                    ? ($tenant->payments_gateway_status ?? 'linked')
                    : 'unlinked',
            ];

            if (! $tenant->isPaymentsGatewayLinked()) {
                return $row;
            }

            $summaryResponse = $client->getTenantSummary((string) $tenant->payments_gateway_tenant_uuid);

            if ($summaryResponse['ok'] ?? false) {
                $summary = $client->extractData($summaryResponse);
                $row['payment_profiles_count'] = $summary['payment_profiles_count'] ?? 0;
                $row['paybill_accounts_count'] = $summary['paybill_accounts_count'] ?? 0;
            }

            $health = $linkService->verifyLinkageHealth($tenant);
            $row['gateway_health'] = (string) ($health['status'] ?? 'error');

            return $row;
        });

        $gatewayUnavailable = ! $client->isConfigured();

        return view('settings.integrations.payments-gateway.tenants.index', [
            'tenants' => $tenants,
            'filters' => [
                'search' => $search,
                'linkage' => $linkageFilter,
            ],
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? __('Payments Gateway admin token is not configured.')
                : null,
        ]);
    }

    public function show(
        Tenant $tenant,
        PaymentsGatewayClient $client,
        PaymentsGatewayTenantLinkService $linkService,
        TreasuryMappingPresenter $presenter,
    ): View {
        $tenant->load('project');
        $linkageHealth = $linkService->verifyLinkageHealth($tenant);

        if (! $tenant->isPaymentsGatewayLinked()) {
            return view('settings.integrations.payments-gateway.tenants.show', [
                'dashboardTenant' => $tenant,
                'linked' => false,
                'linkageHealth' => $linkageHealth,
            ]);
        }

        $gatewayUuid = (string) $tenant->payments_gateway_tenant_uuid;
        $tenantResponse = $client->getTenant($gatewayUuid);
        $gatewayTenant = $client->extractResource($tenantResponse);
        $summaryResponse = $client->getTenantSummary($gatewayUuid);
        $summary = $client->extractData($summaryResponse);
        $profilesResponse = $client->listPaymentProfiles($gatewayUuid);
        $profiles = $client->extractItems($profilesResponse);
        $healthResponse = $client->health('/api/v1/health');

        $gatewayUnavailable = (bool) ($tenantResponse['unavailable'] ?? false)
            || (bool) ($profilesResponse['unavailable'] ?? false);

        $contractWarning = $this->mergeGatewayContractWarnings(
            $this->usesLegacyGatewayIdentifiers($gatewayTenant) ? $this->gatewayContractOutdatedMessage() : null,
            $summary === null && ($summaryResponse['ok'] ?? false) === false && ! ($summaryResponse['unavailable'] ?? false)
                ? $this->gatewayContractOutdatedMessage()
                : null,
        );

        $paybillAccounts = [];
        $webhookEndpoints = [];
        $apiKeys = [];

        if (! $gatewayUnavailable) {
            foreach ($profiles as $profile) {
                $profileUuid = (string) ($profile['uuid'] ?? '');

                foreach ($client->extractItems($client->listPaybillAccounts($profileUuid)) as $account) {
                    $account['payment_profile_uuid'] = $profileUuid;
                    $account['payment_profile_name'] = $profile['name'] ?? null;
                    $paybillAccounts[] = $account;
                }

                foreach ($client->extractItems($client->listWebhookEndpoints($profileUuid)) as $endpoint) {
                    $endpoint['payment_profile_uuid'] = $profileUuid;
                    $endpoint['payment_profile_name'] = $profile['name'] ?? null;
                    $webhookEndpoints[] = $endpoint;
                }

                foreach ($client->extractItems($client->listGatewayApiKeys($profileUuid)) as $apiKey) {
                    $apiKey['payment_profile_uuid'] = $profileUuid;
                    $apiKeys[] = $apiKey;
                }
            }
        }

        $apiKeys = $presenter->attachProfileNamesToApiKeys($profiles, $apiKeys);
        $groupedPaybills = $presenter->groupPaybillsByProfile($profiles, $paybillAccounts);
        $expectedTenantWebhookUrl = $presenter->expectedTenantWebhookUrl($tenant);

        $primaryPaybillUuid = $this->resolvePrimaryPaybillUuid($profiles, $paybillAccounts);
        $checklist = $presenter->buildChecklist($tenant, $profiles, $paybillAccounts, $webhookEndpoints, $apiKeys, $primaryPaybillUuid);
        $checklist = $presenter->attachChecklistActions(
            $checklist,
            route('settings.payments-gateway.tenants.show', $tenant),
            $primaryPaybillUuid
        );

        return view('settings.integrations.payments-gateway.tenants.show', [
            'dashboardTenant' => $tenant,
            'linked' => true,
            'gatewayTenant' => $gatewayTenant,
            'summary' => $summary,
            'profiles' => $profiles,
            'paybillAccounts' => $paybillAccounts,
            'groupedPaybills' => $groupedPaybills,
            'webhookEndpoints' => $webhookEndpoints,
            'apiKeys' => $apiKeys,
            'expectedTenantWebhookUrl' => $expectedTenantWebhookUrl,
            'checklist' => $checklist,
            'primaryPaybillUuid' => $primaryPaybillUuid,
            'linkageHealth' => $linkageHealth,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable
                ? __('Treasury resources could not be loaded because payments.pradytecai.com is unavailable. Local linkage details are shown below.')
                : null,
            'gatewayContractWarning' => $contractWarning,
            'gatewayApiStatus' => ($healthResponse['ok'] ?? false) ? 'reachable' : 'unreachable',
            'gatewayApiResponseMs' => $healthResponse['response_time_ms'] ?? 0,
            'formatPaybillLabel' => fn (?array $account) => GatewayFormOptions::formatPaybillAccountLabel($account),
            'formatCapabilities' => fn (array $account) => $presenter->formatCapabilities($account),
        ]);
    }

    public function link(Tenant $tenant, PaymentsGatewayTenantLinkService $linkService): RedirectResponse
    {
        try {
            $linkService->link($tenant);
        } catch (PaymentsGatewayTenantAlreadyLinkedException|PaymentsGatewayLinkException $exception) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $exception->getMessage());
        }

        return redirect()
            ->route('settings.payments-gateway.tenants.show', $tenant)
            ->with('status', __('Tenant linked to Payments Gateway successfully.'));
    }

    public function unlink(Tenant $tenant, PaymentsGatewayTenantLinkService $linkService): RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('This tenant is not linked to Payments Gateway.'));
        }

        $linkService->unlink($tenant);

        return redirect()
            ->route('settings.payments-gateway.tenants.show', $tenant)
            ->with('status', __('Tenant unlinked from Payments Gateway. Treasury records remain on payments.pradytecai.com.'));
    }

    public function sync(Tenant $tenant, PaymentsGatewayTenantLinkService $linkService): RedirectResponse
    {
        try {
            $linkService->sync($tenant);
        } catch (PaymentsGatewayTenantNotLinkedException $exception) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $exception->getMessage());
        } catch (PaymentsGatewayLinkException $exception) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $exception->getMessage());
        }

        return redirect()
            ->route('settings.payments-gateway.tenants.show', $tenant)
            ->with('status', __('Tenant treasury mapping synced with Payments Gateway.'));
    }

    public function testWebhookEndpoint(Tenant $tenant, string $endpointUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Link this tenant to Payments Gateway before testing webhook endpoints.'));
        }

        $response = $client->testWebhookEndpoint($endpointUuid);

        if (($response['status'] ?? 0) === 404) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Test webhook endpoint API not yet available'));
        }

        if ($response['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $this->gatewayUnavailableMessage($response));
        }

        if (! ($response['ok'] ?? false)) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $response['error'] ?? __('Webhook endpoint test failed.'));
        }

        $message = $response['message'] ?? __('Webhook endpoint test completed successfully.');

        return redirect()
            ->route('settings.payments-gateway.tenants.show', $tenant)
            ->with('status', $message);
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $paybillAccounts
     */
    protected function resolvePrimaryPaybillUuid(array $profiles, array $paybillAccounts): ?string
    {
        foreach ($profiles as $profile) {
            if (filled($profile['default_collection_account_uuid'] ?? null)) {
                return (string) $profile['default_collection_account_uuid'];
            }
        }

        foreach ($profiles as $profile) {
            if (filled($profile['default_disbursement_account_uuid'] ?? null)) {
                return (string) $profile['default_disbursement_account_uuid'];
            }
        }

        return isset($paybillAccounts[0]['uuid']) ? (string) $paybillAccounts[0]['uuid'] : null;
    }
}
