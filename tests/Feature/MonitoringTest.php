<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_monitoring_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('monitoring.index'));

        $response->assertOk();
        $response->assertSee('Observability', false);
        $response->assertSee('Alert center', false);
        $response->assertSee('API monitoring', false);
        $response->assertSee('Escalation policies', false);
    }

    public function test_overview_dashboard_shows_monitoring_section_with_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Monitoring & queues');
        $response->assertSee('Redis & Queues');
        $response->assertSee(route('monitoring.queues'), false);
        $response->assertSee(route('monitoring.index'), false);
    }
}
