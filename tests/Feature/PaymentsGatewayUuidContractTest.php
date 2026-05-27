<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsGatewayUuidContractTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantUuid;

    private string $profileUuid;

    private string $accountUuid;

    private string $endpointUuid;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments.pradytecai.com',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);

        $this->tenantUuid = (string) Str::uuid();
        $this->profileUuid = (string) Str::uuid();
        $this->accountUuid = (string) Str::uuid();
        $this->endpointUuid = (string) Str::uuid();
    }

    public function test_overview_uses_gateway_stats_endpoint(): void
    {
        $this->fakeGateway([
            'GET /api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
            'GET /api/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
            'GET /api/v1/stats/overview' => Http::response([
                'success' => true,
                'data' => [
                    'total_tenants' => 4,
                    'active_tenants' => 3,
                    'total_payment_profiles' => 7,
                    'total_paybill_accounts' => 11,
                ],
            ], 200),
            'GET /api/v1/tenants' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.overview'))
            ->assertOk()
            ->assertSee('4')
            ->assertSee('7')
            ->assertSee('11');
    }

    public function test_tenant_detail_renders_summary_counts(): void
    {
        $project = \App\Models\Project::query()->create([
            'name' => 'MFI SaaS',
            'domain' => 'mfi.pradytecai.test',
            'product_slug' => 'mfi',
            'product_key' => 'mfi',
            'status' => 'active',
        ]);

        $dashboardTenant = \App\Models\Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'Acme MFI',
            'tenant_domain' => 'acme-mfi.test',
            'status' => 'active',
            'subscription_plan' => 'standard',
            'billing_cycle' => 'monthly',
            'payments_gateway_tenant_uuid' => $this->tenantUuid,
            'payments_gateway_linked_at' => now(),
            'payments_gateway_status' => 'active',
        ]);

        $this->fakeGateway([
            'GET /api/v1/tenants/'.$this->tenantUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->tenantUuid,
                    'name' => 'Acme MFI',
                    'status' => 'active',
                    'system_type' => 'mfi',
                ],
            ], 200),
            'GET /api/v1/tenants/'.$this->tenantUuid.'/summary' => Http::response([
                'success' => true,
                'data' => [
                    'payment_profiles_count' => 2,
                    'paybill_accounts_count' => 5,
                    'webhook_endpoints_count' => 1,
                    'api_keys_count' => 3,
                    'active_paybill_accounts_count' => 4,
                    'suspended_paybill_accounts_count' => 1,
                ],
            ], 200),
            'GET /api/v1/tenants/'.$this->tenantUuid.'/payment-profiles' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
        ]);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $dashboardTenant))
            ->assertOk()
            ->assertSee(__('Link status'))
            ->assertSee(__('Quick setup checklist'))
            ->assertSee($this->tenantUuid)
            ->assertSee('https://acme-mfi.test/webhooks/payments-gateway/events');
    }

    public function test_profile_detail_renders_summary_and_uuid_default_accounts(): void
    {
        $this->fakeGateway([
            'GET /api/v1/payment-profiles/'.$this->profileUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->profileUuid,
                    'tenant_uuid' => $this->tenantUuid,
                    'name' => 'Production',
                    'code' => 'prod',
                    'status' => 'active',
                    'environment' => 'production',
                    'default_collection_account_uuid' => $this->accountUuid,
                ],
            ], 200),
            'GET /api/v1/payment-profiles/'.$this->profileUuid.'/summary' => Http::response([
                'success' => true,
                'data' => [
                    'paybill_accounts_count' => 2,
                    'webhook_endpoints_count' => 1,
                    'api_keys_count' => 1,
                    'active_paybill_accounts_count' => 2,
                    'default_collection_account' => [
                        'uuid' => $this->accountUuid,
                        'account_name' => 'Main PayBill',
                        'shortcode' => '123456',
                        'account_type' => 'collection',
                    ],
                    'default_disbursement_account' => null,
                ],
            ], 200),
            'GET /api/v1/tenants/'.$this->tenantUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->tenantUuid,
                    'name' => 'Acme MFI',
                ],
            ], 200),
        ]);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.payment-profiles.show', $this->profileUuid))
            ->assertOk()
            ->assertSee('Main PayBill · 123456 · Collection')
            ->assertSee('2');
    }

    public function test_profile_edit_uses_paybill_account_uuid_select_options(): void
    {
        $this->fakeGateway([
            'GET /api/v1/payment-profiles/'.$this->profileUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->profileUuid,
                    'name' => 'Production',
                    'code' => 'prod',
                    'status' => 'active',
                    'environment' => 'production',
                    'default_collection_account_uuid' => $this->accountUuid,
                ],
            ], 200),
            'GET /api/v1/payment-profiles/'.$this->profileUuid.'/paybill-accounts' => Http::response([
                'success' => true,
                'data' => [[
                    'uuid' => $this->accountUuid,
                    'account_name' => 'Main PayBill',
                    'shortcode' => '123456',
                    'account_type' => 'collection',
                    'payment_profile_uuid' => $this->profileUuid,
                    'tenant_uuid' => $this->tenantUuid,
                ]],
            ], 200),
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.payment-profiles.edit', $this->profileUuid))
            ->assertOk()
            ->assertSee('name="default_collection_account_uuid"', false)
            ->assertSee('Main PayBill · 123456 · Collection')
            ->assertDontSee('default_collection_account_id');
    }

    public function test_profile_update_sends_uuid_default_account_fields(): void
    {
        Http::fake([
            'https://payments.pradytecai.com/*' => function ($request) {
                if ($request->method() === 'PATCH' && str_contains($request->url(), '/api/v1/payment-profiles/'.$this->profileUuid)) {
                    $payload = $request->data();

                    return Http::response([
                        'success' => true,
                        'data' => array_merge([
                            'uuid' => $this->profileUuid,
                            'name' => 'Production',
                            'code' => 'prod',
                        ], $payload),
                    ], 200);
                }

                return Http::response(['success' => true, 'data' => []], 200);
            },
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->patch(route('settings.payments-gateway.payment-profiles.update', $this->profileUuid), [
                'name' => 'Production',
                'code' => 'prod',
                'environment' => 'production',
                'status' => 'active',
                'default_collection_account_uuid' => $this->accountUuid,
                'default_disbursement_account_uuid' => '',
            ])
            ->assertRedirect(route('settings.payments-gateway.payment-profiles.show', $this->profileUuid));

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            $payload = $request->data();

            return str_contains($request->url(), '/api/v1/payment-profiles/'.$this->profileUuid)
                && ($payload['default_collection_account_uuid'] ?? null) === $this->accountUuid
                && ! array_key_exists('default_collection_account_id', $payload)
                && ! array_key_exists('default_disbursement_account_id', $payload);
        });
    }

    public function test_webhook_edit_uses_direct_endpoint_lookup(): void
    {
        $this->fakeGateway([
            'GET /api/v1/webhook-endpoints/'.$this->endpointUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->endpointUuid,
                    'payment_profile_uuid' => $this->profileUuid,
                    'name' => 'Tenant callback',
                    'url' => 'https://tenant.test/webhooks/payments',
                    'events' => ['payment.received'],
                    'status' => 'enabled',
                ],
            ], 200),
            'GET /api/v1/payment-profiles/'.$this->profileUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->profileUuid,
                    'name' => 'Production',
                ],
            ], 200),
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.webhook-endpoints.edit', $this->endpointUuid))
            ->assertOk()
            ->assertSee('Tenant callback')
            ->assertSee('https://tenant.test/webhooks/payments');
    }

    public function test_legacy_gateway_payload_shows_contract_warning(): void
    {
        $this->fakeGateway([
            'GET /api/v1/payment-profiles/'.$this->profileUuid => Http::response([
                'success' => true,
                'data' => [
                    'uuid' => $this->profileUuid,
                    'tenant_id' => 1,
                    'name' => 'Production',
                    'code' => 'prod',
                    'status' => 'active',
                    'environment' => 'production',
                    'default_collection_account_id' => 9,
                ],
            ], 200),
            'GET /api/v1/payment-profiles/'.$this->profileUuid.'/summary' => Http::response(['success' => false, 'message' => 'Not found'], 404),
        ]);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.payment-profiles.show', $this->profileUuid))
            ->assertOk()
            ->assertSee('Payments Gateway API contract is outdated. Please update payments.pradytecai.com.');
    }

    public function test_production_readiness_page_renders_report(): void
    {
        $this->fakeGateway([
            'GET /api/v1/operations/production-readiness' => Http::response([
                'success' => true,
                'data' => [
                    'overall_status' => 'warn',
                    'generated_at' => now()->toIso8601String(),
                    'database' => [
                        'overall_status' => 'pass',
                        'checks' => [
                            ['key' => 'db_connection', 'label' => 'Database connection', 'status' => 'pass', 'message' => 'Connected.'],
                        ],
                    ],
                    'queue' => [
                        'overall_status' => 'warn',
                        'checks' => [
                            'name' => 'queue',
                            'overall_status' => 'warn',
                            'sections' => [
                                'infrastructure' => [
                                    ['key' => 'queue_driver', 'label' => 'Queue driver', 'status' => 'warn', 'message' => 'QUEUE_CONNECTION=sync'],
                                ],
                            ],
                        ],
                    ],
                    'daraja' => [
                        'overall_status' => 'skip',
                        'message' => 'Optional: pass paybill_account_uuid query param for account-level Daraja diagnostics.',
                    ],
                    'callbacks' => [
                        'overall_status' => 'pass',
                        'expected_urls' => ['stk' => 'https://payments.pradytecai.com/api/v1/callbacks/mpesa/stk'],
                        'checks' => [
                            'name' => 'callbacks',
                            'overall_status' => 'pass',
                            'sections' => [
                                'urls' => [
                                    ['key' => 'stk_https', 'label' => 'STK callback HTTPS', 'status' => 'pass', 'message' => 'Valid HTTPS URL.'],
                                ],
                            ],
                        ],
                    ],
                    'workers' => [
                        'overall_status' => 'pass',
                        'checks' => [
                            ['key' => 'worker_heartbeat', 'label' => 'Worker heartbeat', 'status' => 'pass', 'message' => 'Active workers detected.'],
                        ],
                    ],
                    'security' => [
                        'overall_status' => 'pass',
                        'checks' => [
                            'name' => 'security',
                            'overall_status' => 'pass',
                            'sections' => [
                                'routes' => [
                                    ['key' => 'admin_routes', 'label' => 'Admin routes protected', 'status' => 'pass', 'message' => 'Protected.'],
                                ],
                            ],
                        ],
                    ],
                    'treasury' => [
                        'overall_status' => 'pass',
                        'checks' => [],
                    ],
                    'environment' => [
                        'overall_status' => 'pass',
                        'checks' => [
                            'name' => 'environment',
                            'overall_status' => 'pass',
                            'sections' => [
                                'application' => [
                                    ['key' => 'app_env', 'label' => 'APP_ENV', 'status' => 'pass', 'message' => 'Environment is production.'],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.production-readiness', ['run' => 1]))
            ->assertOk()
            ->assertSee(__('Production Readiness'))
            ->assertSee(__('Overall readiness'))
            ->assertSee(__('Queue driver'))
            ->assertSee(__('Optional: pass paybill_account_uuid query param for account-level Daraja diagnostics.'));
    }

    public function test_production_readiness_calls_gateway_with_filters(): void
    {
        Http::fake(function ($request) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $query = parse_url($request->url(), PHP_URL_QUERY) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/operations/production-readiness') {
                $this->assertStringContainsString('paybill_account_uuid='.$this->accountUuid, $query);
                $this->assertStringContainsString('test_oauth=1', $query);

                return Http::response([
                    'success' => true,
                    'data' => [
                        'overall_status' => 'pass',
                        'generated_at' => now()->toIso8601String(),
                        'database' => ['overall_status' => 'pass', 'checks' => []],
                        'queue' => ['overall_status' => 'pass', 'checks' => []],
                        'daraja' => ['overall_status' => 'pass', 'checks' => []],
                        'callbacks' => ['overall_status' => 'pass', 'checks' => []],
                        'workers' => ['overall_status' => 'pass', 'checks' => []],
                        'security' => ['overall_status' => 'pass', 'checks' => []],
                        'treasury' => ['overall_status' => 'pass', 'checks' => []],
                        'environment' => ['overall_status' => 'pass', 'checks' => []],
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.production-readiness', [
                'paybill_account_uuid' => $this->accountUuid,
                'test_oauth' => '1',
                'run' => '1',
            ]))
            ->assertOk()
            ->assertSee(__('Overall readiness'));
    }

    public function test_production_readiness_handles_unavailable_gateway(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.production-readiness', ['run' => 1]))
            ->assertOk()
            ->assertSee('Production readiness check could not be completed because payments.pradytecai.com is unavailable.');
    }

    public function test_go_live_dry_run_page_renders_blank_form(): void
    {
        Http::fake();

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.go-live-dry-run'))
            ->assertOk()
            ->assertSee(__('Go-Live Dry Run'))
            ->assertSee(__('Run Dry Run'))
            ->assertSee(__('Enter a PayBill Account UUID and run the dry run to validate go-live readiness on payments.pradytecai.com.'));

        Http::assertNothingSent();
    }

    public function test_go_live_dry_run_calls_gateway_with_account_uuid_and_params(): void
    {
        Http::fake(function ($request) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $query = parse_url($request->url(), PHP_URL_QUERY) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/operations/go-live-dry-run/'.$this->accountUuid) {
                $this->assertStringContainsString('skip_oauth=1', $query);
                $this->assertStringContainsString('strict=1', $query);

                return Http::response([
                    'success' => true,
                    'data' => [
                        'paybill_account_uuid' => $this->accountUuid,
                        'account_name' => 'Production PayBill',
                        'environment' => 'production',
                        'overall_status' => 'warn',
                        'strict_mode' => true,
                        'readiness_overall' => 'pass',
                        'blocking_issues' => [],
                        'warnings' => ['APP_DEBUG is false: Debug mode is acceptable for this environment.'],
                        'checklist_items' => [
                            [
                                'key' => 'app_env_production',
                                'label' => 'APP_ENV is production',
                                'status' => 'pass',
                                'category' => 'environment',
                                'message' => 'APP_ENV=production',
                                'blocking' => false,
                            ],
                            [
                                'key' => 'oauth_token',
                                'label' => 'OAuth token test',
                                'status' => 'skip',
                                'category' => 'daraja',
                                'message' => 'Skipped (--no-oauth).',
                                'blocking' => false,
                            ],
                        ],
                        'next_steps' => ['Proceed with a small-value STK test (KES 1–10) using a known phone number.'],
                        'generated_at' => now()->toIso8601String(),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.go-live-dry-run', [
                'run' => '1',
                'paybill_account_uuid' => $this->accountUuid,
                'skip_oauth' => '1',
                'strict' => '1',
            ]))
            ->assertOk()
            ->assertSee(__('Dry run result'))
            ->assertSee('Production PayBill')
            ->assertSee(__('Blocking issues'))
            ->assertSee(__('Next steps'))
            ->assertSee('APP_ENV is production');
    }

    public function test_go_live_dry_run_handles_unavailable_gateway(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.go-live-dry-run', [
                'run' => '1',
                'paybill_account_uuid' => $this->accountUuid,
            ]))
            ->assertOk()
            ->assertSee('Go-live dry run could not be completed because payments.pradytecai.com is unavailable.');
    }

    /**
     * @param  array<string, Response>  $routes
     */
    private function fakeGateway(array $routes): void
    {
        Http::fake(function ($request) use ($routes) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if (isset($routes[$key])) {
                return $routes[$key];
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });
    }

    private function paymentsGatewayViewer(): User
    {
        return $this->userWithPermissions(['payments_gateway.view']);
    }

    private function paymentsGatewayManager(): User
    {
        return $this->userWithPermissions(['payments_gateway.view', 'payments_gateway.manage']);
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    private function userWithPermissions(array $permissionCodes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Gateway Tester',
            'code' => 'gateway_tester_'.uniqid(),
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
