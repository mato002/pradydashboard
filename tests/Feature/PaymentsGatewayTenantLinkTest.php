<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Services\PaymentsGateway\PaymentsGatewayTenantLinkService;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsGatewayTenantLinkTest extends TestCase
{
    use RefreshDatabase;

    private string $gatewayTenantUuid;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments.pradytecai.com',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);
        $this->gatewayTenantUuid = (string) Str::uuid();
    }

    public function test_tenant_index_lists_dashboard_tenants(): void
    {
        Http::fake();

        $tenant = $this->createDashboardTenant('Acme Properties');

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.tenants.index'))
            ->assertOk()
            ->assertSee('Acme Properties')
            ->assertSee(__('Unlinked'));
    }

    public function test_link_tenant_creates_gateway_tenant_and_persists_linkage(): void
    {
        $tenant = $this->createDashboardTenant('Beta MFI');

        Http::fake(function ($request) use ($tenant) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/tenants') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            if ($request->method() === 'POST' && $path === '/api/v1/tenants') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $this->gatewayTenantUuid,
                        'name' => 'Beta MFI',
                        'status' => 'active',
                        'external_tenant_id' => $tenant->external_key,
                    ],
                ], 201);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.link', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant));

        $tenant->refresh();

        $this->assertSame($this->gatewayTenantUuid, $tenant->payments_gateway_tenant_uuid);
        $this->assertSame('active', $tenant->payments_gateway_status);
        $this->assertNotNull($tenant->payments_gateway_linked_at);
    }

    public function test_link_tenant_prevents_duplicate_dashboard_link(): void
    {
        $tenant = $this->createDashboardTenant('Linked Co');
        $tenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.link', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('gateway_error');

        Http::assertNothingSent();
    }

    public function test_link_tenant_prevents_duplicate_gateway_uuid_on_another_dashboard_tenant(): void
    {
        $linkedTenant = $this->createDashboardTenant('Already Linked');
        $linkedTenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        $tenant = $this->createDashboardTenant('New Link Attempt');

        Http::fake(function ($request) use ($tenant) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/tenants') {
                return Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $this->gatewayTenantUuid,
                        'external_tenant_id' => $tenant->external_key,
                        'status' => 'active',
                    ]],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.link', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('gateway_error');
    }

    public function test_sync_tenant_updates_gateway_and_last_sync(): void
    {
        $tenant = $this->createDashboardTenant('Sync Co');
        $tenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now()->subDay(),
            'payments_gateway_status' => 'active',
        ]);

        Http::fake(function ($request) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'PATCH' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $this->gatewayTenantUuid,
                        'name' => 'Sync Co',
                        'status' => 'active',
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $this->gatewayTenantUuid,
                        'status' => 'active',
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.sync', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('status');

        $tenant->refresh();
        $this->assertSame('active', $tenant->payments_gateway_status);
        $this->assertTrue($tenant->payments_gateway_linked_at->isToday());
    }

    public function test_unlink_tenant_clears_local_linkage(): void
    {
        $tenant = $this->createDashboardTenant('Unlink Co');
        $tenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.unlink', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant));

        $tenant->refresh();
        $this->assertNull($tenant->payments_gateway_tenant_uuid);
        $this->assertSame('unlinked', $tenant->payments_gateway_status);
    }

    public function test_link_handles_unavailable_gateway(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $tenant = $this->createDashboardTenant('Offline Co');

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.link', $tenant))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('gateway_error');

        $tenant->refresh();
        $this->assertNull($tenant->payments_gateway_tenant_uuid);
    }

    public function test_treasury_mapping_page_renders_for_linked_tenant(): void
    {
        $tenant = $this->createDashboardTenant('Treasury Co');
        $tenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($tenant, $profileUuid, $paybillUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid => Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $this->gatewayTenantUuid,
                        'name' => 'Treasury Co',
                        'status' => 'active',
                    ],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid.'/summary' => Http::response([
                    'success' => true,
                    'data' => [
                        'payment_profiles_count' => 1,
                        'paybill_accounts_count' => 1,
                        'webhook_endpoints_count' => 1,
                    ],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid.'/payment-profiles' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $profileUuid,
                        'name' => 'Production Profile',
                        'environment' => 'production',
                        'status' => 'active',
                        'default_collection_account_uuid' => $paybillUuid,
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $paybillUuid,
                        'account_name' => 'Main PayBill',
                        'account_type' => 'collection',
                        'shortcode' => '600100',
                        'stk_shortcode' => '600100',
                        'supports_stk' => true,
                        'supports_c2b' => true,
                        'validation_url' => 'https://example.test/validate',
                        'confirmation_url' => 'https://example.test/confirm',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'url' => 'https://'.$tenant->tenant_domain.'/webhooks/payments-gateway/events',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'name' => 'Primary key',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $expectedWebhookUrl = 'https://'.$tenant->tenant_domain.'/webhooks/payments-gateway/events';

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Link status'))
            ->assertSee(__('Quick setup checklist'))
            ->assertSee(__('Payment profiles'))
            ->assertSee(__('PayBill accounts'))
            ->assertSee(__('Webhook endpoints'))
            ->assertSee(__('Gateway API keys'))
            ->assertSee(__('Expected tenant listener URL'))
            ->assertSee($expectedWebhookUrl)
            ->assertSee(__('Production Profile'))
            ->assertSee(__('Main PayBill'))
            ->assertSee(__('Tenant linked'))
            ->assertSee(__('At least one payment profile exists'));
    }

    public function test_unlinked_tenant_shows_link_cta_only(): void
    {
        Http::fake();

        $tenant = $this->createDashboardTenant('Unlinked Co');

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Link existing tenant'))
            ->assertSee(__('Link this dashboard tenant to payments.pradytecai.com before managing payment profiles'))
            ->assertDontSee(__('Create payment profile'))
            ->assertDontSee(__('Quick setup checklist'))
            ->assertDontSee(__('Expected tenant listener URL'));
    }

    public function test_create_payment_profile_redirects_to_treasury_mapping_page(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Profile Co');
        $profileUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($profileUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'POST' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid.'/payment-profiles') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $profileUuid,
                        'name' => 'Sandbox Profile',
                        'status' => 'active',
                    ],
                ], 201);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.payment-profiles.store', $tenant), [
                'name' => 'Sandbox Profile',
                'code' => 'sandbox-profile',
                'environment' => 'sandbox',
                'status' => 'active',
            ])
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('status');
    }

    public function test_add_paybill_from_mapping_redirects_back_to_mapping_page(): void
    {
        $tenant = $this->createLinkedDashboardTenant('PayBill Co');
        $profileUuid = (string) Str::uuid();
        $accountUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($profileUuid, $accountUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'POST' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $accountUuid,
                        'account_name' => 'Collection PayBill',
                        'status' => 'active',
                    ],
                ], 201);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.paybill-accounts.store', [$tenant, $profileUuid]), [
                'account_name' => 'Collection PayBill',
                'account_code' => 'collection-main',
                'account_type' => 'collection',
                'shortcode' => '600200',
                'environment' => 'sandbox',
            ])
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('status');
    }

    public function test_checklist_shows_pass_and_pending_statuses(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Checklist Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($profileUuid, $paybillUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid => Http::response([
                    'success' => true,
                    'data' => ['uuid' => $this->gatewayTenantUuid, 'status' => 'active'],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid.'/summary' => Http::response([
                    'success' => true,
                    'data' => ['payment_profiles_count' => 1, 'paybill_accounts_count' => 1],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$this->gatewayTenantUuid.'/payment-profiles' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $profileUuid,
                        'name' => 'Checklist Profile',
                        'status' => 'active',
                        'default_collection_account_uuid' => $paybillUuid,
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $paybillUuid,
                        'account_type' => 'collection',
                        'supports_stk' => true,
                        'supports_c2b' => true,
                        'stk_shortcode' => '600300',
                        'validation_url' => 'https://example.test/validate',
                        'confirmation_url' => 'https://example.test/confirm',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'url' => 'https://example.test/webhooks/payments-gateway/events',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'name' => 'Active key',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('STK account configured'))
            ->assertSee(__('Webhook endpoint configured'))
            ->assertSee(__('Gateway API key generated'))
            ->assertSee(__('Production readiness pass'))
            ->assertSee(__('Go-live dry run pass'))
            ->assertSee(__('Pending'));
    }

    public function test_linked_tenant_shows_local_state_when_gateway_unavailable(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Offline Treasury Co');

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee($this->gatewayTenantUuid)
            ->assertSee(__('Payments Gateway unavailable'))
            ->assertSee(__('Treasury resources could not be loaded because payments.pradytecai.com is unavailable'));
    }

    public function test_paybill_row_has_production_readiness_link_with_uuid(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Readiness Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        $this->fakeTreasuryMappingGateway($tenant, $profileUuid, $paybillUuid);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Production Readiness'))
            ->assertSee('paybill_account_uuid='.$paybillUuid, false)
            ->assertSee('run=1', false);
    }

    public function test_paybill_row_has_go_live_dry_run_link_with_uuid(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Dry Run Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        $this->fakeTreasuryMappingGateway($tenant, $profileUuid, $paybillUuid);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Go-Live Dry Run'))
            ->assertSee('paybill_account_uuid='.$paybillUuid, false)
            ->assertSee('run=1', false);
    }

    public function test_webhook_test_button_appears_on_mapping_page(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Webhook Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();
        $endpointUuid = (string) Str::uuid();

        $this->fakeTreasuryMappingGateway($tenant, $profileUuid, $paybillUuid, $endpointUuid);

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Test endpoint'))
            ->assertSee('webhook-endpoints/'.$endpointUuid.'/test', false);
    }

    public function test_webhook_test_shows_placeholder_when_gateway_endpoint_unsupported(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Webhook Test Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();
        $endpointUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($tenant, $profileUuid, $paybillUuid, $endpointUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $gatewayTenantUuid = (string) $tenant->payments_gateway_tenant_uuid;

            if ($request->method() === 'POST' && $path === '/api/v1/webhook-endpoints/'.$endpointUuid.'/test') {
                return Http::response(['success' => false, 'message' => 'Not found'], 404);
            }

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid => Http::response([
                    'success' => true,
                    'data' => ['uuid' => $gatewayTenantUuid, 'name' => $tenant->company_name, 'status' => 'active'],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/summary' => Http::response([
                    'success' => true,
                    'data' => ['payment_profiles_count' => 1, 'paybill_accounts_count' => 1],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/payment-profiles' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $profileUuid,
                        'name' => 'Mapping Profile',
                        'status' => 'active',
                        'default_collection_account_uuid' => $paybillUuid,
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $paybillUuid,
                        'account_name' => 'Mapping PayBill',
                        'account_type' => 'collection',
                        'shortcode' => '600400',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $endpointUuid,
                        'url' => 'https://'.$tenant->tenant_domain.'/webhooks/payments-gateway/events',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'name' => 'Mapping key',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.webhook-endpoints.test', [$tenant, $endpointUuid]))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('gateway_error', __('Test webhook endpoint API not yet available'));
    }

    public function test_webhook_test_returns_success_when_gateway_supports_it(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Webhook Success Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();
        $endpointUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($tenant, $profileUuid, $paybillUuid, $endpointUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $gatewayTenantUuid = (string) $tenant->payments_gateway_tenant_uuid;

            if ($request->method() === 'POST' && $path === '/api/v1/webhook-endpoints/'.$endpointUuid.'/test') {
                return Http::response([
                    'success' => true,
                    'message' => 'Test delivery queued.',
                    'data' => ['delivery_uuid' => (string) Str::uuid()],
                ], 200);
            }

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid => Http::response([
                    'success' => true,
                    'data' => ['uuid' => $gatewayTenantUuid, 'name' => $tenant->company_name, 'status' => 'active'],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/summary' => Http::response([
                    'success' => true,
                    'data' => ['payment_profiles_count' => 1, 'paybill_accounts_count' => 1],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/payment-profiles' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $profileUuid,
                        'name' => 'Mapping Profile',
                        'status' => 'active',
                        'default_collection_account_uuid' => $paybillUuid,
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $paybillUuid,
                        'account_name' => 'Mapping PayBill',
                        'account_type' => 'collection',
                        'shortcode' => '600400',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $endpointUuid,
                        'url' => 'https://'.$tenant->tenant_domain.'/webhooks/payments-gateway/events',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'name' => 'Mapping key',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.webhook-endpoints.test', [$tenant, $endpointUuid]))
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertSessionHas('status', 'Test delivery queued.');
    }

    public function test_checklist_action_links_render(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Checklist Links Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        $this->fakeTreasuryMappingGateway($tenant, $profileUuid, $paybillUuid);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Run production readiness'))
            ->assertSee(__('Run go-live dry run'))
            ->assertSee(__('View webhooks'))
            ->assertSee(__('View API keys'))
            ->assertSee('#treasury-webhooks', false)
            ->assertSee('#treasury-api-keys', false)
            ->assertSee('paybill_account_uuid='.$paybillUuid, false);
    }

    public function test_link_service_adopts_existing_gateway_tenant_by_external_id(): void
    {
        $tenant = $this->createDashboardTenant('Adopt Co');

        Http::fake(function ($request) use ($tenant) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/tenants') {
                return Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $this->gatewayTenantUuid,
                        'external_tenant_id' => $tenant->external_key,
                        'status' => 'active',
                    ]],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        app(PaymentsGatewayTenantLinkService::class)->link($tenant);

        $tenant->refresh();
        $this->assertSame($this->gatewayTenantUuid, $tenant->payments_gateway_tenant_uuid);
    }

    private function createDashboardTenant(string $companyName): Tenant
    {
        $suffix = uniqid();
        $project = Project::query()->create([
            'name' => 'Property SaaS '.$suffix,
            'domain' => 'property-'.$suffix.'.pradytecai.test',
            'product_slug' => 'property-'.$suffix,
            'product_key' => 'property',
            'status' => 'active',
        ]);

        return Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => $companyName,
            'tenant_domain' => Str::slug($companyName).'.test',
            'status' => 'active',
            'subscription_plan' => 'standard',
            'billing_cycle' => 'monthly',
        ]);
    }

    private function createLinkedDashboardTenant(string $companyName): Tenant
    {
        $tenant = $this->createDashboardTenant($companyName);
        $tenant->update([
            'payments_gateway_tenant_uuid' => $this->gatewayTenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        return $tenant->fresh();
    }

    private function fakeTreasuryMappingGateway(
        Tenant $tenant,
        string $profileUuid,
        string $paybillUuid,
        ?string $endpointUuid = null,
    ): void {
        $endpointUuid ??= (string) Str::uuid();
        $gatewayTenantUuid = (string) $tenant->payments_gateway_tenant_uuid;

        Http::fake(function ($request) use ($tenant, $profileUuid, $paybillUuid, $endpointUuid, $gatewayTenantUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid => Http::response([
                    'success' => true,
                    'data' => ['uuid' => $gatewayTenantUuid, 'name' => $tenant->company_name, 'status' => 'active'],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/summary' => Http::response([
                    'success' => true,
                    'data' => ['payment_profiles_count' => 1, 'paybill_accounts_count' => 1],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/tenants/'.$gatewayTenantUuid.'/payment-profiles' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $profileUuid,
                        'name' => 'Mapping Profile',
                        'status' => 'active',
                        'default_collection_account_uuid' => $paybillUuid,
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $paybillUuid,
                        'account_name' => 'Mapping PayBill',
                        'account_type' => 'collection',
                        'shortcode' => '600400',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $endpointUuid,
                        'url' => 'https://'.$tenant->tenant_domain.'/webhooks/payments-gateway/events',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => (string) Str::uuid(),
                        'name' => 'Mapping key',
                        'status' => 'active',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });
    }

    private function paymentsGatewayManager(): User
    {
        return $this->userWithPermissions(['payments_gateway.view', 'payments_gateway.manage']);
    }

    private function paymentsGatewayViewer(): User
    {
        return $this->userWithPermissions(['payments_gateway.view']);
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    private function userWithPermissions(array $permissionCodes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Gateway Link Tester',
            'code' => 'gateway_link_tester_'.uniqid(),
            'status' => 'active',
        ]);
        $role->permissions()->sync(Permission::query()->whereIn('code', $permissionCodes)->pluck('id'));

        $assignment = UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'scope_type' => RoleScopeType::Global,
            'status' => UserRoleAssignmentStatus::Active,
        ]);

        UserActiveRole::query()->create([
            'user_id' => $user->id,
            'user_role_assignment_id' => $assignment->id,
            'activated_at' => now(),
        ]);

        return $user->fresh();
    }
}
