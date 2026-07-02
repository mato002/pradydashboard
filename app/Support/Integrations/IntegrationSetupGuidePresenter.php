<?php

namespace App\Support\Integrations;

use Illuminate\Support\Facades\File;

final class IntegrationSetupGuidePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(): array
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $apiBase = $appUrl.'/api';
        $stubPath = base_path('stubs/tenant-integration');

        return [
            'app_url' => $appUrl,
            'api_base' => $apiBase,
            'checklist' => $this->checklist(),
            'sections' => $this->sections($appUrl, $apiBase),
            'endpoints' => $this->endpoints($apiBase),
            'env_dashboard' => $this->dashboardEnvVars(),
            'env_product_license' => $this->productLicenseEnv($appUrl),
            'env_product_system' => $this->readStub($stubPath.'/env-prady-system-api-snippet.txt'),
            'tenant_fields' => [
                'tenant_key' => __('Human-readable key for license API (PRADY_TENANT_KEY)'),
                'license_secret' => __('HMAC signing secret (PRADY_LICENSE_SECRET)'),
                'external_key' => __('UUID for usage heartbeat API'),
                'tenant_domain' => __('Hostname users visit — must match license check domain'),
            ],
            'stubs' => [
                'license_middleware' => $this->readStub($stubPath.'/CheckPradyLicense.php'),
                'license_config' => $this->readStub($stubPath.'/config-services-prady-snippet.php'),
                'license_routes' => $this->readStub($stubPath.'/routes-api-snippet.php'),
            ],
            'admin_links' => [
                ['label' => __('Hosted Projects'), 'route' => 'hosted-projects.index', 'permission' => 'projects.view'],
                ['label' => __('Tenants'), 'route' => 'tenants.index', 'permission' => 'tenants.view'],
                ['label' => __('API & Integrations'), 'route' => 'api-credentials.index', 'permission' => 'api_credentials.view'],
                ['label' => __('Payments Gateway'), 'route' => 'settings.payments-gateway.overview', 'permission' => 'payments_gateway.view'],
                ['label' => __('Deployments'), 'route' => 'deployments.index', 'permission' => 'deployments.view'],
                ['label' => __('License Logs'), 'route' => 'license-logs.index', 'permission' => 'license_logs.view'],
            ],
            'troubleshooting' => $this->troubleshooting(),
            'product_implementation' => $this->productImplementation(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productImplementation(): array
    {
        $stubPath = base_path('stubs/tenant-integration');

        return [
            'brief_doc' => 'docs/PRODUCT_APP_INTEGRATION_BRIEF.md',
            'stubs_dir' => 'stubs/tenant-integration/',
            'cursor_rule' => 'stubs/tenant-integration/cursor-rule-prady-integration.mdc',
            'acceptance_checks' => 'stubs/tenant-integration/ACCEPTANCE_CHECKS.md',
            'stub_files' => [
                ['file' => 'CheckPradyLicense.php', 'required' => true],
                ['file' => 'AuthenticatePradyDashboard.php', 'required' => true],
                ['file' => 'SystemInfoController.php', 'required' => true],
                ['file' => 'license-unavailable.blade.php', 'required' => true],
                ['file' => 'tenant-suspended.blade.php', 'required' => true],
                ['file' => 'bootstrap-app-middleware-snippet.php', 'required' => true],
                ['file' => 'RequirePradyModule.php', 'required' => false],
                ['file' => 'PaymentsGatewayWebhookController.php', 'required' => false],
                ['file' => 'cursor-rule-prady-integration.mdc', 'required' => false],
            ],
            'cursor_prompt' => $this->cursorPrompt(),
            'acceptance_preview' => $this->readStub($stubPath.'/ACCEPTANCE_CHECKS.md'),
        ];
    }

    private function cursorPrompt(): string
    {
        return <<<'PROMPT'
Integrate this Laravel product app with Prady Dashboard (control plane).

Read and follow docs/PRODUCT_APP_INTEGRATION_BRIEF.md and copy/adapt required files from stubs/tenant-integration/.

Constraints:
- One tenant per deployment (fixed PRADY_TENANT_KEY in .env)
- Sign license POST raw JSON with HMAC-SHA256 (PRADY_LICENSE_SECRET)
- CheckPradyLicense must skip /api/system/info and /webhooks/payments-gateway/*
- Do not modify unrelated code

Implement middleware, system/info route, error views, config/services.php, .env.example.
Run verification from stubs/tenant-integration/ACCEPTANCE_CHECKS.md when done.
PROMPT;
    }

    /**
     * @return list<array{step: int, key: string, label: string, description: string}>
     */
    private function checklist(): array
    {
        return [
            ['step' => 1, 'key' => 'dashboard', 'label' => __('Dashboard install'), 'description' => __('Configure .env, database, and queue workers on this server.')],
            ['step' => 2, 'key' => 'product', 'label' => __('Hosted project'), 'description' => __('Create a product and copy its API token.')],
            ['step' => 3, 'key' => 'tenant', 'label' => __('Tenant & subscription'), 'description' => __('Register tenant, domain, keys, and a product subscription.')],
            ['step' => 4, 'key' => 'license', 'label' => __('License API'), 'description' => __('Wire product app → dashboard license check with HMAC signing.')],
            ['step' => 5, 'key' => 'system_info', 'label' => __('Tenant system API'), 'description' => __('Expose GET /api/system/info; configure in tenant Integrations tab.')],
            ['step' => 6, 'key' => 'heartbeat', 'label' => __('Usage heartbeat'), 'description' => __('Optional push metrics from product app.')],
            ['step' => 7, 'key' => 'payments', 'label' => __('Payments Gateway'), 'description' => __('Link treasury and configure inbound webhooks.')],
            ['step' => 8, 'key' => 'deployments', 'label' => __('CI webhooks'), 'description' => __('Optional GitHub/GitLab deployment hooks.')],
            ['step' => 9, 'key' => 'verify', 'label' => __('Verify'), 'description' => __('Run connection tests and review integration readiness on each tenant.')],
        ];
    }

    /**
     * @return array<string, array{title: string, summary: string}>
     */
    private function sections(string $appUrl, string $apiBase): array
    {
        $paymentsUrl = rtrim((string) config('payment_gateway.base_url'), '/');

        return [
            'dashboard' => [
                'title' => __('1. Dashboard server setup'),
                'summary' => __('Install Laravel, copy .env.example, run migrations, and start a queue worker. Set APP_URL to this dashboard\'s public HTTPS URL.'),
            ],
            'product' => [
                'title' => __('2. Register a hosted product'),
                'summary' => __('Each product line (Property, MFI, CRM) is a Hosted Project. The auto-generated api_token identifies the product in all license and usage API calls.'),
            ],
            'tenant' => [
                'title' => __('3. Register a tenant'),
                'summary' => __('Link the tenant to a hosted project, set tenant_domain to the hostname users visit, and create a project subscription to unlock the Integrations tab.'),
            ],
            'license' => [
                'title' => __('4. Product app — license enforcement'),
                'summary' => __('Product apps call POST :endpoint on every session (or cache ~10 min). Sign the raw JSON body with the tenant license_secret.', ['endpoint' => $apiBase.'/v1/license/check']),
            ],
            'system_info' => [
                'title' => __('5. Product app — system info endpoint'),
                'summary' => __('The dashboard polls GET /api/system/info on each tenant installation for version, usage, and health. Protect with PRADY_DASHBOARD_API_TOKEN.'),
            ],
            'heartbeat' => [
                'title' => __('6. Usage heartbeat (optional)'),
                'summary' => __('Push active users, storage, and version metrics with POST :endpoint using the tenant external_key (UUID).', ['endpoint' => $apiBase.'/v1/tenant/usage']),
            ],
            'payments' => [
                'title' => __('7. Payments Gateway'),
                'summary' => __('Link each dashboard tenant to a treasury profile on :url. Configure PAYMENTS_GATEWAY_ADMIN_TOKEN and webhook secret on this dashboard.', ['url' => $paymentsUrl]),
            ],
            'deployments' => [
                'title' => __('8. Deployment CI webhooks (optional)'),
                'summary' => __('Point GitHub/GitLab webhooks to POST :endpoint/{integration_id} with the deployment webhook secret.', ['endpoint' => $apiBase.'/v1/deployments/webhooks']),
            ],
            'verify' => [
                'title' => __('9. Verify integration'),
                'summary' => __('Use the tenant command center Integration readiness panel, license logs, and manual curl tests before go-live.'),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function endpoints(string $apiBase): array
    {
        return [
            ['method' => 'POST', 'path' => $apiBase.'/v1/license/check', 'auth' => 'Project Bearer + HMAC', 'direction' => 'Product → Dashboard'],
            ['method' => 'POST', 'path' => $apiBase.'/license/check', 'auth' => 'Project Bearer', 'direction' => 'Product → Dashboard'],
            ['method' => 'POST', 'path' => $apiBase.'/v1/tenant/usage', 'auth' => 'Project Bearer', 'direction' => 'Product → Dashboard'],
            ['method' => 'GET', 'path' => '{tenant_domain}/api/system/info', 'auth' => 'Dashboard token', 'direction' => 'Dashboard → Product'],
            ['method' => 'POST', 'path' => $apiBase.'/v1/payments-gateway/webhooks', 'auth' => 'Webhook secret', 'direction' => 'Gateway → Dashboard'],
            ['method' => 'POST', 'path' => $apiBase.'/v1/deployments/webhooks/{id}', 'auth' => 'Webhook secret', 'direction' => 'CI → Dashboard'],
        ];
    }

    /**
     * @return list<array{key: string, purpose: string, required: bool}>
     */
    private function dashboardEnvVars(): array
    {
        return [
            ['key' => 'APP_URL', 'purpose' => __('Public dashboard URL'), 'required' => true],
            ['key' => 'PRADY_LICENSE_REQUIRE_SIGNATURE', 'purpose' => __('Require HMAC when tenant has license_secret'), 'required' => false],
            ['key' => 'PAYMENTS_GATEWAY_URL', 'purpose' => __('payments.pradytecai.com base URL'), 'required' => false],
            ['key' => 'PAYMENTS_GATEWAY_ADMIN_TOKEN', 'purpose' => __('Gateway admin API bearer token'), 'required' => false],
            ['key' => 'PAYMENTS_GATEWAY_WEBHOOK_SECRET', 'purpose' => __('Inbound gateway webhook auth'), 'required' => false],
            ['key' => 'DEPLOYMENTS_WEBHOOK_SECRET', 'purpose' => __('Default CI webhook secret'), 'required' => false],
            ['key' => 'INTEGRATIONS_API_TIMEOUT', 'purpose' => __('Timeout when polling tenant systems (seconds)'), 'required' => false],
        ];
    }

    private function productLicenseEnv(string $appUrl): string
    {
        return implode("\n", [
            'PRADY_DASHBOARD_URL='.$appUrl,
            'PRADY_PROJECT_API_TOKEN=<from Hosted Project → API token>',
            'PRADY_PRODUCT_KEY=<product_key e.g. property>',
            'PRADY_TENANT_KEY=<tenant_key from tenant record>',
            'PRADY_LICENSE_SECRET=<license_secret from tenant record>',
            'PRADY_LICENSE_CACHE_TTL=600',
        ]);
    }

    /**
     * @return list<array{symptom: string, cause: string, fix: string}>
     */
    private function troubleshooting(): array
    {
        return [
            ['symptom' => __('401 on license check'), 'cause' => __('Wrong project token or invalid HMAC'), 'fix' => __('Verify PRADY_PROJECT_API_TOKEN; sign the raw JSON body with license_secret.')],
            ['symptom' => __('404 tenant not found'), 'cause' => __('tenant_key mismatch or wrong hosted project'), 'fix' => __('Confirm tenant is linked to the project whose API token you use.')],
            ['symptom' => __('403 domain mismatch'), 'cause' => __('Request domain ≠ tenant_domain'), 'fix' => __('Set tenant_domain on the tenant record to the product hostname.')],
            ['symptom' => __('Tenant system API failing'), 'cause' => __('URL, token, or firewall'), 'fix' => __('Test curl from dashboard server; match auth type in Integrations tab.')],
            ['symptom' => __('Payments not importing'), 'cause' => __('Tenant not linked to gateway'), 'fix' => __('Complete Treasury Mapping; webhook must include gateway tenant_uuid.')],
        ];
    }

    private function readStub(string $path): string
    {
        if (! File::isFile($path)) {
            return '';
        }

        return trim(File::get($path));
    }
}
