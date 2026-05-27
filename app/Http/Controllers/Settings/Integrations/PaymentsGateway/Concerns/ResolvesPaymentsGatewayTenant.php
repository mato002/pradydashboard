<?php

namespace App\Http\Controllers\Settings\Integrations\PaymentsGateway\Concerns;

use App\Models\Tenant;
use App\Services\PaymentsGateway\PaymentsGatewayTenantLinkService;

trait ResolvesPaymentsGatewayTenant
{
    protected function requireLinkedGatewayUuid(Tenant $tenant): string
    {
        return app(PaymentsGatewayTenantLinkService::class)->requireGatewayUuid($tenant);
    }

    protected function optionalLinkedGatewayUuid(Tenant $tenant): ?string
    {
        return app(PaymentsGatewayTenantLinkService::class)->isLinked($tenant)
            ? (string) $tenant->payments_gateway_tenant_uuid
            : null;
    }
}
