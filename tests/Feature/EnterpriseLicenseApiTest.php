<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tenant;
use Database\Seeders\LicenseModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseLicenseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_enterprise_license_check_validates_product_and_domain(): void
    {
        $this->seed(LicenseModuleSeeder::class);

        $project = Project::query()->create([
            'name' => 'Property',
            'domain' => 'property.pradytecai.com',
            'product_slug' => 'property_management',
            'status' => 'active',
        ]);

        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'ABC Properties',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'renewal_date' => now()->addMonth()->toDateString(),
            'tenant_domain' => 'abc.property.pradytecai.com',
        ]);

        $this->postJson('/api/license/check', [
            'tenant_id' => $tenant->id,
            'domain' => 'abc.property.pradytecai.com',
            'product' => 'property_management',
        ], [
            'Authorization' => 'Bearer '.$project->api_token,
        ])
            ->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('access_level', 'full');
    }

    public function test_enterprise_license_rejects_product_mismatch(): void
    {
        $project = Project::query()->create([
            'name' => 'Property',
            'domain' => 'property.pradytecai.com',
            'product_slug' => 'property_management',
            'status' => 'active',
        ]);

        $this->postJson('/api/license/check', [
            'tenant_id' => 1,
            'domain' => 'x',
            'product' => 'wrong_product',
        ], [
            'Authorization' => 'Bearer '.$project->api_token,
        ])
            ->assertStatus(403);
    }
}
