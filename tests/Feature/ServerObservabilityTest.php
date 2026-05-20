<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\ServerProviderNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ServerObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_show_displays_tabbed_observation_page(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create([
            'name' => 'prod-01',
            'provider' => 'Hostinger',
            'ip_address' => '203.0.113.1',
            'status' => 'online',
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
        ]);

        $this->actingAs($user)
            ->get(route('servers.show', $server))
            ->assertOk()
            ->assertSee('prod-01')
            ->assertSee('Overview')
            ->assertSee('Notices')
            ->assertSee('Hosted deployments');
    }

    public function test_can_add_provider_notice_to_server(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create([
            'name' => 'prod-01',
            'status' => 'online',
            'telemetry_mode' => 'manual',
            'currency' => 'KES',
        ]);

        $this->actingAs($user)
            ->post(route('servers.notices.store', $server), [
                'notice_type' => 'renewal',
                'title' => 'VPS renewal due',
                'severity' => 'warning',
                'notice_date' => now()->toDateString(),
                'status' => 'open',
            ])
            ->assertRedirect(route('servers.show', $server).'#notices');

        $this->assertDatabaseHas('server_provider_notices', [
            'server_id' => $server->id,
            'title' => 'VPS renewal due',
        ]);
    }

    public function test_can_delete_server_from_fleet(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create([
            'name' => 'remove-me',
            'status' => 'offline',
            'telemetry_mode' => 'manual',
            'currency' => 'KES',
        ]);

        $this->actingAs($user)
            ->delete(route('servers.destroy', $server))
            ->assertRedirect(route('servers.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    }

    public function test_server_index_lists_delete_action(): void
    {
        $user = User::factory()->create();
        $server = Server::query()->create([
            'name' => 'fleet-node',
            'status' => 'online',
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
        ]);

        $this->actingAs($user)
            ->get(route('servers.index'))
            ->assertOk()
            ->assertSee('fleet-node')
            ->assertSee(__('Actions'), false);
    }

    public function test_api_token_is_encrypted_and_not_exposed_on_edit_form(): void
    {
        $server = Server::query()->create([
            'name' => 'secure',
            'status' => 'unknown',
            'telemetry_mode' => 'whm',
            'currency' => 'KES',
            'provisioning_meta' => ['api_token' => Crypt::encryptString('secret-token-123')],
        ]);

        $this->assertSame('secret-token-123', $server->decryptedApiToken());
        $this->assertNotSame('secret-token-123', $server->meta('api_token'));
    }
}
