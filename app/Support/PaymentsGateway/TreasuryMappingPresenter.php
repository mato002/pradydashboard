<?php

namespace App\Support\PaymentsGateway;

use App\Models\Tenant;

class TreasuryMappingPresenter
{
    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $paybillAccounts
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupPaybillsByProfile(array $profiles, array $paybillAccounts): array
    {
        $grouped = [];

        foreach ($profiles as $profile) {
            $uuid = (string) ($profile['uuid'] ?? '');
            $grouped[$uuid] = [
                'profile' => $profile,
                'accounts' => [],
            ];
        }

        foreach ($paybillAccounts as $account) {
            $profileUuid = (string) ($account['payment_profile_uuid'] ?? '');

            if ($profileUuid === '') {
                continue;
            }

            if (! isset($grouped[$profileUuid])) {
                $grouped[$profileUuid] = [
                    'profile' => ['uuid' => $profileUuid, 'name' => $account['payment_profile_name'] ?? __('Unknown profile')],
                    'accounts' => [],
                ];
            }

            $grouped[$profileUuid]['accounts'][] = $account;
        }

        return $grouped;
    }

    public function expectedTenantWebhookUrl(Tenant $tenant): string
    {
        $domain = trim((string) ($tenant->tenant_domain ?? $tenant->project?->domain ?? ''));

        if ($domain === '') {
            return 'https://{tenant_primary_domain}/webhooks/payments-gateway/events';
        }

        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;

        return 'https://'.$domain.'/webhooks/payments-gateway/events';
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $apiKeys
     * @return list<array<string, mixed>>
     */
    public function attachProfileNamesToApiKeys(array $profiles, array $apiKeys): array
    {
        $profileNames = collect($profiles)->mapWithKeys(
            fn (array $profile): array => [(string) ($profile['uuid'] ?? '') => (string) ($profile['name'] ?? '')]
        )->all();

        return array_map(function (array $key) use ($profileNames): array {
            $profileUuid = (string) ($key['payment_profile_uuid'] ?? '');

            return array_merge($key, [
                'payment_profile_name' => $profileNames[$profileUuid] ?? __('Unknown profile'),
            ]);
        }, $apiKeys);
    }

    /**
     * @param  list<array{key: string, label: string, status: string, message: string|null}>  $checklist
     * @return list<array{key: string, label: string, status: string, message: string|null, action_url: string|null, action_label: string|null}>
     */
    public function attachChecklistActions(array $checklist, string $mappingPageUrl, ?string $primaryPaybillUuid): array
    {
        return array_map(function (array $item) use ($mappingPageUrl, $primaryPaybillUuid): array {
            [$actionUrl, $actionLabel] = match ($item['key']) {
                'webhook_endpoint' => [$mappingPageUrl.'#treasury-webhooks', __('View webhooks')],
                'gateway_api_key' => [$mappingPageUrl.'#treasury-api-keys', __('View API keys')],
                'production_readiness' => filled($primaryPaybillUuid)
                    ? [route('settings.payments-gateway.production-readiness', [
                        'paybill_account_uuid' => $primaryPaybillUuid,
                        'run' => 1,
                    ]), __('Run production readiness')]
                    : [null, null],
                'go_live_dry_run' => filled($primaryPaybillUuid)
                    ? [route('settings.payments-gateway.go-live-dry-run', [
                        'paybill_account_uuid' => $primaryPaybillUuid,
                        'run' => 1,
                    ]), __('Run go-live dry run')]
                    : [null, null],
                default => [null, null],
            };

            return array_merge($item, [
                'action_url' => $actionUrl,
                'action_label' => $actionLabel,
            ]);
        }, $checklist);
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $paybillAccounts
     * @param  list<array<string, mixed>>  $webhookEndpoints
     * @param  list<array<string, mixed>>  $apiKeys
     * @return list<array{key: string, label: string, status: string, message: string|null}>
     */
    public function buildChecklist(
        Tenant $tenant,
        array $profiles,
        array $paybillAccounts,
        array $webhookEndpoints,
        array $apiKeys,
        ?string $primaryPaybillUuid = null,
    ): array {
        $collectionAccounts = collect($paybillAccounts)->filter(
            fn (array $account): bool => in_array((string) ($account['account_type'] ?? ''), ['collection', 'mixed', 'treasury'], true)
                || (bool) ($account['supports_stk'] ?? false)
                || (bool) ($account['supports_c2b'] ?? false)
        );

        $stkConfigured = collect($paybillAccounts)->contains(
            fn (array $account): bool => (bool) ($account['supports_stk'] ?? false)
                && filled($account['stk_shortcode'] ?? $account['shortcode'] ?? null)
        );

        $c2bConfigured = collect($paybillAccounts)->contains(function (array $account): bool {
            if (! (bool) ($account['supports_c2b'] ?? false)) {
                return false;
            }

            return filled($account['validation_url'] ?? null) && filled($account['confirmation_url'] ?? null);
        });

        $b2cRequired = collect($paybillAccounts)->contains(fn (array $account): bool => (bool) ($account['supports_b2c'] ?? false));
        $b2cConfigured = ! $b2cRequired || collect($paybillAccounts)->contains(function (array $account): bool {
            if (! (bool) ($account['supports_b2c'] ?? false)) {
                return false;
            }

            return filled($account['b2c_result_url'] ?? null) && filled($account['b2c_timeout_url'] ?? null);
        });

        $activeApiKeys = collect($apiKeys)->contains(
            fn (array $key): bool => strtolower((string) ($key['status'] ?? '')) === 'active'
        );

        return [
            $this->checklistItem('tenant_linked', __('Tenant linked'), $tenant->isPaymentsGatewayLinked() ? 'pass' : 'fail'),
            $this->checklistItem('payment_profile', __('At least one payment profile exists'), count($profiles) > 0 ? 'pass' : 'fail'),
            $this->checklistItem('collection_paybill', __('At least one collection PayBill exists'), $collectionAccounts->isNotEmpty() ? 'pass' : 'fail'),
            $this->checklistItem('stk_account', __('STK account configured'), $stkConfigured ? 'pass' : 'fail'),
            $this->checklistItem('c2b_urls', __('C2B URLs configured'), $c2bConfigured ? 'pass' : ($b2cRequired || collect($paybillAccounts)->contains(fn ($a) => (bool) ($a['supports_c2b'] ?? false)) ? 'fail' : 'skip')),
            $this->checklistItem('b2c_account', __('B2C account configured (if payouts enabled)'), $b2cConfigured ? 'pass' : ($b2cRequired ? 'fail' : 'skip')),
            $this->checklistItem('webhook_endpoint', __('Webhook endpoint configured'), count($webhookEndpoints) > 0 ? 'pass' : 'fail'),
            $this->checklistItem('gateway_api_key', __('Gateway API key generated'), $activeApiKeys ? 'pass' : 'fail'),
            $this->checklistItem(
                'production_readiness',
                __('Production readiness pass'),
                filled($primaryPaybillUuid) ? 'pending' : 'skip',
                filled($primaryPaybillUuid) ? __('Run production readiness for the primary PayBill account.') : null
            ),
            $this->checklistItem(
                'go_live_dry_run',
                __('Go-live dry run pass'),
                filled($primaryPaybillUuid) ? 'pending' : 'skip',
                filled($primaryPaybillUuid) ? __('Run go-live dry run for the primary PayBill account.') : null
            ),
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string|null}
     */
    protected function checklistItem(string $key, string $label, string $status, ?string $message = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'message' => $message,
        ];
    }

    public function formatCapabilities(array $account): string
    {
        $capabilities = array_filter([
            ($account['supports_stk'] ?? false) ? 'STK' : null,
            ($account['supports_c2b'] ?? false) ? 'C2B' : null,
            ($account['supports_b2c'] ?? false) ? 'B2C' : null,
            ($account['supports_b2b'] ?? false) ? 'B2B' : null,
            ($account['supports_reversal'] ?? false) ? 'Reversal' : null,
        ]);

        return $capabilities !== [] ? implode(', ', $capabilities) : '—';
    }
}
