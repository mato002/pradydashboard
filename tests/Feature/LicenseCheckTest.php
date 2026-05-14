<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tenant;
use Database\Seeders\LicenseModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_check_returns_full_access_for_active_tenant(): void
    {
        $this->seed(LicenseModuleSeeder::class);

        $project = Project::query()->create([
            'name' => 'Property',
            'domain' => 'property.test',
            'status' => 'active',
        ]);

        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'ABC Properties',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'renewal_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'tenant_key' => $tenant->external_key,
        ], [
            'Authorization' => 'Bearer '.$project->api_token,
        ]);

        $response->assertOk()
            ->assertJsonPath('tenant_status', 'active')
            ->assertJsonPath('subscription_status', 'paid')
            ->assertJsonPath('access_level', 'full');
    }

    public function test_license_check_returns_restricted_when_overdue(): void
    {
        $this->seed(LicenseModuleSeeder::class);

        $project = Project::query()->create([
            'name' => 'MFI',
            'domain' => 'mfi.test',
            'status' => 'active',
        ]);

        $tenant = Tenant::query()->create([
            'project_id' => $project->id,
            'company_name' => 'XYZ Microfinance',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'renewal_date' => now()->subDays(10)->toDateString(),
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'tenant_key' => $tenant->external_key,
        ], [
            'Authorization' => 'Bearer '.$project->api_token,
        ]);

        $response->assertOk()
            ->assertJsonPath('tenant_status', 'overdue')
            ->assertJsonPath('subscription_status', 'unpaid')
            ->assertJsonPath('access_level', 'restricted');
    }

    public function test_license_check_rejects_invalid_token(): void
    {
        $response = $this->postJson('/api/v1/license/check', [
            'tenant_key' => '00000000-0000-4000-8000-000000000000',
        ], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertUnauthorized();
    }
}
