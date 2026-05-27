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

class PaymentsGatewayOperationsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private string $transactionUuid;

    private string $callbackUuid;

    private string $webhookEventUuid;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments.pradytecai.com',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);

        $this->transactionUuid = (string) Str::uuid();
        $this->callbackUuid = (string) Str::uuid();
        $this->webhookEventUuid = (string) Str::uuid();
    }

    public function test_operations_console_page_renders(): void
    {
        $this->fakeGateway($this->baseConsoleRoutes());

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Operations Console'))
            ->assertSee(__('Live Transactions'))
            ->assertSee(__('Callback Health'))
            ->assertSee(__('Webhook Health'))
            ->assertSee(__('Queue Health'))
            ->assertSee(__('Reconciliation Snapshot'))
            ->assertSee(__('Treasury Alerts'))
            ->assertSee(__('Go-Live & Readiness'));
    }

    public function test_operations_console_handles_gateway_unavailable(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Operations console could not load because payments.pradytecai.com is unavailable.'));
    }

    public function test_operations_console_shows_missing_api_notices(): void
    {
        $routes = $this->baseConsoleRoutes();

        foreach ([
            'GET /api/v1/operations/transactions/summary',
            'GET /api/v1/operations/callback-logs/summary',
            'GET /api/v1/operations/webhooks/summary',
            'GET /api/v1/operations/queue/overview',
            'GET /api/v1/operations/reconciliation/runs',
            'GET /api/v1/operations/reconciliation/unmatched',
            'GET /api/v1/operations/treasury-alerts',
            'GET /api/v1/operations/readiness/status',
        ] as $endpoint) {
            $routes[$endpoint] = Http::response(['success' => false, 'message' => 'Not found'], 404);
        }

        $this->fakeGateway($routes);

        $response = $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Some gateway operations APIs are not available yet'));

        foreach ([
            'GET /api/v1/operations/transactions/summary',
            'GET /api/v1/operations/queue/overview',
            'GET /api/v1/operations/treasury-alerts',
        ] as $endpoint) {
            $response->assertSee($endpoint);
        }
    }

    public function test_operations_console_renders_sections_when_api_fakes_are_provided(): void
    {
        $routes = $this->baseConsoleRoutes();

        $routes['GET /api/v1/operations/transactions/summary'] = Http::response([
            'success' => true,
            'data' => [
                'status_counts' => [
                    'pending' => 2,
                    'processing' => 1,
                    'success' => 10,
                    'failed' => 3,
                    'timeout' => 1,
                ],
            ],
        ], 200);

        $routes['GET /api/v1/operations/callback-logs/summary'] = Http::response([
            'success' => true,
            'data' => [
                'failed' => 4,
                'duplicate' => 2,
                'unmatched' => 1,
                'malformed' => 0,
            ],
        ], 200);

        $routes['GET /api/v1/operations/webhooks/summary'] = Http::response([
            'success' => true,
            'data' => [
                'pending_events' => 5,
                'failed_events' => 2,
                'failed_deliveries' => 3,
            ],
        ], 200);

        $routes['GET /api/v1/operations/queue/overview'] = Http::response([
            'success' => true,
            'data' => [
                'worker_status' => 'healthy',
                'dead_letters' => 0,
                'stuck_jobs' => 1,
                'failed_jobs' => 2,
            ],
        ], 200);

        $routes['GET /api/v1/transactions'] = Http::response([
            'success' => true,
            'data' => [[
                'uuid' => $this->transactionUuid,
                'transaction_type' => 'stk',
                'amount' => 1500,
                'currency' => 'KES',
                'status' => 'success',
                'created_at' => now()->toIso8601String(),
            ]],
        ], 200);

        $routes['GET /api/v1/callback-logs'] = function ($request) {
            $query = [];
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);

            if (($query['processing_status'] ?? null) === 'failed') {
                return Http::response([
                    'success' => true,
                    'data' => [[
                        'uuid' => $this->callbackUuid,
                        'callback_type' => 'c2b',
                        'processing_status' => 'failed',
                    ]],
                ], 200);
            }

            return Http::response([
                'success' => true,
                'data' => [[
                    'uuid' => $this->callbackUuid,
                    'callback_type' => 'stk',
                    'processing_status' => 'success',
                ]],
            ], 200);
        };

        $routes['GET /api/v1/webhook-events'] = Http::response([
            'success' => true,
            'data' => [[
                'uuid' => $this->webhookEventUuid,
                'event_type' => 'payment.success',
                'status' => 'pending',
            ]],
        ], 200);

        $routes['GET /api/v1/webhook-deliveries'] = Http::response([
            'success' => true,
            'data' => [[
                'uuid' => (string) Str::uuid(),
                'http_status' => 500,
                'status' => 'failed',
            ]],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee('10')
            ->assertSee('4')
            ->assertSee('healthy')
            ->assertSee('STK')
            ->assertSee(substr($this->transactionUuid, 0, 8))
            ->assertSee(substr($this->callbackUuid, 0, 8))
            ->assertSee('payment.success');
    }

    public function test_operations_console_renders_incident_panels_and_health_banner(): void
    {
        $routes = $this->baseConsoleRoutes();

        $routes['GET /api/v1/operations/webhooks/summary'] = Http::response([
            'success' => true,
            'data' => ['failed_deliveries' => 2, 'failed_events' => 1, 'pending_events' => 0],
        ], 200);

        $routes['GET /api/v1/operations/queue/overview'] = Http::response([
            'success' => true,
            'data' => [
                'worker_status' => 'degraded',
                'dead_letters' => 1,
                'stuck_jobs' => 0,
                'failed_jobs' => 0,
                'active_workers' => 1,
                'stale_workers' => 1,
            ],
        ], 200);

        $routes['GET /api/v1/operations/treasury-alerts'] = Http::response([
            'success' => true,
            'data' => [
                'groups' => [
                    'critical' => [[
                        'uuid' => (string) Str::uuid(),
                        'title' => 'Critical settlement variance',
                        'severity' => 'critical',
                        'status' => 'open',
                        'created_at' => now()->toIso8601String(),
                    ]],
                ],
                'counts_by_severity' => ['critical' => 1],
            ],
        ], 200);

        $routes['GET /api/v1/operations/queue/dead-letters'] = Http::response([
            'success' => true,
            'data' => [[
                'uuid' => (string) Str::uuid(),
                'source_type' => 'webhook_event',
                'created_at' => now()->toIso8601String(),
            ]],
        ], 200);

        $routes['GET /api/v1/operations/queue/workers'] = Http::response([
            'success' => true,
            'data' => [[
                'worker_name' => 'worker-01',
                'status' => 'active',
                'last_heartbeat_at' => now()->subMinutes(2)->toIso8601String(),
            ]],
        ], 200);

        $routes['GET /api/v1/operations/readiness/status'] = Http::response([
            'success' => true,
            'data' => [
                'production_readiness_status' => 'warn',
                'last_run_at' => now()->toIso8601String(),
            ],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Operations health'))
            ->assertSee(__('Incident panels'))
            ->assertSee(__('Live activity stream'))
            ->assertSee(__('Queue workers'))
            ->assertSee(__('Reconciliation urgency'))
            ->assertSee(__('Failed webhooks'))
            ->assertSee(__('Critical settlement variance'));
    }

    public function test_operations_console_health_banner_shows_warning_state_for_failed_webhooks(): void
    {
        $routes = $this->baseConsoleRoutes();
        $routes['GET /api/v1/operations/webhooks/summary'] = Http::response([
            'success' => true,
            'data' => ['failed_deliveries' => 3, 'failed_events' => 0, 'pending_events' => 0],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Failed webhooks'))
            ->assertSee('3');
    }

    public function test_operations_console_shows_quick_action_unavailable_placeholder_when_dead_letter_api_missing(): void
    {
        $routes = $this->baseConsoleRoutes();
        $routes['GET /api/v1/operations/queue/dead-letters'] = Http::response(['success' => false], 404);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Operation API not available yet.'));
    }

    public function test_operations_console_shows_operational_empty_states(): void
    {
        $this->fakeGateway($this->baseConsoleRoutes());

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('No failed webhook deliveries'))
            ->assertSee(__('No unresolved treasury alerts'))
            ->assertSee(__('No worker heartbeats reported yet.'));
    }

    public function test_operations_console_requires_view_permission(): void
    {
        $user = User::factory()->create();

        $this->get(route('settings.payments-gateway.operations-console'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertForbidden();
    }

    public function test_operations_console_does_not_show_dead_letter_placeholder_when_api_available(): void
    {
        $routes = $this->baseConsoleRoutes();
        $routes['GET /api/v1/operations/queue/dead-letters'] = Http::response([
            'success' => true,
            'data' => [[
                'uuid' => (string) Str::uuid(),
                'source_type' => 'callback_log',
                'queue_name' => 'callbacks',
                'status' => 'pending',
                'created_at' => now()->toIso8601String(),
            ]],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee('callbacks')
            ->assertDontSee(__('Operation API not available yet.'));
    }

    public function test_operations_console_renders_worker_fields_from_gateway(): void
    {
        $routes = $this->baseConsoleRoutes();
        $routes['GET /api/v1/operations/queue/workers'] = Http::response([
            'success' => true,
            'data' => [[
                'worker_name' => 'worker-east-1',
                'status' => 'active',
                'queue_names' => ['webhooks', 'callbacks'],
                'last_seen_at' => now()->subSeconds(45)->toIso8601String(),
                'age_seconds' => 45,
            ]],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee('worker-east-1')
            ->assertSee('webhooks, callbacks')
            ->assertSee('45')
            ->assertDontSee(__('Operation API not available yet.'));
    }

    public function test_operations_console_shows_worker_placeholder_when_workers_api_missing(): void
    {
        $routes = $this->baseConsoleRoutes();
        $routes['GET /api/v1/operations/queue/workers'] = Http::response(['success' => false], 404);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.operations-console'))
            ->assertOk()
            ->assertSee(__('Operation API not available yet.'));
    }

    public function test_replay_dead_letter_action_calls_gateway(): void
    {
        $deadLetterUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($deadLetterUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if ($key === 'POST /api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/replay') {
                return Http::response(['success' => true, 'data' => ['uuid' => $deadLetterUuid]], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.dead-letters.replay', $deadLetterUuid))
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/replay'));
    }

    public function test_discard_dead_letter_action_calls_gateway(): void
    {
        $deadLetterUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($deadLetterUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if ($key === 'POST /api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/discard') {
                return Http::response(['success' => true, 'data' => ['uuid' => $deadLetterUuid]], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.dead-letters.discard', $deadLetterUuid))
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/discard'));
    }

    public function test_retry_callback_action_calls_gateway(): void
    {
        $callbackLogUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($callbackLogUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if ($key === 'POST /api/v1/operations/queue/callbacks/'.$callbackLogUuid.'/retry') {
                return Http::response(['success' => true, 'data' => ['uuid' => $callbackLogUuid]], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.callback-logs.retry', $callbackLogUuid))
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/callbacks/'.$callbackLogUuid.'/retry'));
    }

    public function test_acknowledge_alert_action_calls_gateway(): void
    {
        $alertUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($alertUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if ($key === 'POST /api/v1/treasury/alerts/'.$alertUuid.'/acknowledge') {
                return Http::response(['success' => true, 'data' => ['uuid' => $alertUuid]], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.treasury-alerts.acknowledge', $alertUuid), [
                'comments' => 'Investigating variance',
            ])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($alertUuid): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/treasury/alerts/'.$alertUuid.'/acknowledge')) {
                return false;
            }

            return ($request->data()['comments'] ?? null) === 'Investigating variance';
        });
    }

    public function test_resolve_alert_action_calls_gateway(): void
    {
        $alertUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($alertUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if ($key === 'POST /api/v1/treasury/alerts/'.$alertUuid.'/resolve') {
                return Http::response(['success' => true, 'data' => ['uuid' => $alertUuid]], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.treasury-alerts.resolve', $alertUuid), [
                'comments' => 'Variance cleared',
            ])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($alertUuid): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/treasury/alerts/'.$alertUuid.'/resolve')) {
                return false;
            }

            return ($request->data()['comments'] ?? null) === 'Variance cleared';
        });
    }

    public function test_replay_dead_letter_action_shows_placeholder_flash_on_gateway_404(): void
    {
        $deadLetterUuid = (string) Str::uuid();

        Http::fake([
            '*' => Http::response(['success' => false], 404),
        ]);

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.dead-letters.replay', $deadLetterUuid))
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('gateway_error', __('Operation API not available yet.'));
    }

    public function test_bulk_replay_dead_letters_calls_gateway_per_uuid(): void
    {
        $firstUuid = (string) Str::uuid();
        $secondUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($firstUuid, $secondUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/dead-letters/'.$firstUuid.'/replay'
                || $path === '/api/v1/operations/queue/dead-letters/'.$secondUuid.'/replay') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('dead_letters.replay', [$firstUuid, $secondUuid])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status', __('Bulk action completed: :succeeded succeeded, :failed failed.', [
                'succeeded' => 2,
                'failed' => 0,
            ]));

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/dead-letters/'.$firstUuid.'/replay'));
        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/dead-letters/'.$secondUuid.'/replay'));
    }

    public function test_bulk_discard_dead_letters_calls_gateway_per_uuid(): void
    {
        $deadLetterUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($deadLetterUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/discard') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('dead_letters.discard', [$deadLetterUuid])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/dead-letters/'.$deadLetterUuid.'/discard'));
    }

    public function test_bulk_retry_callbacks_calls_gateway_per_uuid(): void
    {
        $callbackUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($callbackUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/callbacks/'.$callbackUuid.'/retry') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('callbacks.retry', [$callbackUuid])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/queue/callbacks/'.$callbackUuid.'/retry'));
    }

    public function test_bulk_acknowledge_alerts_calls_gateway_with_comments(): void
    {
        $alertUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($alertUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/treasury/alerts/'.$alertUuid.'/acknowledge') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('alerts.acknowledge', [$alertUuid], 'Investigating in bulk')
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($alertUuid): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/api/v1/treasury/alerts/'.$alertUuid.'/acknowledge')) {
                return false;
            }

            return ($request->data()['comments'] ?? null) === 'Investigating in bulk';
        });
    }

    public function test_bulk_resolve_alerts_calls_gateway(): void
    {
        $alertUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($alertUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/treasury/alerts/'.$alertUuid.'/resolve') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('alerts.resolve', [$alertUuid], 'Resolved in bulk')
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/treasury/alerts/'.$alertUuid.'/resolve'));
    }

    public function test_bulk_redispatch_webhook_deliveries_calls_gateway(): void
    {
        $deliveryUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($deliveryUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/webhook-deliveries/'.$deliveryUuid.'/redispatch') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('webhook_deliveries.redispatch', [$deliveryUuid])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/webhook-deliveries/'.$deliveryUuid.'/redispatch'));
    }

    public function test_bulk_action_reports_partial_failure_summary(): void
    {
        $successUuid = (string) Str::uuid();
        $failureUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($successUuid, $failureUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path === '/api/v1/operations/queue/dead-letters/'.$successUuid.'/replay') {
                return Http::response(['success' => true, 'data' => []], 200);
            }

            if ($path === '/api/v1/operations/queue/dead-letters/'.$failureUuid.'/replay') {
                return Http::response(['success' => false, 'message' => 'Dead letter not found'], 404);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->postBulkAction('dead_letters.replay', [$successUuid, $failureUuid])
            ->assertRedirect(route('settings.payments-gateway.operations-console'))
            ->assertSessionHas('status', __('Bulk action completed: :succeeded succeeded, :failed failed.', [
                'succeeded' => 1,
                'failed' => 1,
            ]))
            ->assertSessionHas('bulk_action_errors', [
                ['uuid' => $failureUuid, 'message' => 'Dead letter not found'],
            ]);
    }

    public function test_bulk_action_requires_manage_permission(): void
    {
        $this->actingAs($this->paymentsGatewayViewer())
            ->post(route('settings.payments-gateway.operations-console.bulk-action'), [
                'action' => 'dead_letters.replay',
                'uuids' => [(string) Str::uuid()],
            ])
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $uuids
     */
    private function postBulkAction(string $action, array $uuids, ?string $comments = null)
    {
        $payload = [
            'action' => $action,
            'uuids' => $uuids,
        ];

        if ($comments !== null) {
            $payload['comments'] = $comments;
        }

        return $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.operations-console.bulk-action'), $payload);
    }

    /**
     * @return array<string, Response>
     */
    private function baseConsoleRoutes(): array
    {
        return [
            'GET /api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
            'GET /api/v1/operations/transactions/summary' => Http::response(['success' => true, 'data' => ['status_counts' => []]], 200),
            'GET /api/v1/operations/callback-logs/summary' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/webhooks/summary' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/overview' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/reconciliation/runs' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/reconciliation/unmatched' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/treasury-alerts' => Http::response(['success' => true, 'data' => ['groups' => [], 'counts_by_severity' => []]], 200),
            'GET /api/v1/operations/readiness/status' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/dead-letters' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/workers' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/transactions' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/callback-logs' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/webhook-events' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/webhook-deliveries' => Http::response(['success' => true, 'data' => []], 200),
        ];
    }

    /**
     * @param  array<string, Response|\Closure>  $routes
     */
    private function fakeGateway(array $routes): void
    {
        Http::fake(function ($request) use ($routes) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $key = $request->method().' '.$path;

            if (isset($routes[$key])) {
                $handler = $routes[$key];

                return is_callable($handler) ? $handler($request) : $handler;
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
            'name' => 'Gateway Ops Viewer',
            'code' => 'gateway_ops_viewer_'.uniqid(),
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
