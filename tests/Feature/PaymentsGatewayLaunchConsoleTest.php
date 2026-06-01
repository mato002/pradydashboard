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

class PaymentsGatewayLaunchConsoleTest extends TestCase
{
    use RefreshDatabase;

    private string $paybillUuid;

    private string $validationRunUuid;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments.pradytecai.com',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);

        $this->paybillUuid = (string) Str::uuid();
        $this->validationRunUuid = (string) Str::uuid();
    }

    public function test_launch_console_page_renders(): void
    {
        $this->fakeGateway($this->baseLaunchRoutes());

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console'))
            ->assertOk()
            ->assertSee(__('Launch Console'))
            ->assertSee(__('Gateway operational status'))
            ->assertSee(__('PayBill deployment readiness'))
            ->assertSee(__('Operational validation workspace'))
            ->assertSee(__('Validation run history'))
            ->assertSee(__('Incident escalation workspace'))
            ->assertSee(__('Live financial environment'));
    }

    public function test_launch_console_handles_gateway_unavailable(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console'))
            ->assertOk()
            ->assertSee(__('Launch console could not load because payments.pradytecai.com is unavailable.'));
    }

    public function test_paybill_readiness_renders_with_blocked_deployment_visibility(): void
    {
        $routes = $this->baseLaunchRoutes();
        $routes['GET /api/v1/paybill-accounts/'.$this->paybillUuid] = Http::response([
            'success' => true,
            'data' => [
                'uuid' => $this->paybillUuid,
                'account_name' => 'Launch PayBill',
                'supports_c2b' => true,
                'validation_url' => 'https://payments.pradytecai.com/api/v1/callbacks/c2b/validate',
                'confirmation_url' => 'https://payments.pradytecai.com/pay/c2b/confirm',
            ],
        ], 200);
        $routes['GET /api/v1/operations/production-readiness'] = Http::response([
            'success' => true,
            'data' => ['overall_status' => 'fail', 'daraja' => ['overall_status' => 'fail'], 'queue' => ['overall_status' => 'pass'], 'treasury' => ['overall_status' => 'warn']],
        ], 200);
        $routes['GET /api/v1/operations/go-live-dry-run/'.$this->paybillUuid] = Http::response([
            'success' => true,
            'data' => ['overall_status' => 'blocked', 'checklist_items' => []],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console', [
                'paybill_account_uuid' => $this->paybillUuid,
                'environment' => 'production',
            ]))
            ->assertOk()
            ->assertSee(__('Production blockers'))
            ->assertSee(__('Callback URL alignment'))
            ->assertSee(__('BLOCKED'));
    }

    public function test_validation_run_detail_renders_after_submission(): void
    {
        $routes = $this->baseLaunchRoutes();
        $routes['POST /api/v1/operations/validation-runs'] = Http::response([
            'success' => true,
            'data' => ['uuid' => $this->validationRunUuid],
        ], 201);
        $routes['GET /api/v1/operations/validation-runs/'.$this->validationRunUuid] = Http::response([
            'success' => true,
            'data' => [
                'uuid' => $this->validationRunUuid,
                'environment' => 'production',
                'paybill_account_uuid' => $this->paybillUuid,
                'overall_status' => 'pass',
                'strict_mode' => true,
                'duration_ms' => 1200,
                'stages' => [
                    ['key' => 'callback_ingestion', 'label' => 'Callback ingestion', 'status' => 'pass'],
                ],
            ],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.launch-console.validation-runs.store'), [
                'environment' => 'production',
                'paybill_account_uuid' => $this->paybillUuid,
                'stk_transaction_uuid' => (string) Str::uuid(),
                'strict_mode' => '1',
            ])
            ->assertRedirect(route('settings.payments-gateway.launch-console', [
                'paybill_account_uuid' => $this->paybillUuid,
                'environment' => 'production',
                'validation_run_uuid' => $this->validationRunUuid,
            ]))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/operations/validation-runs'));
    }

    public function test_operational_escalation_panel_renders_queue_incidents(): void
    {
        $routes = $this->baseLaunchRoutes();
        $routes['GET /api/v1/operations/queue/overview'] = Http::response([
            'success' => true,
            'data' => ['dead_letters' => 2, 'stuck_jobs' => 1, 'failed_jobs' => 3],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console.panel', ['panel' => 'incidents']))
            ->assertOk()
            ->assertSee(__('Queue incidents'))
            ->assertSee('Dead letters: 2', false);
    }

    public function test_readiness_severity_renders_on_operational_status_panel(): void
    {
        $routes = $this->baseLaunchRoutes();
        $routes['GET /api/v1/operations/readiness/status'] = Http::response([
            'success' => true,
            'data' => [
                'production_readiness_status' => 'fail',
                'blocking_issue_count' => 3,
            ],
        ], 200);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console.panel', ['panel' => 'operational-status']))
            ->assertOk()
            ->assertSee(__('Blocked go-live issues'))
            ->assertSee('3', false)
            ->assertSee(__('BLOCKED'));
    }

    public function test_validation_history_panel_handles_missing_api(): void
    {
        $routes = $this->baseLaunchRoutes();
        $routes['GET /api/v1/operations/validation-runs'] = Http::response(['success' => false], 404);

        $this->fakeGateway($routes);

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.launch-console.panel', ['panel' => 'validation-history']))
            ->assertOk()
            ->assertSee(__('Validation history API not available yet.'));
    }

    public function test_validation_run_requires_manage_permission(): void
    {
        Http::fake();

        $this->actingAs($this->paymentsGatewayViewer())
            ->post(route('settings.payments-gateway.launch-console.validation-runs.store'), [
                'environment' => 'production',
                'paybill_account_uuid' => $this->paybillUuid,
            ])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    /**
     * @return array<string, Response>
     */
    private function baseLaunchRoutes(): array
    {
        return [
            'GET /api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
            'GET /api/v1/operations/readiness/status' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/overview' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/webhooks/summary' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/callback-logs/summary' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/treasury-alerts' => Http::response(['success' => true, 'data' => ['groups' => [], 'counts_by_severity' => []]], 200),
            'GET /api/v1/operations/reconciliation/unmatched' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/workers' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/validation-runs' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/operations/queue/dead-letters' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/webhook-deliveries' => Http::response(['success' => true, 'data' => []], 200),
            'GET /api/v1/callback-logs' => Http::response(['success' => true, 'data' => []], 200),
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
            'name' => 'Launch Console Tester',
            'code' => 'launch_console_tester_'.uniqid(),
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
