<?php

namespace Tests\Feature;

use App\Domain\Tenancy\ApiIntegrationsHub;
use App\Domain\Tenancy\Support\TenantSystemInfoContract;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Models\Project;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Models\User;
use App\Support\IntegrationServiceOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiIntegrationsHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_system_api_saved_from_tenant_profile(): void
    {
        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.store', [$tenant, $subscription]), [
                'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
                'purpose' => 'system_info',
                'api_name' => 'Mattare system info',
                'display_name' => 'System info API',
                'endpoint_url' => 'https://tenant.test/api/system/info',
                'authentication_type' => 'bearer_token',
                'api_secret' => 'tenant-secret-key',
            ])
            ->assertRedirect();

        $integration = TenantProjectServiceIntegration::query()->first();
        $this->assertSame(IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM, $integration->integration_category);
        $this->assertSame('system_info', $integration->purpose);
        $this->assertSame('tenant_system', $integration->service_type);
        $this->assertTrue($integration->hasStoredSecret());
    }

    public function test_secret_masking_does_not_overwrite_on_tenant_system_api(): void
    {
        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $this->actingAs($user)->post(route('tenants.project-subscriptions.integrations.store', [$tenant, $subscription]), [
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'purpose' => 'health',
            'display_name' => 'Health API',
            'endpoint_url' => 'https://tenant.test/health',
            'authentication_type' => 'api_key_header',
            'api_secret' => 'original-secret',
        ]);

        $integration = TenantProjectServiceIntegration::query()->first();
        $encrypted = $integration->getAttributes()['api_secret'];

        $this->actingAs($user)->put(route('tenants.project-subscriptions.integrations.update', [$tenant, $subscription, $integration]), [
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'purpose' => 'health',
            'display_name' => 'Health API updated',
            'endpoint_url' => 'https://tenant.test/health',
            'authentication_type' => 'api_key_header',
            'api_secret' => \App\Domain\Servers\Support\ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER,
            'status' => 'active',
        ])->assertRedirect();

        $integration->refresh();
        $this->assertSame($encrypted, $integration->getAttributes()['api_secret']);
    }

    public function test_connection_test_stores_response_code_and_time(): void
    {
        Http::fake(['*' => Http::response(['version' => '2.1.0'], 200)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'version',
            'display_name' => 'Version API',
            'endpoint_url' => 'https://tenant.test/api/version',
            'authentication_type' => 'none',
            'status' => 'not_configured',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.test', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $integration->refresh();
        $this->assertSame(200, $integration->last_response_code);
        $this->assertNotNull($integration->last_response_time_ms);
        $this->assertNotNull($integration->last_checked_at);
    }

    public function test_json_version_response_updates_tenant_version_tracking(): void
    {
        Http::fake(['*' => Http::response([
            'version' => '3.4.1',
            'build' => '4412',
            'commit' => 'abc123def',
            'environment' => 'production',
        ], 200)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'version',
            'display_name' => 'Version API',
            'endpoint_url' => 'https://tenant.test/api/version',
            'status' => 'not_configured',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.pull-version', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $version = TenantProjectVersion::query()->where('tenant_project_subscription_id', $subscription->id)->first();
        $this->assertNotNull($version);
        $this->assertSame('3.4.1', $version->current_version);
        $this->assertSame('4412', $version->build_number);
        $this->assertSame('abc123def', $version->commit_hash);
    }

    public function test_failed_endpoint_marks_api_as_failing(): void
    {
        Http::fake(['*' => Http::response('error', 503)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'health',
            'display_name' => 'Health API',
            'endpoint_url' => 'https://tenant.test/health',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.test', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $integration->refresh();
        $this->assertSame('failing', $integration->status);
        $this->assertSame('fail', $integration->last_test_status);
        $this->assertSame(503, $integration->last_response_code);
    }

    public function test_invalid_version_json_does_not_update_version_tracking(): void
    {
        Http::fake(['*' => Http::response(['version' => 'not valid!'], 200)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'version',
            'display_name' => 'Version API',
            'endpoint_url' => 'https://tenant.test/api/version',
            'status' => 'not_configured',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.pull-version', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $this->assertNull(
            TenantProjectVersion::query()->where('tenant_project_subscription_id', $subscription->id)->value('current_version')
        );
    }

    public function test_failed_connection_stores_readable_error_not_exception_dump(): void
    {
        Http::fake(['*' => function () {
            throw new ConnectionException('cURL error 7: Failed to connect');
        }]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'health',
            'display_name' => 'Health API',
            'endpoint_url' => 'https://tenant.test/health',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.test', [$tenant, $subscription, $integration]))
            ->assertRedirect()
            ->assertSessionHas('status');

        $integration->refresh();
        $this->assertSame('failing', $integration->status);
        $this->assertStringNotContainsString('cURL error', (string) $integration->last_error);
        $this->assertStringContainsString('connect', strtolower((string) $integration->last_error));
    }

    public function test_system_info_pull_records_contract_health(): void
    {
        Http::fake(['*' => Http::response(TenantSystemInfoContract::samplePayload(), 200)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'system_info',
            'display_name' => 'System info API',
            'endpoint_url' => 'https://tenant.test/api/system/info',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.pull-system-info', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $integration->refresh();
        $this->assertSame('valid', $integration->contractHealth());
        $this->assertSame(__('Valid'), $integration->contractHealthLabel());
    }

    public function test_usage_sync_stores_safe_summary_without_secrets(): void
    {
        Http::fake(['*' => Http::response([
            'usage' => [
                'active_users' => 10,
                'api_secret' => 'hidden',
            ],
        ], 200)]);

        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $integration = TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'usage',
            'display_name' => 'Usage API',
            'endpoint_url' => 'https://tenant.test/usage',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('tenants.project-subscriptions.integrations.pull-usage', [$tenant, $subscription, $integration]))
            ->assertRedirect();

        $integration->refresh();
        $usage = $integration->last_payload_summary['usage'] ?? [];
        $this->assertSame(10, $usage['active_users'] ?? null);
        $this->assertArrayNotHasKey('api_secret', $usage);
    }

    public function test_activity_log_masks_api_secret_on_create(): void
    {
        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        $this->actingAs($user)->post(route('tenants.project-subscriptions.integrations.store', [$tenant, $subscription]), [
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'purpose' => 'heartbeat',
            'display_name' => 'Heartbeat API',
            'endpoint_url' => 'https://tenant.test/hb',
            'api_secret' => 'super-secret-should-not-log',
        ]);

        $log = SystemActivityLog::query()->where('action', 'integration.created')->latest('id')->first();
        $encoded = json_encode($log?->new_values);
        $this->assertStringNotContainsString('super-secret-should-not-log', (string) $encoded);
        $this->assertStringContainsString('***MASKED***', (string) $encoded);
    }

    public function test_global_api_page_aggregates_real_stats(): void
    {
        $user = User::factory()->create();
        [$tenant, $subscription] = $this->tenantWithSubscription();

        TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM,
            'service_type' => 'tenant_system',
            'purpose' => 'heartbeat',
            'display_name' => 'Heartbeat',
            'endpoint_url' => 'https://tenant.test/hb',
            'status' => 'active',
            'success_count' => 9,
            'failure_count' => 1,
            'last_checked_at' => now(),
            'last_response_code' => 200,
            'average_response_time_ms' => 120,
        ]);

        TenantProjectServiceIntegration::query()->create([
            'tenant_project_subscription_id' => $subscription->id,
            'integration_category' => IntegrationServiceOptions::CATEGORY_PROVIDER,
            'service_type' => 'bulk_sms',
            'display_name' => 'SMS',
            'status' => 'failing',
            'failure_count' => 2,
            'last_checked_at' => now(),
        ]);

        $summary = app(ApiIntegrationsHub::class)->globalSummary();
        $this->assertSame(2, $summary['total_configured']);
        $this->assertSame(1, $summary['active']);
        $this->assertSame(1, $summary['failing']);
        $this->assertSame(75.0, $summary['success_rate']);

        $this->actingAs($user)
            ->get(route('api-credentials.index'))
            ->assertOk()
            ->assertSee(__('Tenant System APIs'))
            ->assertSee(__('Tenant System API contract'))
            ->assertSee(__('Provider Integrations'))
            ->assertSee('Heartbeat')
            ->assertSee(__('APIs by category'));
    }

    /**
     * @return array{0: Tenant, 1: TenantProjectSubscription}
     */
    private function tenantWithSubscription(): array
    {
        $project = Project::query()->create(['name' => 'Prady MFI', 'domain' => 'mfi.test']);
        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Mattare MFI',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        (new TenantProjectProvisioner)->syncPrimarySubscription($tenant);
        $subscription = TenantProjectSubscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        return [$tenant, $subscription];
    }
}
