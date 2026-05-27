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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsGatewayIncidentInvestigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments.pradytecai.com',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);
    }

    public function test_dead_letter_investigation_page_renders_enriched_context(): void
    {
        $uuid = (string) Str::uuid();
        $callbackUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid, $callbackUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/dead-letters/'.$uuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $uuid,
                        'source_type' => 'mpesa_callback',
                        'queue_name' => 'callbacks',
                        'status' => 'pending',
                        'failure_reason' => 'Processing timeout',
                        'callback_log_uuid' => $callbackUuid,
                        'related_type' => 'mpesa_callback',
                        'related_uuid' => $callbackUuid,
                        'payload' => ['redacted' => true],
                        'created_at' => now()->toIso8601String(),
                        'investigation' => $this->investigationBlock('dead_letter', $uuid, [
                            'risk_level' => 'high',
                            'recommended_next_actions' => ['replay_dead_letter', 'discard_dead_letter'],
                            'related_records' => [[
                                'type' => 'callback_log',
                                'uuid' => $callbackUuid,
                                'label' => 'Callback log',
                                'status' => 'failed',
                            ]],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.dead-letters.show', $uuid))
            ->assertOk()
            ->assertSee(__('Dead letter investigation'))
            ->assertSee(__('Recommended next actions'))
            ->assertSee(__('Risk: High'))
            ->assertSee($callbackUuid)
            ->assertSee(__('Replay dead letter'));
    }

    public function test_treasury_alert_investigation_renders_investigation_block(): void
    {
        $uuid = (string) Str::uuid();
        $transactionUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid, $transactionUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/treasury/alerts/'.$uuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $uuid,
                        'title' => 'Callback mismatch detected',
                        'message' => 'Amount mismatch on B2C callback.',
                        'severity' => 'critical',
                        'status' => 'open',
                        'evidence' => ['payment_transaction_uuid' => $transactionUuid],
                        'related_references' => [[
                            'type' => 'payment_transaction',
                            'uuid' => $transactionUuid,
                            'label' => 'Payment transaction',
                        ]],
                        'investigation' => $this->investigationBlock('treasury_alert', $uuid, [
                            'risk_level' => 'critical',
                            'recommended_next_actions' => ['acknowledge_alert', 'resolve_alert', 'review_payment_transaction'],
                            'related_records' => [[
                                'type' => 'payment_transaction',
                                'uuid' => $transactionUuid,
                                'label' => 'Payment transaction',
                            ]],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.treasury-alerts.show', $uuid))
            ->assertOk()
            ->assertSee('Amount mismatch on B2C callback.')
            ->assertSee(__('Recommended next actions'))
            ->assertSee(__('Risk: Critical'))
            ->assertDontSee(__('Alert detail API not available yet.'));
    }

    public function test_webhook_event_investigation_page_renders_deliveries(): void
    {
        $uuid = (string) Str::uuid();
        $deliveryUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid, $deliveryUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/webhook-events/'.$uuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $uuid,
                        'event_type' => 'payment.success',
                        'status' => 'failed',
                        'deliveries' => [[
                            'uuid' => $deliveryUuid,
                            'delivery_status' => 'failed',
                            'response_status' => 503,
                            'error_message' => 'HTTP 503 from tenant endpoint',
                        ]],
                        'latest_delivery' => [
                            'uuid' => $deliveryUuid,
                            'delivery_status' => 'failed',
                            'response_status' => 503,
                            'error_message' => 'HTTP 503 from tenant endpoint',
                        ],
                        'error_context' => ['latest_delivery_error' => 'HTTP 503 from tenant endpoint'],
                        'investigation' => $this->investigationBlock('webhook_event', $uuid, [
                            'risk_level' => 'high',
                            'recommended_next_actions' => ['redispatch_webhook_event'],
                            'related_records' => [[
                                'type' => 'webhook_delivery',
                                'uuid' => $deliveryUuid,
                                'label' => 'Webhook delivery',
                                'status' => 'failed',
                            ]],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.webhook-events.show', $uuid))
            ->assertOk()
            ->assertSee(__('Webhook event investigation'))
            ->assertSee('HTTP 503 from tenant endpoint')
            ->assertSee(__('Recommended next actions'))
            ->assertSee(route('settings.payments-gateway.operations-console.webhook-deliveries.show', $deliveryUuid, false));
    }

    public function test_unmatched_transaction_investigation_page_renders_suggested_matches(): void
    {
        $uuid = (string) Str::uuid();
        $transactionUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid, $transactionUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/treasury/reconciliation/unmatched/'.$uuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $uuid,
                        'reason' => 'callback_unlinked',
                        'status' => 'open',
                        'suggested_matches' => [[
                            'transaction_uuid' => $transactionUuid,
                            'confidence' => 0.82,
                            'reason' => 'amount_match',
                        ]],
                        'resolution_history' => [[
                            'action' => 'opened',
                            'occurred_at' => now()->toIso8601String(),
                        ]],
                        'investigation' => $this->investigationBlock('unmatched_transaction', $uuid, [
                            'risk_level' => 'high',
                            'recommended_next_actions' => ['review_suggested_matches', 'resolve_unmatched_transaction'],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.unmatched-transactions.show', $uuid))
            ->assertOk()
            ->assertSee(__('Suggested matches'))
            ->assertSee('0.82')
            ->assertSee(__('Recommended next actions'))
            ->assertSee(__('Unavailable in dashboard'));
    }

    public function test_treasury_alert_investigation_shows_unavailable_fallback_on_404(): void
    {
        $uuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/treasury/alerts/'.$uuid) {
                return Http::response(['success' => false, 'message' => 'Not found'], 404);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.treasury-alerts.show', $uuid))
            ->assertOk()
            ->assertSee(__('Alert detail API not available yet.'));
    }

    public function test_incident_panels_include_investigate_links(): void
    {
        $deadLetterUuid = (string) Str::uuid();
        $callbackUuid = (string) Str::uuid();
        $deliveryUuid = (string) Str::uuid();
        $alertUuid = (string) Str::uuid();
        $unmatchedUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($deadLetterUuid, $callbackUuid, $deliveryUuid, $alertUuid, $unmatchedUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $query = [];
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);

            if ($path === '/api/v1/health') {
                return Http::response(['success' => true, 'data' => ['status' => 'ok']], 200);
            }

            if ($path === '/api/v1/operations/queue/dead-letters') {
                return Http::response(['success' => true, 'data' => [[
                    'uuid' => $deadLetterUuid,
                    'source_type' => 'queue_job',
                    'queue_name' => 'default',
                    'status' => 'pending',
                    'created_at' => now()->toIso8601String(),
                ]]], 200);
            }

            if ($path === '/api/v1/callback-logs' && ($query['processing_status'] ?? null) === 'failed') {
                return Http::response(['success' => true, 'data' => [[
                    'uuid' => $callbackUuid,
                    'callback_type' => 'c2b',
                    'processing_status' => 'failed',
                ]]], 200);
            }

            if ($path === '/api/v1/webhook-deliveries' && ($query['status'] ?? null) === 'failed') {
                return Http::response(['success' => true, 'data' => [[
                    'uuid' => $deliveryUuid,
                    'status' => 'failed',
                    'http_status' => 500,
                ]]], 200);
            }

            if ($path === '/api/v1/operations/reconciliation/unmatched') {
                return Http::response(['success' => true, 'data' => [[
                    'uuid' => $unmatchedUuid,
                    'unmatched_reason' => 'callback_unlinked',
                    'status' => 'open',
                ]]], 200);
            }

            if ($path === '/api/v1/operations/treasury-alerts') {
                return Http::response(['success' => true, 'data' => [
                    'groups' => [
                        'critical' => [[
                            'uuid' => $alertUuid,
                            'title' => 'Settlement variance',
                            'severity' => 'critical',
                            'status' => 'open',
                            'created_at' => now()->toIso8601String(),
                        ]],
                    ],
                ]], 200);
            }

            if (str_starts_with($path, '/api/v1/operations/')) {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $response = $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Investigate'));

        $response->assertSee(route('settings.payments-gateway.operations-console.dead-letters.show', $deadLetterUuid, false));
        $response->assertSee(route('settings.payments-gateway.operations-console.callback-logs.show', $callbackUuid, false));
        $response->assertSee(route('settings.payments-gateway.operations-console.webhook-deliveries.show', $deliveryUuid, false));
        $response->assertSee(route('settings.payments-gateway.operations-console.treasury-alerts.show', $alertUuid, false));
        $response->assertSee(route('settings.payments-gateway.operations-console.unmatched-transactions.show', $unmatchedUuid, false));
    }

    public function test_investigation_action_buttons_require_manage_permission(): void
    {
        $uuid = (string) Str::uuid();

        Http::fake(function ($request) use ($uuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/dead-letters/'.$uuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $uuid,
                        'source_type' => 'queue_job',
                        'queue_name' => 'default',
                        'status' => 'pending',
                        'created_at' => now()->toIso8601String(),
                        'investigation' => $this->investigationBlock('dead_letter', $uuid, [
                            'recommended_next_actions' => ['replay_dead_letter', 'discard_dead_letter'],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console.dead-letters.show', $uuid))
            ->assertOk()
            ->assertSee(__('Remediation actions require payments_gateway.manage permission.'));

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.operations-console.dead-letters.show', $uuid))
            ->assertOk()
            ->assertSee(__('Replay dead letter'))
            ->assertSee(__('Discard dead letter'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function investigationBlock(string $incidentType, string $uuid, array $overrides = []): array
    {
        return array_merge([
            'incident_type' => $incidentType,
            'incident_uuid' => $uuid,
            'primary_record' => [
                'type' => $incidentType,
                'uuid' => $uuid,
            ],
            'tenant_impact' => [
                'tenant_uuid' => (string) Str::uuid(),
                'scoped' => true,
            ],
            'related_records' => [],
            'recommended_next_actions' => [],
            'risk_level' => 'medium',
        ], $overrides);
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
            'name' => 'Gateway Investigation Tester',
            'code' => 'gateway_investigation_'.uniqid(),
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
