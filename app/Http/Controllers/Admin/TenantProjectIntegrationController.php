<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Domain\Tenancy\IntegrationConnectionTester;
use App\Domain\Tenancy\Support\IntegrationApiErrorFormatter;
use App\Domain\Tenancy\TenantSystemApiClient;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Support\IntegrationServiceOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantProjectIntegrationController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly IntegrationConnectionTester $connectionTester,
        private readonly TenantSystemApiClient $tenantSystemApiClient,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $data = $this->validated($request);
        $secret = $data['api_secret'] ?? null;
        unset($data['api_secret']);

        $integration = $subscription->serviceIntegrations()->create([
            ...$data,
            'status' => $data['status'] ?? 'not_configured',
        ]);

        $this->applySecret($integration, $secret);
        $integration->save();

        $this->logIntegrationChange('integration.created', $integration, null, $secret, $data);

        return $this->redirectBack($tenant, $subscription)
            ->with('status', __('Integration saved.'));
    }

    public function update(Request $request, Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);
        abort_unless($integration->tenant_project_subscription_id === $subscription->id, 404);

        $data = $this->validated($request, $integration);
        $secret = $data['api_secret'] ?? null;
        unset($data['api_secret']);
        $old = $integration->only(array_keys($data));

        $integration->fill($data);
        $this->applySecret($integration, $secret);
        $integration->save();

        $this->logIntegrationChange('integration.updated', $integration, $old, $secret, $integration->only(array_keys($data)));

        return $this->redirectBack($tenant, $subscription)
            ->with('status', __('Integration updated.'));
    }

    public function test(Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        return $this->runSync($tenant, $subscription, $integration, 'test', fn () => $this->connectionTester->test($integration));
    }

    public function pullSystemInfo(Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        abort_unless($integration->isTenantSystem(), 404);

        return $this->runSync($tenant, $subscription, $integration, 'pull_system_info', fn () => $this->mapClientResult(
            $this->tenantSystemApiClient->pullSystemInfo($integration),
        ));
    }

    public function pullVersion(Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        abort_unless($integration->isTenantSystem(), 404);

        return $this->runSync($tenant, $subscription, $integration, 'pull_version', fn () => $this->mapClientResult(
            $this->tenantSystemApiClient->pullVersionInfo($integration),
        ));
    }

    public function pullUsage(Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        abort_unless($integration->isTenantSystem(), 404);

        return $this->runSync($tenant, $subscription, $integration, 'pull_usage', fn () => $this->mapClientResult(
            $this->tenantSystemApiClient->pullUsageStats($integration),
        ));
    }

    public function heartbeat(Tenant $tenant, TenantProjectSubscription $subscription, TenantProjectServiceIntegration $integration): RedirectResponse
    {
        abort_unless($integration->isTenantSystem(), 404);

        return $this->runSync($tenant, $subscription, $integration, 'heartbeat', fn () => $this->mapClientResult(
            $this->tenantSystemApiClient->recordHeartbeat($integration),
        ));
    }

    /**
     * @param  callable(): array<string, mixed>  $runner
     */
    private function runSync(
        Tenant $tenant,
        TenantProjectSubscription $subscription,
        TenantProjectServiceIntegration $integration,
        string $action,
        callable $runner,
    ): RedirectResponse {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);
        abort_unless($integration->tenant_project_subscription_id === $subscription->id, 404);

        try {
            $result = $runner();
        } catch (\Throwable $e) {
            $result = [
                'last_test_status' => 'fail',
                'last_error' => IntegrationApiErrorFormatter::format($e),
                'status' => 'failing',
                'response_code' => 0,
                'response_time_ms' => 0,
            ];
        }

        $this->applyCheckResult($integration, $result);
        $integration->save();

        $passed = ($result['last_test_status'] ?? '') === 'pass';

        $this->activityLogger->log(
            $passed ? 'integration.test_passed' : 'integration.test_failed',
            ActivityLogCategory::INTEGRATION,
            $this->syncMessage($action, $passed),
            $integration,
            null,
            [
                'action' => $action,
                'last_test_status' => $result['last_test_status'] ?? null,
                'response_code' => $result['response_code'] ?? null,
                'response_time_ms' => $result['response_time_ms'] ?? null,
            ],
        );

        return $this->redirectBack($tenant, $subscription)->with('status', $this->syncMessage($action, $passed));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function applyCheckResult(TenantProjectServiceIntegration $integration, array $result): void
    {
        $success = ($result['last_test_status'] ?? '') === 'pass';
        $responseCode = (int) ($result['response_code'] ?? 0);
        $responseTimeMs = (int) ($result['response_time_ms'] ?? 0);

        $integration->recordCheckResult($success, $responseCode, $responseTimeMs, $result['last_error'] ?? null);

        $summary = $integration->last_payload_summary ?? [];
        if (isset($result['payload_summary']) && is_array($result['payload_summary'])) {
            $summary = array_merge($summary, $result['payload_summary']);
        }
        unset($summary['api_secret'], $summary['token'], $summary['password']);
        $integration->last_payload_summary = $summary !== [] ? $summary : null;

        if (isset($result['status'])) {
            $integration->status = $result['status'];
        }
    }

    /**
     * @param  array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}  $result
     * @return array<string, mixed>
     */
    private function mapClientResult(array $result): array
    {
        return [
            'last_test_status' => $result['success'] ? 'pass' : 'fail',
            'last_error' => $result['error'],
            'status' => $result['success'] ? 'active' : 'failing',
            'response_code' => $result['response_code'],
            'response_time_ms' => $result['response_time_ms'],
            'payload_summary' => $result['payload_summary'],
        ];
    }

    private function syncMessage(string $action, bool $passed): string
    {
        return match ($action) {
            'pull_system_info' => $passed ? __('System info pulled successfully.') : __('System info pull failed.'),
            'pull_version' => $passed ? __('Version info updated.') : __('Version pull failed.'),
            'pull_usage' => $passed ? __('Usage stats recorded.') : __('Usage pull failed.'),
            'heartbeat' => $passed ? __('Heartbeat recorded.') : __('Heartbeat failed.'),
            default => $passed ? __('Connection test passed.') : __('Connection test failed.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?TenantProjectServiceIntegration $existing = null): array
    {
        $category = $request->input('integration_category', $existing?->integration_category ?? IntegrationServiceOptions::CATEGORY_PROVIDER);

        $rules = [
            'integration_category' => ['required', Rule::in(array_keys(IntegrationServiceOptions::integrationCategories()))],
            'display_name' => ['required', 'string', 'max:255'],
            'api_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(IntegrationServiceOptions::statuses()))],
            'endpoint_url' => ['nullable', 'url', 'max:500'],
            'authentication_type' => ['nullable', Rule::in(array_keys(IntegrationServiceOptions::authenticationTypes()))],
            'account_reference' => ['nullable', 'string', 'max:255'],
            'balance_credits' => ['nullable', 'numeric', 'min:0'],
            'monthly_quota' => ['nullable', 'integer', 'min:0'],
            'used_quota' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'api_secret' => ['nullable', 'string', 'max:500'],
        ];

        if ($category === IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM) {
            $rules['purpose'] = ['required', Rule::in(array_keys(IntegrationServiceOptions::tenantSystemPurposes()))];
            $rules['service_type'] = ['nullable', 'string', 'max:60'];
            $rules['provider_name'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['service_type'] = ['required', Rule::in(array_keys(IntegrationServiceOptions::providerServiceTypes()))];
            $rules['provider_name'] = ['nullable', 'string', 'max:255'];
            $rules['purpose'] = ['nullable', 'string', 'max:40'];
        }

        $data = $request->validate($rules);

        if ($category === IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM) {
            $data['service_type'] = 'tenant_system';
            $data['integration_category'] = IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM;
        } else {
            $data['integration_category'] = IntegrationServiceOptions::CATEGORY_PROVIDER;
            $data['purpose'] = null;
        }

        $data['authentication_type'] = $data['authentication_type'] ?? 'none';

        return $data;
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>  $data
     */
    private function logIntegrationChange(
        string $action,
        TenantProjectServiceIntegration $integration,
        ?array $old,
        ?string $secret,
        array $data,
    ): void {
        $loggedNew = collect($data)->except(['api_secret', 'last_payload_summary'])->all();
        if ($secret && $secret !== ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER) {
            $loggedNew['api_secret'] = '***MASKED***';
        }

        $this->activityLogger->log(
            $action,
            ActivityLogCategory::INTEGRATION,
            __('Integration :name :verb', [
                'name' => $integration->resolvedApiName(),
                'verb' => str_contains($action, 'created') ? __('created') : __('updated'),
            ]),
            $integration,
            $old,
            $loggedNew,
        );
    }

    private function applySecret(TenantProjectServiceIntegration $integration, ?string $secret): void
    {
        if (filled($secret) && $secret !== ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER) {
            $integration->api_secret = $secret;
            if ($integration->status === 'not_configured' && filled($integration->endpoint_url)) {
                $integration->status = 'active';
            }
        }
    }

    private function redirectBack(Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        return redirect()->route('tenants.show', [
            'tenant' => $tenant,
            'tab' => 'integrations',
            'subscription' => $subscription->id,
        ]);
    }
}
