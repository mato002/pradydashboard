<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_access_controls_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('access-controls.index'));

        $response->assertOk();
        $response->assertSee('Security');
        $response->assertSee('Enforcement Center');
        $response->assertSee('Access policy registry');
    }
}
