<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns;

use App\Services\PaymentsGateway\PaymentsGatewayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

trait InteractsWithPaymentsGateway
{
    protected function gatewayContractOutdatedMessage(): string
    {
        return __('Payments Gateway API contract is outdated. Please update payments.pradytecai.com.');
    }

    /**
     * @param  array<string, mixed>|null  $resource
     * @param  list<string>  $requiredFields
     */
    protected function gatewayContractWarning(?array $resource, array $requiredFields): ?string
    {
        if ($resource === null) {
            return null;
        }

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $resource)) {
                return $this->gatewayContractOutdatedMessage();
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $resource
     */
    protected function usesLegacyGatewayIdentifiers(?array $resource): bool
    {
        if ($resource === null) {
            return false;
        }

        $legacyPairs = [
            ['tenant_id', 'tenant_uuid'],
            ['payment_profile_id', 'payment_profile_uuid'],
            ['default_collection_account_id', 'default_collection_account_uuid'],
            ['default_disbursement_account_id', 'default_disbursement_account_uuid'],
        ];

        foreach ($legacyPairs as [$legacyField, $uuidField]) {
            if (array_key_exists($legacyField, $resource) && ! array_key_exists($uuidField, $resource)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string|null>  $warnings
     */
    protected function mergeGatewayContractWarnings(?string ...$warnings): ?string
    {
        foreach ($warnings as $warning) {
            if ($warning !== null) {
                return $warning;
            }
        }

        return null;
    }

    protected function gatewayUnavailableMessage(array $response): string
    {
        if (! app(PaymentsGatewayClient::class)->isConfigured()) {
            return __('Payments Gateway admin token is not configured.');
        }

        return $response['error'] ?? __('Payments Gateway unavailable');
    }

    protected function redirectWithGatewayFailure(array $response, ?string $fallbackRoute = null, array $routeParams = []): RedirectResponse
    {
        $redirect = $fallbackRoute !== null
            ? redirect()->route($fallbackRoute, $routeParams)
            : back();

        $redirect = $redirect->withInput()->with('gateway_error', $this->gatewayUnavailableMessage($response));

        $errors = $response['errors'] ?? null;
        if (is_array($errors) && $errors !== []) {
            $bag = new MessageBag;
            foreach ($errors as $field => $messages) {
                $bag->set($field, is_array($messages) ? $messages : [$messages]);
            }

            $redirect->with('errors', (new ViewErrorBag)->put('default', $bag));
        }

        return $redirect;
    }

    protected function redirectWithGatewaySuccess(string $route, array $params, string $message, array $flash = []): RedirectResponse
    {
        return redirect()->route($route, $params)->with(array_merge(['status' => $message], $flash));
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    protected function findByUuid(array $items, string $uuid): ?array
    {
        return collect($items)->first(fn (array $item) => ($item['uuid'] ?? null) === $uuid);
    }

    /**
     * @param  array<string, mixed>|null  $account
     */
    protected function resolveProfileUuidFromAccount(?array $account): string
    {
        if ($account === null) {
            return '';
        }

        if (filled($account['payment_profile_uuid'] ?? null)) {
            return (string) $account['payment_profile_uuid'];
        }

        return '';
    }

    /**
     * @param  array<string, mixed>|null  $endpoint
     */
    protected function resolveProfileUuidFromEndpoint(?array $endpoint): string
    {
        if ($endpoint === null) {
            return '';
        }

        if (filled($endpoint['payment_profile_uuid'] ?? null)) {
            return (string) $endpoint['payment_profile_uuid'];
        }

        return '';
    }

    /**
     * @param  list<array<string, mixed>>  $tenants
     * @return list<array<string, mixed>>
     */
    protected function enrichTenantsWithCounts(PaymentsGatewayClient $client, array $tenants): array
    {
        return collect($tenants)->map(function (array $tenant) use ($client) {
            $profilesResponse = $client->listPaymentProfiles((string) ($tenant['uuid'] ?? ''));
            $profiles = $client->extractItems($profilesResponse);
            $paybillCount = 0;

            foreach ($profiles as $profile) {
                $accountsResponse = $client->listPaybillAccounts((string) ($profile['uuid'] ?? ''));
                $paybillCount += count($client->extractItems($accountsResponse));
            }

            $tenant['payment_profiles_count'] = count($profiles);
            $tenant['paybill_accounts_count'] = $paybillCount;

            return $tenant;
        })->all();
    }

    /**
     * @return array{total_profiles: int, total_paybills: int}
     */
    protected function aggregateGatewayCounts(PaymentsGatewayClient $client, array $tenants): array
    {
        $totalProfiles = 0;
        $totalPaybills = 0;

        foreach ($tenants as $tenant) {
            $profilesResponse = $client->listPaymentProfiles((string) ($tenant['uuid'] ?? ''));
            $profiles = $client->extractItems($profilesResponse);
            $totalProfiles += count($profiles);

            foreach ($profiles as $profile) {
                $accountsResponse = $client->listPaybillAccounts((string) ($profile['uuid'] ?? ''));
                $totalPaybills += count($client->extractItems($accountsResponse));
            }
        }

        return [
            'total_profiles' => $totalProfiles,
            'total_paybills' => $totalPaybills,
        ];
    }

    protected function statusVariant(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'ok', 'healthy', 'enabled' => 'success',
            'suspended', 'warning', 'degraded' => 'warning',
            'inactive', 'failed', 'offline', 'disabled' => 'danger',
            default => 'neutral',
        };
    }
}
