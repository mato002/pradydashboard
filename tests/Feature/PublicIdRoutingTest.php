<?php

namespace Tests\Feature;

use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Billing\BillingDocumentType;
use App\Support\PublicId\PublicIdFormat;
use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\DocumentTemplateSeeder;
use Tests\Concerns\CreatesBillableTenant;
use Tests\TestCase;

class PublicIdRoutingTest extends TestCase
{
    use CreatesBillableTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DocumentTemplateSeeder::class);
    }

    public function test_existing_records_have_public_id_after_creation(): void
    {
        $tenant = $this->makeTenant();

        $this->assertNotNull($tenant->fresh()->public_id);
        $this->assertValidPublicIdToken($tenant->public_id);
    }

    public function test_new_tenant_generates_token_public_id(): void
    {
        $tenant = $this->makeTenant();
        $tenant->update(['company_name' => 'Public ID Co']);

        $this->assertValidPublicIdToken($tenant->fresh()->public_id);
    }

    public function test_new_invoice_generates_token_public_id(): void
    {
        $tenant = $this->makeTenant();
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-TEST-001',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
        ]);

        $this->assertValidPublicIdToken($invoice->fresh()->public_id);
    }

    public function test_new_server_generates_token_public_id(): void
    {
        $server = Server::query()->create([
            'name' => 'Edge Node',
            'provider' => 'manual',
            'status' => 'active',
        ]);

        $this->assertValidPublicIdToken($server->fresh()->public_id);
    }

    public function test_new_payment_generates_token_public_id(): void
    {
        $tenant = $this->makeTenant();
        $payment = TenantPayment::query()->create([
            'tenant_id' => $tenant->id,
            'amount' => 500,
            'currency' => 'KES',
            'status' => 'successful',
            'source' => 'manual',
            'reconciliation_status' => 'unreconciled',
            'paid_at' => now(),
        ]);

        $this->assertValidPublicIdToken($payment->fresh()->public_id);
    }

    public function test_new_support_ticket_generates_token_public_id(): void
    {
        $tenant = $this->makeTenant();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'subject' => 'Login issue',
            'priority' => 'medium',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->assertValidPublicIdToken($ticket->fresh()->public_id);
    }

    public function test_tenant_show_works_with_public_id_url(): void
    {
        $user = $this->userWithPermissions(['tenants.view']);
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get('/tenants/'.$tenant->public_id)
            ->assertOk();
    }

    public function test_invoice_preview_works_with_public_id_url(): void
    {
        $user = $this->userWithPermissions(['invoices.view']);
        $tenant = $this->makeTenant();
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-PREVIEW-001',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'total' => 1000,
            'amount_due' => 1000,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get('/invoices/'.$invoice->public_id.'/preview')
            ->assertOk();
    }

    public function test_server_show_works_with_public_id_url(): void
    {
        $user = $this->userWithPermissions(['servers.view']);
        $server = Server::query()->create([
            'name' => 'Primary',
            'provider' => 'manual',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/servers/'.$server->public_id)
            ->assertOk();
    }

    public function test_support_ticket_show_works_with_public_id_url(): void
    {
        $user = $this->userWithPermissions(['support.tickets.view']);
        $tenant = $this->makeTenant();
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'subject' => 'Broken checkout',
            'priority' => 'high',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/support-tickets/'.$ticket->public_id)
            ->assertOk();
    }

    public function test_legacy_numeric_tenant_url_redirects_with_query_string(): void
    {
        $user = $this->userWithPermissions(['tenants.view']);
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get('/tenants/'.$tenant->id.'?tab=billing')
            ->assertRedirect('/tenants/'.$tenant->public_id.'?tab=billing');
    }

    public function test_invalid_public_id_returns_not_found(): void
    {
        $user = $this->userWithPermissions(['tenants.view']);

        $this->actingAs($user)
            ->get('/tenants/ZZnotfound')
            ->assertNotFound();
    }

    public function test_public_id_is_immutable_after_creation(): void
    {
        $tenant = $this->makeTenant();
        $original = $tenant->public_id;

        $tenant->update(['public_id' => 'HACKED99', 'company_name' => 'Renamed Co']);

        $this->assertSame($original, $tenant->fresh()->public_id);
    }

    public function test_signed_billing_url_with_public_id_is_valid(): void
    {
        $tenant = $this->makeTenant();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'billing.pay',
            now()->addHour(),
            ['tenant' => $tenant],
        );

        $this->assertStringContainsString($tenant->public_id, $url);

        $this->get($url)->assertOk();
    }

    public function test_legacy_signed_billing_url_with_numeric_id_still_validates(): void
    {
        $tenant = $this->makeTenant();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'billing.pay',
            now()->addHour(),
            ['tenant' => $tenant->id],
        );

        $this->get($url)->assertOk();
    }

    public function test_route_generation_works_with_column_restricted_eager_load(): void
    {
        $tenant = Tenant::query()->create([
            'hosted_project_id' => HostedProject::query()->create([
                'name' => 'Partial Load App',
                'domain' => 'partial.test',
            ])->id,
            'company_name' => 'Partial Load Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $partial = Tenant::query()->select(['id', 'public_id', 'company_name'])->findOrFail($tenant->id);
        $url = route('tenants.show', $partial);

        $this->assertStringContainsString($tenant->public_id, $url);
    }

    public function test_public_id_collision_retry_generates_unique_value(): void
    {
        $generated = collect(range(1, 8))->map(fn () => Tenant::generateUniquePublicId());

        $this->assertSame($generated->unique()->count(), $generated->count());
        $generated->each(fn (string $id) => $this->assertValidPublicIdToken($id));
    }

    public function test_legacy_numeric_tenant_url_redirects_to_public_id_url(): void
    {
        $user = $this->userWithPermissions(['tenants.view']);
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get('/tenants/'.$tenant->id)
            ->assertRedirect('/tenants/'.$tenant->public_id);
    }

    public function test_legacy_numeric_invoice_preview_redirects_to_public_id_url(): void
    {
        $user = $this->userWithPermissions(['invoices.view']);
        $tenant = $this->makeTenant();
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-LEGACY-001',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'total' => 500,
            'amount_due' => 500,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get('/invoices/'.$invoice->id.'/preview')
            ->assertRedirect('/invoices/'.$invoice->public_id.'/preview');
    }

    public function test_route_helper_generates_public_id_urls_for_key_resources(): void
    {
        $tenant = $this->makeTenant();
        $invoice = TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-LINK-001',
            'document_type' => BillingDocumentType::INVOICE,
            'currency' => 'KES',
            'total' => 100,
            'amount_due' => 100,
            'status' => 'draft',
        ]);
        $server = Server::query()->create([
            'name' => 'Link Server',
            'provider' => 'manual',
            'status' => 'active',
        ]);
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $tenant->id,
            'subject' => 'Route link test',
            'priority' => 'low',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $tenantUrl = route('tenants.show', $tenant);
        $invoiceUrl = route('invoices.preview', $invoice);
        $serverUrl = route('servers.show', $server);
        $ticketUrl = route('support-tickets.show', $ticket);

        $this->assertStringContainsString($tenant->public_id, $tenantUrl);
        $this->assertStringContainsString($invoice->public_id, $invoiceUrl);
        $this->assertStringContainsString($server->public_id, $serverUrl);
        $this->assertStringContainsString($ticket->public_id, $ticketUrl);
        $this->assertValidPublicIdToken($tenant->public_id);
        $this->assertValidPublicIdToken($invoice->public_id);
        $this->assertDoesNotMatchRegularExpression('#/tenants/'.$tenant->id.'($|\?)#', $tenantUrl);
        $this->assertDoesNotMatchRegularExpression('#/invoices/'.$invoice->id.'/#', $invoiceUrl);
    }

    public function test_unauthorized_user_cannot_access_tenant_by_public_id(): void
    {
        config(['rbac.legacy_open_access' => false]);

        $user = User::factory()->create();
        $tenant = $this->makeTenant();

        $this->actingAs($user)
            ->get('/tenants/'.$tenant->public_id)
            ->assertForbidden();
    }

    public function test_operational_document_download_requires_authorization(): void
    {
        config(['rbac.legacy_open_access' => false]);

        Storage::fake('local');
        $path = 'operational-documents/test/contract.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $tenant = $this->makeTenant();
        $document = OperationalDocument::query()->create([
            'tenant_id' => $tenant->id,
            'document_type' => 'contract',
            'title' => 'MSA',
            'file_path' => $path,
            'status' => 'active',
        ]);

        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('tenants.documents.download', [$tenant, $document]))
            ->assertForbidden();

        $authorized = $this->userWithPermissions(['tenants.view', 'tenants.update']);
        $this->actingAs($authorized)
            ->get(route('tenants.documents.download', [$tenant, $document]))
            ->assertOk();
    }

    public function test_api_license_check_route_is_unaffected_by_public_id_changes(): void
    {
        $this->postJson('/api/v1/license/check', [])
            ->assertStatus(401);
    }

    private function makeTenant(): Tenant
    {
        $project = HostedProject::query()->create([
            'name' => 'Public ID App',
            'domain' => 'public-id.test',
        ]);

        return Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Public ID Tenant',
            'tenant_currency' => 'KES',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
    }

    /**
     * @param  list<string>  $permissionCodes
     */
    private function userWithPermissions(array $permissionCodes): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'Public ID Tester',
            'code' => 'public_id_tester',
            'status' => 'active',
        ]);

        $permissionIds = collect($permissionCodes)->map(function (string $code) {
            return Permission::query()->firstOrCreate(
                ['code' => $code],
                ['name' => $code, 'group' => 'test'],
            )->id;
        })->all();

        $role->permissions()->sync($permissionIds);
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

        return $user;
    }

    private function assertValidPublicIdToken(string $publicId): void
    {
        $this->assertMatchesRegularExpression(PublicIdFormat::PATTERN, $publicId);
    }
}
