<?php

namespace Tests\Feature;

use App\Models\LicenseCheckLog;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantAccessControl;
use App\Models\TenantInvoice;
use Database\Seeders\LicenseModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseCheckTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(string $slug = 'property'): array
    {
        $project = Project::query()->create([
            'name' => ucfirst($slug),
            'domain' => $slug.'.pradytecai.test',
            'product_slug' => $slug,
            'product_key' => $slug,
            'status' => 'active',
        ]);

        return [$project];
    }

    private function signedHeaders(Project $project, Tenant $tenant, array $body): array
    {
        $json = json_encode($body);

        return [
            'Authorization' => 'Bearer '.$project->api_token,
            'X-Prady-Signature' => hash_hmac('sha256', $json, $tenant->license_secret),
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    public function test_license_check_grants_full_access_for_active_tenant(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        [$project] = $this->createProduct('property');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'ABC Properties',
            'tenant_key' => 'abc-properties',
            'tenant_domain' => 'property.pradytecai.test',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'renewal_date' => now()->addMonth()->toDateString(),
        ]);

        $body = [
            'tenant_key' => 'abc-properties',
            'product_key' => 'property',
            'domain' => 'property.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('tenant_status', 'active')
            ->assertJsonPath('access_level', 'full')
            ->assertJsonPath('message', 'Access granted');

        $this->assertDatabaseHas('license_check_logs', [
            'tenant_key' => 'abc-properties',
            'product_key' => 'property',
            'allowed' => true,
        ]);
    }

    public function test_license_check_returns_warning_when_overdue_in_grace(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        [$project] = $this->createProduct('mfi');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'XYZ Microfinance',
            'tenant_key' => 'xyz-mfi',
            'tenant_domain' => 'mfi.pradytecai.test',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'renewal_date' => now()->subDays(3)->toDateString(),
            'grace_days' => 7,
        ]);

        $body = [
            'tenant_key' => 'xyz-mfi',
            'product_key' => 'mfi',
            'domain' => 'mfi.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('access_level', 'warning');
    }

    public function test_license_check_returns_read_only_when_restricted(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        [$project] = $this->createProduct('crm');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Restricted Co',
            'tenant_key' => 'restricted-co',
            'tenant_domain' => 'crm.pradytecai.test',
            'status' => 'active',
            'renewal_date' => now()->addMonth()->toDateString(),
        ]);

        TenantAccessControl::query()->create([
            'tenant_id' => $tenant->id,
            'level' => 'restricted',
            'restrict_login' => false,
        ]);

        $body = [
            'tenant_key' => 'restricted-co',
            'product_key' => 'crm',
            'domain' => 'crm.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('tenant_status', 'restricted')
            ->assertJsonPath('access_level', 'read_only');
    }

    public function test_license_check_blocks_suspended_tenant(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        [$project] = $this->createProduct('property');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Suspended Co',
            'tenant_key' => 'suspended-co',
            'tenant_domain' => 'property.pradytecai.test',
            'status' => 'suspended',
        ]);

        $body = [
            'tenant_key' => 'suspended-co',
            'product_key' => 'property',
            'domain' => 'property.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('tenant_status', 'suspended')
            ->assertJsonPath('access_level', 'blocked')
            ->assertJsonStructure([
                'billing' => [
                    'amount_due',
                    'payment_instructions',
                    'actions',
                ],
            ]);
    }

    public function test_license_check_includes_pay_actions_when_invoice_overdue(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        Setting::setJson('platform.billing', [
            'default_currency' => 'KES',
            'company_legal_name' => 'PradytecAI Ltd',
            'payment_instructions' => 'M-Pesa Paybill 123456. Account: invoice number.',
        ]);

        [$project] = $this->createProduct('mfi');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Overdue MFI',
            'tenant_key' => 'overdue-mfi',
            'tenant_domain' => 'mfi.pradytecai.test',
            'billing_cycle' => 'monthly',
            'status' => 'overdue',
            'tenant_currency' => 'KES',
        ]);

        TenantInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'document_type' => 'invoice',
            'invoice_number' => 'INV-TEST-001',
            'status' => 'overdue',
            'currency' => 'KES',
            'subtotal' => 15000,
            'tax_amount' => 0,
            'total' => 15000,
            'amount_paid' => 0,
            'due_date' => now()->subDays(5),
            'issue_date' => now()->subDays(35),
        ]);

        $body = [
            'tenant_key' => 'overdue-mfi',
            'product_key' => 'mfi',
            'domain' => 'mfi.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('billing.amount_due', 15000)
            ->assertJsonPath('billing.invoice_number', 'INV-TEST-001');

        $actions = $response->json('billing.actions');
        $this->assertNotEmpty($actions);
        $payAction = collect($actions)->firstWhere('type', 'pay');
        $this->assertNotNull($payAction);
        $this->assertStringContainsString('/billing/pay/', $payAction['href']);
        $this->assertTrue($payAction['primary']);
    }

    public function test_license_check_uses_subscription_fee_when_no_open_invoice_balance(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        Setting::setJson('platform.billing', [
            'default_currency' => 'KES',
            'company_legal_name' => 'PradytecAI Ltd',
        ]);

        [$project] = $this->createProduct('mfi');

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Matech MFI',
            'tenant_key' => 'matech-mfi',
            'tenant_domain' => 'mfi.pradytecai.test',
            'status' => 'active',
            'tenant_currency' => 'KES',
            'subscription_amount' => 25000,
        ]);

        TenantAccessControl::query()->create([
            'tenant_id' => $tenant->id,
            'level' => 'restricted',
            'restrict_login' => false,
        ]);

        $body = [
            'tenant_key' => 'matech-mfi',
            'product_key' => 'mfi',
            'domain' => 'mfi.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, $this->signedHeaders($project, $tenant, $body));

        $response->assertOk()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('billing.amount_due', 25000);
    }

    public function test_license_check_rejects_invalid_token(): void
    {
        $response = $this->postJson('/api/v1/license/check', [
            'tenant_key' => 'x',
            'product_key' => 'property',
            'domain' => 'example.com',
        ], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertUnauthorized();
    }

    public function test_license_check_requires_valid_signature_when_secret_set(): void
    {
        [$project] = $this->createProduct();

        $tenant = Tenant::query()->create([
            'hosted_project_id' => $project->id,
            'company_name' => 'Sig Test',
            'tenant_key' => 'sig-test',
            'tenant_domain' => 'property.pradytecai.test',
        ]);

        $body = [
            'tenant_key' => 'sig-test',
            'product_key' => 'property',
            'domain' => 'property.pradytecai.test',
        ];

        $response = $this->postJson('/api/v1/license/check', $body, [
            'Authorization' => 'Bearer '.$project->api_token,
        ]);

        $response->assertUnauthorized();
    }
}
