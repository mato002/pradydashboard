<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_tenant_create_page(): void
    {
        $user = User::factory()->create();
        Project::query()->create([
            'name' => 'Test Product',
            'domain' => 'test.example.com',
        ]);

        $this->actingAs($user)
            ->get(route('tenants.create'))
            ->assertOk()
            ->assertSee(__('Provision tenant'))
            ->assertSee(__('Who is this tenant?'))
            ->assertSee('phone_dial_code');
    }

    public function test_tenant_phone_rejects_invalid_length(): void
    {
        $user = User::factory()->create();
        $project = $this->createHostedProject();

        $this->actingAs($user)
            ->post(route('tenants.store'), [
                'project_id' => $project->id,
                'company_name' => 'Demo MFI',
                'tenant_currency' => 'KES',
                'billing_cycle' => 'monthly',
                'status' => 'trial',
                'country' => 'KE',
                'phone_dial_code' => '254',
                'phone_local' => '71234567890123456789',
            ])
            ->assertSessionHasErrors('phone_local');
    }

    public function test_tenant_phone_stores_e164_format(): void
    {
        $user = User::factory()->create();
        $project = $this->createHostedProject();

        $this->actingAs($user)
            ->post(route('tenants.store'), [
                'project_id' => $project->id,
                'company_name' => 'Demo MFI',
                'tenant_currency' => 'KES',
                'billing_cycle' => 'monthly',
                'status' => 'trial',
                'country' => 'KE',
                'phone_dial_code' => '254',
                'phone_local' => '712345678',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'company_name' => 'Demo MFI',
            'phone' => '+254712345678',
        ]);
    }

    private function createHostedProject(): Project
    {
        $product = Product::query()->create([
            'name' => 'MFI',
            'slug' => 'mfi',
            'status' => 'active',
        ]);

        return Project::query()->create([
            'name' => 'MFI',
            'domain' => 'mfi.test',
            'product_id' => $product->id,
        ]);
    }
}
