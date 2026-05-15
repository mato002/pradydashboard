<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_server_health_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('server-health.index'));

        $response->assertOk();
        $response->assertSee('Infrastructure Monitoring Center', false);
        $response->assertSee('Server health grid', false);
    }
}
