<?php

namespace App\Support\Integrations;

use App\Models\HostedProject;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;

class TenantIntegrationReadinessService
{
    /**
     * @return list<array{key: string, label: string, status: string, message: string, action: ?string}>
     */
    public function checklist(Tenant $tenant): array
    {
        $tenant->loadMissing(['hostedProject', 'projectSubscriptions.integrations']);

        $items = [
            $this->hostedProjectCheck($tenant),
            $this->licenseSecretCheck($tenant),
            $this->tenantSystemIntegrationCheck($tenant),
            $this->paymentsGatewayLinkCheck($tenant),
        ];

        return $items;
    }

    public function isReady(Tenant $tenant): bool
    {
        foreach ($this->checklist($tenant) as $item) {
            if ($item['status'] === 'fail') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, action: ?string}
     */
    private function hostedProjectCheck(Tenant $tenant): array
    {
        $project = $tenant->hostedProject;

        if ($project === null) {
            return [
                'key' => 'hosted_project',
                'label' => __('Hosted product'),
                'status' => 'fail',
                'message' => __('No hosted project linked to this tenant.'),
                'action' => __('Assign a product subscription in the tenant command center.'),
            ];
        }

        if (! filled($project->api_token)) {
            return [
                'key' => 'hosted_project',
                'label' => __('Hosted product'),
                'status' => 'fail',
                'message' => __('Hosted project :name is missing an API token.', ['name' => $project->name]),
                'action' => __('Regenerate the project API token under Infrastructure → Hosted Projects.'),
            ];
        }

        return [
            'key' => 'hosted_project',
            'label' => __('Hosted product'),
            'status' => 'pass',
            'message' => __('Linked to :name (:domain).', [
                'name' => $project->name,
                'domain' => $project->domain ?? __('no domain'),
            ]),
            'action' => null,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, action: ?string}
     */
    private function licenseSecretCheck(Tenant $tenant): array
    {
        if (! filled($tenant->license_secret)) {
            return [
                'key' => 'license_secret',
                'label' => __('License signing'),
                'status' => 'fail',
                'message' => __('Tenant license secret is not set.'),
                'action' => __('License checks from hosted apps will fail until a secret is provisioned.'),
            ];
        }

        return [
            'key' => 'license_secret',
            'label' => __('License signing'),
            'status' => 'pass',
            'message' => __('License secret configured for signed API checks.'),
            'action' => null,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, action: ?string}
     */
    private function tenantSystemIntegrationCheck(Tenant $tenant): array
    {
        $subscription = $tenant->projectSubscriptions->first();

        if ($subscription === null) {
            return [
                'key' => 'tenant_system_api',
                'label' => __('Tenant system API'),
                'status' => 'warn',
                'message' => __('No product subscription — tenant system polling is inactive.'),
                'action' => __('Create a subscription and connect the tenant system integration.'),
            ];
        }

        $integration = $subscription->integrations
            ->first(fn (TenantProjectServiceIntegration $i) => $i->isTenantSystem());

        if ($integration === null) {
            return [
                'key' => 'tenant_system_api',
                'label' => __('Tenant system API'),
                'status' => 'warn',
                'message' => __('No tenant system integration configured.'),
                'action' => __('Add endpoint URL + credentials on the Integrations tab.'),
            ];
        }

        if (! filled($integration->endpoint_url)) {
            return [
                'key' => 'tenant_system_api',
                'label' => __('Tenant system API'),
                'status' => 'fail',
                'message' => __('Integration :name has no endpoint URL.', ['name' => $integration->display_name]),
                'action' => __('Set the hosted app base URL for heartbeat and usage polling.'),
            ];
        }

        $status = match ($integration->status) {
            'active' => 'pass',
            'failing' => 'fail',
            default => 'warn',
        };

        return [
            'key' => 'tenant_system_api',
            'label' => __('Tenant system API'),
            'status' => $status,
            'message' => match ($integration->status) {
                'active' => __(':name reachable (:url).', [
                    'name' => $integration->display_name,
                    'url' => $integration->endpoint_url,
                ]),
                'failing' => __(':name last check failed: :error', [
                    'name' => $integration->display_name,
                    'error' => $integration->last_error ?? __('unknown error'),
                ]),
                default => __(':name configured but not yet verified.', ['name' => $integration->display_name]),
            },
            'action' => $status !== 'pass'
                ? __('Run a connection test from the tenant Integrations tab.')
                : null,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, message: string, action: ?string}
     */
    private function paymentsGatewayLinkCheck(Tenant $tenant): array
    {
        if (! filled(config('payment_gateway.admin_token'))) {
            return [
                'key' => 'payments_gateway',
                'label' => __('Treasury mapping'),
                'status' => 'warn',
                'message' => __('Payments Gateway control plane is not configured on this dashboard.'),
                'action' => __('Set PAYMENTS_GATEWAY_ADMIN_TOKEN to enable treasury linking.'),
            ];
        }

        if (! filled($tenant->payments_gateway_tenant_uuid)) {
            return [
                'key' => 'payments_gateway',
                'label' => __('Treasury mapping'),
                'status' => 'warn',
                'message' => __('Tenant is not linked to a Payments Gateway treasury profile.'),
                'action' => __('Link via Settings → Payments Gateway → Treasury Mapping.'),
            ];
        }

        return [
            'key' => 'payments_gateway',
            'label' => __('Treasury mapping'),
            'status' => 'pass',
            'message' => __('Linked to gateway tenant :uuid.', [
                'uuid' => $tenant->payments_gateway_tenant_uuid,
            ]),
            'action' => null,
        ];
    }
}
