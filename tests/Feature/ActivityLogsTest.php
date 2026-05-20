<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_activity_logs(): void
    {
        $this->get(route('activity-logs.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_activity_logs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee(__('Activity Log'))
            ->assertSee(__('Filter'));
    }

    public function test_authenticated_user_can_export_activity_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('activity-logs.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Timestamp', $response->streamedContent());
    }

    public function test_activity_logs_placeholder_route_removed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/modules/activity-logs')
            ->assertNotFound();
    }
}
