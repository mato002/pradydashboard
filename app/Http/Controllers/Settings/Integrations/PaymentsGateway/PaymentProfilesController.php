<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\ResolvesPaymentsGatewayTenant;
use App\Http\Requests\Settings\PaymentsGateway\StoreGatewayPaymentProfileRequest;
use App\Http\Requests\Settings\PaymentsGateway\UpdateGatewayPaymentProfileRequest;
use App\Models\Tenant;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Services\PaymentsGateway\PaymentsGatewayTenantLinkService;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentProfilesController extends Controller
{
    use InteractsWithPaymentsGateway;
    use ResolvesPaymentsGatewayTenant;

    public function index(Tenant $tenant, PaymentsGatewayClient $client): View|RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Link this tenant to Payments Gateway before managing payment profiles.'));
        }

        $gatewayUuid = $this->requireLinkedGatewayUuid($tenant);
        $tenantResponse = $client->getTenant($gatewayUuid);
        $gatewayTenant = $client->extractResource($tenantResponse);
        $profilesResponse = $client->listPaymentProfiles($gatewayUuid);
        $profiles = $client->extractItems($profilesResponse);

        $gatewayUnavailable = (bool) ($profilesResponse['unavailable'] ?? false);
        $contractWarning = $this->usesLegacyGatewayIdentifiers($gatewayTenant)
            ? $this->gatewayContractOutdatedMessage()
            : (collect($profiles)->contains(fn (array $profile) => $this->usesLegacyGatewayIdentifiers($profile))
                ? $this->gatewayContractOutdatedMessage()
                : null);

        return view('settings.integrations.payments-gateway.payment-profiles.index', [
            'dashboardTenant' => $tenant,
            'tenant' => $gatewayTenant,
            'profiles' => $profiles,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($profilesResponse) : null,
            'gatewayContractWarning' => $contractWarning,
        ]);
    }

    public function create(Tenant $tenant, PaymentsGatewayClient $client): View|RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Link this tenant to Payments Gateway before creating payment profiles.'));
        }

        $gatewayUuid = $this->requireLinkedGatewayUuid($tenant);
        $tenantResponse = $client->getTenant($gatewayUuid);
        $gatewayTenant = $client->extractResource($tenantResponse);

        if ($tenantResponse['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $this->gatewayUnavailableMessage($tenantResponse));
        }

        return view('settings.integrations.payments-gateway.payment-profiles.create', [
            'dashboardTenant' => $tenant,
            'tenant' => $gatewayTenant,
            'environments' => GatewayFormOptions::paymentEnvironments(),
            'statuses' => GatewayFormOptions::paymentProfileStatuses(),
            'paybillAccountOptions' => [],
        ]);
    }

    public function store(StoreGatewayPaymentProfileRequest $request, Tenant $tenant, PaymentsGatewayClient $client): RedirectResponse
    {
        $gatewayUuid = $this->requireLinkedGatewayUuid($tenant);
        $response = $client->createPaymentProfile($gatewayUuid, $request->validated());

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure(
                $response,
                'settings.payments-gateway.tenants.payment-profiles.create',
                ['tenant' => $tenant]
            );
        }

        $profile = $client->extractResource($response);

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.tenants.show',
            ['tenant' => $tenant],
            __('Payment profile created on Payments Gateway.')
        );
    }

    public function show(string $profileUuid, PaymentsGatewayClient $client, PaymentsGatewayTenantLinkService $linkService): View
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);
        $summaryResponse = $client->getPaymentProfileSummary($profileUuid);
        $summary = $client->extractData($summaryResponse);

        $gatewayTenant = null;
        $dashboardTenant = null;

        if ($profile !== null && filled($profile['tenant_uuid'] ?? null)) {
            $gatewayTenant = $client->extractResource($client->getTenant((string) $profile['tenant_uuid']));
            $dashboardTenant = $linkService->findDashboardTenantByGatewayUuid((string) $profile['tenant_uuid']);
        }

        $gatewayUnavailable = (bool) ($profileResponse['unavailable'] ?? false);
        $contractWarning = $this->mergeGatewayContractWarnings(
            $this->usesLegacyGatewayIdentifiers($profile) ? $this->gatewayContractOutdatedMessage() : null,
            $summary === null && ($summaryResponse['ok'] ?? false) === false && ! ($summaryResponse['unavailable'] ?? false)
                ? $this->gatewayContractOutdatedMessage()
                : null,
        );

        return view('settings.integrations.payments-gateway.payment-profiles.show', [
            'profile' => $profile,
            'summary' => $summary,
            'tenant' => $gatewayTenant,
            'dashboardTenant' => $dashboardTenant,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($profileResponse) : null,
            'gatewayContractWarning' => $contractWarning,
        ]);
    }

    public function edit(string $profileUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        $response = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($response);

        if ($response['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.payment-profiles.show', $profileUuid)
                ->with('gateway_error', $this->gatewayUnavailableMessage($response));
        }

        $accountsResponse = $client->listPaybillAccounts($profileUuid);
        $accounts = $client->extractItems($accountsResponse);
        $paybillAccountOptions = GatewayFormOptions::paybillAccountSelectOptions($accounts);

        $contractWarning = $this->usesLegacyGatewayIdentifiers($profile)
            ? $this->gatewayContractOutdatedMessage()
            : (collect($accounts)->contains(fn (array $account) => $this->usesLegacyGatewayIdentifiers($account))
                ? $this->gatewayContractOutdatedMessage()
                : null);

        return view('settings.integrations.payments-gateway.payment-profiles.edit', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'environments' => GatewayFormOptions::paymentEnvironments(),
            'statuses' => GatewayFormOptions::paymentProfileStatuses(),
            'paybillAccountOptions' => $paybillAccountOptions,
            'gatewayUnavailable' => ! ($response['ok'] ?? false),
            'gatewayContractWarning' => $contractWarning,
        ]);
    }

    public function update(UpdateGatewayPaymentProfileRequest $request, string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->updatePaymentProfile($profileUuid, $request->validated());

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.show',
            ['profileUuid' => $profileUuid],
            __('Payment profile updated on Payments Gateway.')
        );
    }

    public function suspend(string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->suspendPaymentProfile($profileUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.show', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.show',
            ['profileUuid' => $profileUuid],
            __('Payment profile suspended on Payments Gateway.')
        );
    }

    public function activate(string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $response = $client->activatePaymentProfile($profileUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.show', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.show',
            ['profileUuid' => $profileUuid],
            __('Payment profile activated on Payments Gateway.')
        );
    }
}
