<?php

namespace App\Services\PaymentsGateway;

use App\Models\Tenant;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayLinkException;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayTenantAlreadyLinkedException;
use App\Services\PaymentsGateway\Exceptions\PaymentsGatewayTenantNotLinkedException;
use App\Support\PaymentsGateway\GatewayFormOptions;

class PaymentsGatewayTenantLinkService
{
    public function __construct(
        protected PaymentsGatewayClient $client,
    ) {}

    public function link(Tenant $tenant): Tenant
    {
        if ($this->isLinked($tenant)) {
            throw new PaymentsGatewayTenantAlreadyLinkedException;
        }

        $existingGatewayTenant = $this->findExistingGatewayTenant($tenant);

        if ($existingGatewayTenant !== null) {
            $gatewayUuid = (string) ($existingGatewayTenant['uuid'] ?? '');

            if ($gatewayUuid === '') {
                throw new PaymentsGatewayLinkException(__('Matching Payments Gateway tenant is missing a UUID.'));
            }

            return $this->persistLink($tenant, $gatewayUuid, $existingGatewayTenant);
        }

        $response = $this->client->createTenant($this->buildGatewayPayload($tenant));

        if ((bool) ($response['unavailable'] ?? false)) {
            throw new PaymentsGatewayLinkException(
                __('Payments Gateway is unavailable. Tenant link could not be completed.')
            );
        }

        if (! ($response['ok'] ?? false)) {
            throw new PaymentsGatewayLinkException(
                (string) ($response['error'] ?? $response['message'] ?? __('Unable to create tenant on Payments Gateway.'))
            );
        }

        $gatewayTenant = $this->client->extractResource($response);
        $gatewayUuid = (string) ($gatewayTenant['uuid'] ?? '');

        if ($gatewayUuid === '') {
            throw new PaymentsGatewayLinkException(__('Payments Gateway did not return a tenant UUID.'));
        }

        return $this->persistLink($tenant, $gatewayUuid, $gatewayTenant);
    }

    public function sync(Tenant $tenant): Tenant
    {
        $gatewayUuid = $this->requireGatewayUuid($tenant);

        $response = $this->client->updateTenant($gatewayUuid, $this->buildGatewayPayload($tenant));

        if ((bool) ($response['unavailable'] ?? false)) {
            throw new PaymentsGatewayLinkException(
                __('Payments Gateway is unavailable. Tenant sync could not be completed.')
            );
        }

        if (! ($response['ok'] ?? false)) {
            throw new PaymentsGatewayLinkException(
                (string) ($response['error'] ?? $response['message'] ?? __('Unable to sync tenant with Payments Gateway.'))
            );
        }

        $health = $this->verifyLinkageHealth($tenant);

        $tenant->update([
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => (string) ($health['status'] ?? 'linked'),
        ]);

        return $tenant->fresh(['project']);
    }

    public function unlink(Tenant $tenant): Tenant
    {
        $tenant->update([
            'payments_gateway_tenant_uuid' => null,
            'payments_gateway_linked_at' => null,
            'payments_gateway_status' => 'unlinked',
        ]);

        return $tenant->fresh(['project']);
    }

    /**
     * @return array{
     *     healthy: bool,
     *     status: string,
     *     gateway_tenant: array<string, mixed>|null,
     *     message: string|null
     * }
     */
    public function verifyLinkageHealth(Tenant $tenant): array
    {
        if (! $this->isLinked($tenant)) {
            return [
                'healthy' => false,
                'status' => 'unlinked',
                'gateway_tenant' => null,
                'message' => __('Tenant is not linked to Payments Gateway.'),
            ];
        }

        $response = $this->client->getTenant((string) $tenant->payments_gateway_tenant_uuid);

        if ((bool) ($response['unavailable'] ?? false)) {
            return [
                'healthy' => false,
                'status' => 'unreachable',
                'gateway_tenant' => null,
                'message' => $this->client->isConfigured()
                    ? ($response['error'] ?? __('Payments Gateway unavailable'))
                    : __('Payments Gateway admin token is not configured.'),
            ];
        }

        if (! ($response['ok'] ?? false)) {
            return [
                'healthy' => false,
                'status' => 'error',
                'gateway_tenant' => null,
                'message' => (string) ($response['error'] ?? __('Unable to verify Payments Gateway tenant linkage.')),
            ];
        }

        $gatewayTenant = $this->client->extractResource($response);

        return [
            'healthy' => true,
            'status' => (string) ($gatewayTenant['status'] ?? 'linked'),
            'gateway_tenant' => is_array($gatewayTenant) ? $gatewayTenant : null,
            'message' => null,
        ];
    }

    public function isLinked(Tenant $tenant): bool
    {
        return filled($tenant->payments_gateway_tenant_uuid);
    }

    public function requireGatewayUuid(Tenant $tenant): string
    {
        if (! $this->isLinked($tenant)) {
            throw new PaymentsGatewayTenantNotLinkedException;
        }

        return (string) $tenant->payments_gateway_tenant_uuid;
    }

    public function findDashboardTenantByGatewayUuid(string $gatewayUuid): ?Tenant
    {
        return Tenant::query()
            ->where('payments_gateway_tenant_uuid', $gatewayUuid)
            ->first();
    }

    protected function findExistingGatewayTenant(Tenant $tenant): ?array
    {
        $response = $this->client->listTenants();

        if (! ($response['ok'] ?? false)) {
            return null;
        }

        return collect($this->client->extractItems($response))->first(
            fn (array $item): bool => (string) ($item['external_tenant_id'] ?? '') === (string) $tenant->external_key
        );
    }

    /**
     * @param  array<string, mixed>|null  $gatewayTenant
     */
    protected function persistLink(Tenant $tenant, string $gatewayUuid, ?array $gatewayTenant): Tenant
    {
        if ($this->isGatewayUuidLinkedElsewhere($gatewayUuid, $tenant)) {
            throw new PaymentsGatewayLinkException(
                __('This Payments Gateway tenant is already linked to another dashboard tenant.')
            );
        }

        $tenant->update([
            'payments_gateway_tenant_uuid' => $gatewayUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => (string) ($gatewayTenant['status'] ?? 'linked'),
        ]);

        return $tenant->fresh(['project']);
    }

    protected function isGatewayUuidLinkedElsewhere(string $gatewayUuid, Tenant $tenant): bool
    {
        return Tenant::query()
            ->where('id', '!=', $tenant->id)
            ->where('payments_gateway_tenant_uuid', $gatewayUuid)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildGatewayPayload(Tenant $tenant): array
    {
        $tenant->loadMissing('project');

        return array_filter([
            'name' => $tenant->company_name,
            'slug' => $tenant->tenant_key,
            'external_tenant_id' => (string) $tenant->external_key,
            'project_code' => $tenant->project?->product_key ?? $tenant->tenant_code,
            'system_type' => $this->resolveSystemType($tenant),
            'primary_domain' => $tenant->tenant_domain ?? $tenant->project?->domain,
            'status' => $this->resolveGatewayStatus($tenant),
        ], fn (mixed $value) => $value !== null && $value !== '');
    }

    protected function resolveSystemType(Tenant $tenant): string
    {
        $candidate = strtolower((string) ($tenant->project?->product_key ?? $tenant->industry ?? 'other'));

        return in_array($candidate, GatewayFormOptions::systemTypes(), true) ? $candidate : 'other';
    }

    protected function resolveGatewayStatus(Tenant $tenant): string
    {
        return in_array(strtolower((string) $tenant->status), ['active', 'suspended'], true)
            ? strtolower((string) $tenant->status)
            : 'active';
    }
}
