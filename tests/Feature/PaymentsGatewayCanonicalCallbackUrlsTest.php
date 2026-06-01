<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\PaymentsGateway\CanonicalCallbackUrls;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Database\Seeders\RbacBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsGatewayCanonicalCallbackUrlsTest extends TestCase
{
    use RefreshDatabase;

    private string $gatewayTenantUuid;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rbac.legacy_open_access' => false,
            'payment_gateway.base_url' => 'https://payments-gateway.test',
            'payment_gateway.admin_token' => 'test-admin-token',
        ]);

        $this->seed(RbacBootstrapSeeder::class);
        $this->gatewayTenantUuid = (string) Str::uuid();
    }

    public function test_paybill_create_form_prefills_blank_callback_url_fields(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Create Form Co');
        $profileUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($profileUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $profileUuid,
                        'name' => 'Sandbox Profile',
                        'status' => 'active',
                    ],
                ], 200);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $canonical = CanonicalCallbackUrls::all();

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.tenants.paybill-accounts.create', [$tenant, $profileUuid]))
            ->assertOk()
            ->assertSee('value="'.$canonical['validation_url'].'"', false)
            ->assertSee('value="'.$canonical['confirmation_url'].'"', false)
            ->assertSee('value="'.$canonical['stk_callback_url'].'"', false)
            ->assertDontSee('payments.pradytecai.com/pay/', false);
    }

    public function test_paybill_edit_form_shows_legacy_url_warning(): void
    {
        $accountUuid = (string) Str::uuid();
        $profileUuid = (string) Str::uuid();
        $legacyUrl = 'https://payments-gateway.test/api/v1/callbacks/mpesa/stk';

        Http::fake(function ($request) use ($accountUuid, $profileUuid, $legacyUrl) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/paybill-accounts/'.$accountUuid => Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $accountUuid,
                        'payment_profile_uuid' => $profileUuid,
                        'account_name' => 'Legacy PayBill',
                        'account_code' => 'legacy-main',
                        'account_type' => 'collection',
                        'shortcode' => '600100',
                        'environment' => 'sandbox',
                        'status' => 'active',
                        'stk_callback_url' => $legacyUrl,
                    ],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid => Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $profileUuid,
                        'name' => 'Legacy Profile',
                        'status' => 'active',
                    ],
                ], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.paybill-accounts.edit', $accountUuid))
            ->assertOk()
            ->assertSee(__('One or more callback URLs use the legacy internal /api/v1/callbacks/* path. Safaricom must receive the public /pay/* URLs instead.'))
            ->assertSee($legacyUrl, false)
            ->assertSee(__('Use canonical URLs'));
    }

    public function test_paybill_edit_form_shows_use_canonical_urls_when_values_differ(): void
    {
        $accountUuid = (string) Str::uuid();
        $profileUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($accountUuid, $profileUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            return match (true) {
                $request->method() === 'GET' && $path === '/api/v1/paybill-accounts/'.$accountUuid => Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $accountUuid,
                        'payment_profile_uuid' => $profileUuid,
                        'account_name' => 'Mismatched PayBill',
                        'account_code' => 'mismatch-main',
                        'account_type' => 'collection',
                        'shortcode' => '600101',
                        'environment' => 'sandbox',
                        'status' => 'active',
                        'validation_url' => 'https://other.example.test/pay/c2b/validate',
                        'confirmation_url' => 'https://other.example.test/pay/c2b/confirm',
                    ],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid => Http::response([
                    'success' => true,
                    'data' => [
                        'uuid' => $profileUuid,
                        'name' => 'Mismatch Profile',
                        'status' => 'active',
                    ],
                ], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->get(route('settings.payments-gateway.paybill-accounts.edit', $accountUuid))
            ->assertOk()
            ->assertSee(__('Use canonical URLs'))
            ->assertSee('id="use-canonical-callback-urls"', false)
            ->assertSee('https://other.example.test/pay/c2b/validate', false);
    }

    public function test_treasury_mapping_marks_paybill_needing_url_update(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Callback Health Co');
        $profileUuid = (string) Str::uuid();
        $paybillUuid = (string) Str::uuid();

        Http::fake(function ($request) use ($tenant, $profileUuid, $paybillUuid) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';
            $gatewayTenantUuid = (string) $tenant->payments_gateway_tenant_uuid;

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
                        'account_name' => 'Legacy Callback PayBill',
                        'account_type' => 'collection',
                        'shortcode' => '600500',
                        'status' => 'active',
                        'supports_c2b' => true,
                        'validation_url' => 'https://payments-gateway.test/api/v1/callbacks/c2b/validate',
                        'confirmation_url' => 'https://payments-gateway.test/pay/c2b/confirm',
                    ]],
                ], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/webhook-endpoints' => Http::response(['success' => true, 'data' => []], 200),
                $request->method() === 'GET' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/api-keys' => Http::response(['success' => true, 'data' => []], 200),
                $request->method() === 'GET' && $path === '/api/v1/health' => Http::response(['success' => true, 'data' => ['status' => 'ok']], 200),
                default => Http::response(['success' => true, 'data' => []], 200),
            };
        });

        $canonical = CanonicalCallbackUrls::all();

        $this->actingAs($this->paymentsGatewayViewer())
            ->get(route('settings.payments-gateway.tenants.show', $tenant))
            ->assertOk()
            ->assertSee(__('Needs URL update'))
            ->assertSee(__('PayBill callback URL health'))
            ->assertSee($canonical['validation_url'], false)
            ->assertSee(__('Update callback URLs before go-live.'))
            ->assertSee(__('Legacy internal'));
    }

    public function test_paybill_store_submits_canonical_urls_when_fields_left_blank(): void
    {
        $tenant = $this->createLinkedDashboardTenant('Store Canonical Co');
        $profileUuid = (string) Str::uuid();
        $accountUuid = (string) Str::uuid();
        $canonical = CanonicalCallbackUrls::all();

        Http::fake(function ($request) use ($profileUuid, $accountUuid, $canonical) {
            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($request->method() === 'POST' && $path === '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts') {
                $payload = json_decode($request->body(), true) ?? [];

                return Http::response([
                    'success' => true,
                    'data' => array_merge([
                        'uuid' => $accountUuid,
                        'account_name' => 'Canonical PayBill',
                        'status' => 'active',
                    ], $payload),
                ], 201);
            }

            return Http::response(['success' => true, 'data' => []], 200);
        });

        $this->actingAs($this->paymentsGatewayManager())
            ->post(route('settings.payments-gateway.tenants.paybill-accounts.store', [$tenant, $profileUuid]), [
                'account_name' => 'Canonical PayBill',
                'account_code' => 'canonical-main',
                'account_type' => 'collection',
                'shortcode' => '600600',
                'environment' => 'sandbox',
            ])
            ->assertRedirect(route('settings.payments-gateway.tenants.show', $tenant));

        Http::assertSent(function ($request) use ($profileUuid, $canonical) {
            if ($request->method() !== 'POST') {
                return false;
            }

            $path = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($path !== '/api/v1/payment-profiles/'.$profileUuid.'/paybill-accounts') {
                return false;
            }

            $payload = json_decode($request->body(), true) ?? [];

            return ($payload['validation_url'] ?? null) === $canonical['validation_url']
                && ($payload['confirmation_url'] ?? null) === $canonical['confirmation_url']
                && ($payload['stk_callback_url'] ?? null) === $canonical['stk_callback_url'];
        });
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
            'hosted_project_id' => $project->id,
            'company_name' => $companyName,
            'tenant_domain' => Str::slug($companyName).'.test',
            'status' => 'active',
            'subscription_plan' => 'standard',
            'billing_cycle' => 'monthly',
        ]);
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
            'name' => 'Gateway Callback Tester',
            'code' => 'gateway_callback_tester_'.uniqid(),
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
