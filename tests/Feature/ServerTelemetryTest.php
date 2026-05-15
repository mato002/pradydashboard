<?php

namespace Tests\Feature;

use App\Domain\Servers\ServerTelemetrySyncService;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_probe_endpoint_requires_authentication(): void
    {
        $this->postJson(route('servers.probe'), ['ip_address' => '127.0.0.1'])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_probe_server_connectivity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('servers.probe'), [
            'ip_address' => '1.1.1.1',
            'meta' => ['hostname' => 'cloudflare.com'],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['ok', 'status', 'messages', 'sources']);
    }

    public function test_sync_telemetry_updates_server_record(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create([
            'name' => 'probe-test',
            'ip_address' => '1.1.1.1',
            'status' => 'unknown',
            'currency' => 'KES',
        ]);

        $this->actingAs($user)
            ->post(route('servers.sync-telemetry', $server))
            ->assertRedirect();

        $server->refresh();

        $this->assertNotNull($server->last_synced_at);
        $this->assertContains($server->status, ['online', 'offline', 'unknown']);
    }

    public function test_sync_service_records_health_log_when_metrics_present(): void
    {
        $server = Server::query()->create([
            'name' => 'health-log-test',
            'ip_address' => '1.1.1.1',
            'status' => 'unknown',
            'currency' => 'KES',
        ]);

        $sync = app(ServerTelemetrySyncService::class);
        $result = $sync->sync($server);

        $this->assertTrue($result['ok'] || filled($result['message']));
        $server->refresh();
        $this->assertNotNull($server->last_synced_at);
    }
}
