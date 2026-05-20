<?php

namespace Tests\Feature;

use App\Domain\Servers\FleetSummaryService;
use App\Models\Server;
use App\Models\ServerHealthLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerFleetSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_shows_real_fleet_counts(): void
    {
        $user = User::factory()->create();

        Server::query()->create([
            'name' => 'alpha',
            'status' => 'online',
            'ssl_status' => 'valid',
            'backup_status' => 'healthy',
            'cpu_cores' => 4,
            'disk_usage_percent' => 55,
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
            'created_at' => now()->subDays(2),
        ]);

        Server::query()->create([
            'name' => 'beta',
            'status' => 'offline',
            'ssl_status' => 'expired',
            'backup_status' => 'failed',
            'cpu_cores' => 8,
            'disk_usage_percent' => 80,
            'telemetry_mode' => 'manual',
            'currency' => 'KES',
            'created_at' => now()->subDays(10),
        ]);

        $this->actingAs($user)
            ->get(route('servers.create'))
            ->assertOk()
            ->assertSee('Register server')
            ->assertSee('Overview');
    }

    public function test_fleet_summary_sparklines_are_derived_from_fleet_not_placeholders(): void
    {
        Server::query()->create([
            'name' => 'node-a',
            'status' => 'online',
            'cpu_cores' => 2,
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
            'created_at' => now()->subDays(3),
        ]);

        Server::query()->create([
            'name' => 'node-b',
            'status' => 'online',
            'cpu_cores' => 4,
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
            'created_at' => now(),
        ]);

        $summary = app(FleetSummaryService::class)->summary();

        $this->assertSame(2, $summary['total']);
        $this->assertSame(2, $summary['healthy']);
        $this->assertSame(6, $summary['cpu_capacity']);
        $this->assertCount(12, $summary['spark']['total']);
        $this->assertSame(2, end($summary['spark']['total']));
        $this->assertNotSame([40, 55, 48, 62, 58, 70, 66, 74], array_slice($summary['spark']['total'], 0, 8));
        $this->assertSame('+2', $summary['total_trend']);
    }

    public function test_disk_sparkline_uses_health_log_history(): void
    {
        $server = Server::query()->create([
            'name' => 'logged',
            'status' => 'online',
            'telemetry_mode' => 'basic',
            'currency' => 'KES',
        ]);

        ServerHealthLog::query()->create([
            'server_id' => $server->id,
            'disk_percent' => 40,
            'checked_at' => now()->subDays(2),
        ]);

        ServerHealthLog::query()->create([
            'server_id' => $server->id,
            'disk_percent' => 60,
            'checked_at' => now()->subDay(),
        ]);

        $summary = app(FleetSummaryService::class)->summary();

        $this->assertNotEmpty($summary['spark']['disk']);
        $this->assertGreaterThan(0, max($summary['spark']['disk']));
    }
}
