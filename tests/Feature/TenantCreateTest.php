<?php

namespace Tests\Feature;

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
            ->assertSee(__('Organization profile'));
    }
}
