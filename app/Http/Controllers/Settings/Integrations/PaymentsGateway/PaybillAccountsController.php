<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns\InteractsWithPaymentsGateway;
use App\Http\Requests\Settings\PaymentsGateway\StoreGatewayPaybillAccountRequest;
use App\Http\Requests\Settings\PaymentsGateway\UpdateGatewayPaybillAccountRequest;
use App\Models\Tenant;
use App\Services\PaymentsGateway\PaymentsGatewayClient;
use App\Support\PaymentsGateway\GatewayFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaybillAccountsController extends Controller
{
    use InteractsWithPaymentsGateway;

    public function index(string $profileUuid, PaymentsGatewayClient $client): View
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);
        $accountsResponse = $client->listPaybillAccounts($profileUuid);
        $accounts = $client->extractItems($accountsResponse);

        $gatewayUnavailable = (bool) ($accountsResponse['unavailable'] ?? false);

        return view('settings.integrations.payments-gateway.paybill-accounts.index', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'accounts' => $accounts,
            'gatewayUnavailable' => $gatewayUnavailable,
            'gatewayMessage' => $gatewayUnavailable ? $this->gatewayUnavailableMessage($accountsResponse) : null,
        ]);
    }

    public function createFromTenant(Tenant $tenant, string $profileUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Link this tenant to Payments Gateway before adding PayBill accounts.'));
        }

        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);

        if ($profileResponse['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', $this->gatewayUnavailableMessage($profileResponse));
        }

        return view('settings.integrations.payments-gateway.paybill-accounts.create', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'dashboardTenant' => $tenant,
            'returnToMapping' => true,
            'accountTypes' => GatewayFormOptions::paybillAccountTypes(),
            'environments' => GatewayFormOptions::paymentEnvironments(),
            'statuses' => GatewayFormOptions::paybillAccountStatuses(),
        ]);
    }

    public function create(string $profileUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        $profileResponse = $client->getPaymentProfile($profileUuid);
        $profile = $client->extractResource($profileResponse);

        if ($profileResponse['unavailable'] ?? false) {
            return redirect()
                ->route('settings.payments-gateway.payment-profiles.paybill-accounts.index', $profileUuid)
                ->with('gateway_error', $this->gatewayUnavailableMessage($profileResponse));
        }

        return view('settings.integrations.payments-gateway.paybill-accounts.create', [
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'dashboardTenant' => null,
            'returnToMapping' => false,
            'accountTypes' => GatewayFormOptions::paybillAccountTypes(),
            'environments' => GatewayFormOptions::paymentEnvironments(),
            'statuses' => GatewayFormOptions::paybillAccountStatuses(),
        ]);
    }

    public function storeFromTenant(StoreGatewayPaybillAccountRequest $request, Tenant $tenant, string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        if (! $tenant->isPaymentsGatewayLinked()) {
            return redirect()
                ->route('settings.payments-gateway.tenants.show', $tenant)
                ->with('gateway_error', __('Link this tenant to Payments Gateway before adding PayBill accounts.'));
        }

        $payload = $this->preparePaybillPayload($request->validated());
        $response = $client->createPaybillAccount($profileUuid, $payload);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.tenants.paybill-accounts.create', [
                'tenant' => $tenant,
                'profileUuid' => $profileUuid,
            ]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.tenants.show',
            ['tenant' => $tenant],
            __('PayBill account created on Payments Gateway.')
        );
    }

    public function store(StoreGatewayPaybillAccountRequest $request, string $profileUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $payload = $this->preparePaybillPayload($request->validated());
        $response = $client->createPaybillAccount($profileUuid, $payload);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.paybill-accounts.index',
            ['profileUuid' => $profileUuid],
            __('PayBill account created on Payments Gateway.')
        );
    }

    public function edit(string $accountUuid, PaymentsGatewayClient $client): View|RedirectResponse
    {
        $response = $client->getPaybillAccount($accountUuid);
        $account = $client->extractResource($response);

        if ($response['unavailable'] ?? false) {
            return redirect()
                ->back()
                ->with('gateway_error', $this->gatewayUnavailableMessage($response));
        }

        $profileUuid = $this->resolveProfileUuidFromAccount($account);
        $profile = $profileUuid !== ''
            ? $client->extractResource($client->getPaymentProfile($profileUuid))
            : null;

        return view('settings.integrations.payments-gateway.paybill-accounts.edit', [
            'account' => $account,
            'accountUuid' => $accountUuid,
            'profile' => $profile,
            'profileUuid' => $profileUuid,
            'accountTypes' => GatewayFormOptions::paybillAccountTypes(),
            'environments' => GatewayFormOptions::paymentEnvironments(),
            'statuses' => GatewayFormOptions::paybillAccountStatuses(),
            'gatewayUnavailable' => ! ($response['ok'] ?? false),
            'gatewayContractWarning' => $this->usesLegacyGatewayIdentifiers($account)
                ? $this->gatewayContractOutdatedMessage()
                : null,
        ]);
    }

    public function update(UpdateGatewayPaybillAccountRequest $request, string $accountUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $payload = $request->validated();
        foreach (['consumer_key', 'consumer_secret', 'passkey', 'initiator_name', 'security_credential'] as $secretField) {
            if (blank($payload[$secretField] ?? null)) {
                unset($payload[$secretField]);
            }
        }

        $response = $client->updatePaybillAccount($accountUuid, $payload);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response);
        }

        $account = $client->extractResource($response);
        $profileUuid = $this->resolveProfileUuidFromAccount($account);

        if ($profileUuid === '') {
            return back()->with('status', __('PayBill account updated on Payments Gateway.'));
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.paybill-accounts.index',
            ['profileUuid' => $profileUuid],
            __('PayBill account updated on Payments Gateway.')
        );
    }

    public function suspend(string $accountUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $accountResponse = $client->getPaybillAccount($accountUuid);
        $account = $client->extractResource($accountResponse);
        $profileUuid = $this->resolveProfileUuidFromAccount($account);

        $response = $client->suspendPaybillAccount($accountUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.paybill-accounts.index', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.paybill-accounts.index',
            ['profileUuid' => $profileUuid],
            __('PayBill account suspended on Payments Gateway.')
        );
    }

    public function activate(string $accountUuid, PaymentsGatewayClient $client): RedirectResponse
    {
        $accountResponse = $client->getPaybillAccount($accountUuid);
        $account = $client->extractResource($accountResponse);
        $profileUuid = $this->resolveProfileUuidFromAccount($account);

        $response = $client->activatePaybillAccount($accountUuid);

        if (! ($response['ok'] ?? false)) {
            return $this->redirectWithGatewayFailure($response, 'settings.payments-gateway.payment-profiles.paybill-accounts.index', ['profileUuid' => $profileUuid]);
        }

        return $this->redirectWithGatewaySuccess(
            'settings.payments-gateway.payment-profiles.paybill-accounts.index',
            ['profileUuid' => $profileUuid],
            __('PayBill account activated on Payments Gateway.')
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function preparePaybillPayload(array $payload): array
    {
        foreach (['consumer_key', 'consumer_secret', 'passkey', 'initiator_name', 'security_credential'] as $secretField) {
            if (blank($payload[$secretField] ?? null)) {
                unset($payload[$secretField]);
            }
        }

        return $payload;
    }
}
