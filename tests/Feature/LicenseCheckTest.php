<?php

namespace Tests\Feature;

use App\Models\LicenseCheckLog;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantAccessControl;
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
            'project_id' => $project->id,
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
            'project_id' => $project->id,
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
            'project_id' => $project->id,
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
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('tenant_status', 'restricted')
            ->assertJsonPath('access_level', 'read_only');
    }

    public function test_license_check_blocks_suspended_tenant(): void
    {
        $this->seed(LicenseModuleSeeder::class);
        [$project] = $this->createProduct('property');

        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
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
            ->assertJsonPath('access_level', 'blocked');
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
            'project_id' => $project->id,
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
