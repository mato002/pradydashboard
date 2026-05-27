<?php

namespace App\Support\PaymentsGateway;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OperationsTenantDirectory
{
    /** @var Collection<string, array{name: string, dashboard_tenant_id: int|null, mapping_url: string|null}>|null */
    protected ?Collection $directory = null;

    /**
     * @return Collection<string, array{name: string, dashboard_tenant_id: int|null, mapping_url: string|null}>
     */
    public function directory(): Collection
    {
        if ($this->directory !== null) {
            return $this->directory;
        }

        $this->directory = Tenant::query()
            ->whereNotNull('payments_gateway_tenant_uuid')
            ->get(['id', 'company_name', 'payments_gateway_tenant_uuid'])
            ->mapWithKeys(fn (Tenant $tenant): array => [
                (string) $tenant->payments_gateway_tenant_uuid => [
                    'name' => (string) $tenant->company_name,
                    'dashboard_tenant_id' => $tenant->id,
                    'mapping_url' => route('settings.payments-gateway.tenants.show', $tenant),
                ],
            ]);

        return $this->directory;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function enrich(array $item): array
    {
        $tenantUuid = (string) ($item['tenant_uuid'] ?? '');
        $tenant = $tenantUuid !== '' ? $this->directory()->get($tenantUuid) : null;

        $profileUuid = (string) ($item['payment_profile_uuid'] ?? '');
        $paybillUuid = (string) ($item['paybill_account_uuid'] ?? '');

        return array_merge($item, [
            'tenant_name' => $tenant['name'] ?? ($item['tenant_name'] ?? $this->shortLabel($tenantUuid, __('Unknown tenant'))),
            'tenant_mapping_url' => $tenant['mapping_url'] ?? null,
            'payment_profile_label' => filled($item['payment_profile_name'] ?? null)
                ? (string) $item['payment_profile_name']
                : $this->shortLabel($profileUuid, __('Unknown profile')),
            'paybill_label' => filled($item['paybill_shortcode'] ?? $item['shortcode'] ?? null)
                ? (string) ($item['paybill_shortcode'] ?? $item['shortcode'])
                : $this->shortLabel($paybillUuid, __('Unknown PayBill')),
            'webhook_endpoint' => (string) ($item['target_url'] ?? $item['webhook_endpoint_url'] ?? $item['endpoint_url'] ?? '—'),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function enrichMany(array $items): array
    {
        return array_map(fn (array $item): array => $this->enrich($item), $items);
    }

    protected function shortLabel(?string $uuid, string $fallback): string
    {
        if (! filled($uuid)) {
            return $fallback;
        }

        return Str::substr($uuid, 0, 8).'…';
    }
}
