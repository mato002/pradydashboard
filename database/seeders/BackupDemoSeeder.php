<?php

namespace Database\Seeders;

use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BackupDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Backup::query()->exists()) {
            return;
        }

        $servers = Server::query()->get();
        $tenants = Tenant::query()->with('server', 'project')->get();
        $projects = Project::query()->get();

        $this->seedSchedules($servers, $tenants);
        $this->seedJobs($servers, $tenants, $projects);
    }

    private function seedSchedules($servers, $tenants): void
    {
        $templates = [
            ['name' => 'Fleet nightly full', 'schedule_type' => 'daily', 'cron' => '0 2 * * *', 'retention' => '14 days', 'offset_hours' => 6],
            ['name' => 'DB incremental — primary', 'schedule_type' => 'incremental', 'cron' => '0 */4 * * *', 'retention' => '7 days', 'offset_hours' => 2],
            ['name' => 'Weekly snapshot — edge', 'schedule_type' => 'weekly', 'cron' => '0 3 * * 0', 'retention' => '8 weeks', 'offset_hours' => 48],
            ['name' => 'Monthly compliance archive', 'schedule_type' => 'monthly', 'cron' => '0 4 1 * *', 'retention' => '12 months', 'offset_hours' => 120],
            ['name' => 'Tenant vault — SaaS', 'schedule_type' => 'daily', 'cron' => '30 1 * * *', 'retention' => '30 days', 'offset_hours' => 8],
            ['name' => 'Full system image', 'schedule_type' => 'full', 'cron' => '0 0 * * 6', 'retention' => '4 copies', 'offset_hours' => 72],
        ];

        foreach ($templates as $i => $tpl) {
            $server = $servers->get($i % max(1, $servers->count()));
            $tenant = $tenants->get($i % max(1, $tenants->count()));

            BackupSchedule::query()->create([
                'name' => $tpl['name'],
                'server_id' => $server?->id,
                'tenant_id' => $tenant?->id,
                'schedule_type' => $tpl['schedule_type'],
                'cron_expression' => $tpl['cron'],
                'next_run_at' => now()->addHours($tpl['offset_hours']),
                'retention_policy' => $tpl['retention'],
                'enabled' => $i !== 4,
            ]);
        }
    }

    private function seedJobs($servers, $tenants, $projects): void
    {
        $jobs = [
            ['name' => 'prod-db-primary', 'type' => 'database', 'status' => 'successful', 'size' => 4_821_000_000, 'duration' => 342, 'hours_ago' => 2, 'restore' => true],
            ['name' => 'edge-api-snapshot', 'type' => 'snapshot', 'status' => 'successful', 'size' => 12_400_000_000, 'duration' => 1180, 'hours_ago' => 5, 'restore' => true],
            ['name' => 'tenant-acme-files', 'type' => 'files', 'status' => 'running', 'size' => 890_000_000, 'duration' => 180, 'hours_ago' => 0, 'restore' => false],
            ['name' => 'whm-cpanel-full', 'type' => 'full', 'status' => 'successful', 'size' => 28_900_000_000, 'duration' => 4200, 'hours_ago' => 8, 'restore' => true],
            ['name' => 'redis-cache-dump', 'type' => 'incremental', 'status' => 'warning', 'size' => 124_000_000, 'duration' => 45, 'hours_ago' => 1, 'restore' => false],
            ['name' => 'staging-db-clone', 'type' => 'database', 'status' => 'failed', 'size' => 0, 'duration' => 92, 'hours_ago' => 3, 'restore' => false],
            ['name' => 'tenant-nova-vault', 'type' => 'files', 'status' => 'successful', 'size' => 2_100_000_000, 'duration' => 510, 'hours_ago' => 12, 'restore' => true],
            ['name' => 'object-store-mirror', 'type' => 'snapshot', 'status' => 'queued', 'size' => null, 'duration' => null, 'hours_ago' => 0, 'restore' => false],
            ['name' => 'mysql-replica-sync', 'type' => 'database', 'status' => 'successful', 'size' => 3_200_000_000, 'duration' => 280, 'hours_ago' => 6, 'restore' => true],
            ['name' => 'legacy-app-archive', 'type' => 'full', 'status' => 'successful', 'size' => 8_750_000_000, 'duration' => 2400, 'hours_ago' => 24, 'restore' => true],
            ['name' => 'tenant-orbit-db', 'type' => 'database', 'status' => 'failed', 'size' => 0, 'duration' => 18, 'hours_ago' => 4, 'restore' => false],
            ['name' => 'cdn-static-bundle', 'type' => 'files', 'status' => 'successful', 'size' => 640_000_000, 'duration' => 95, 'hours_ago' => 10, 'restore' => false],
        ];

        foreach ($jobs as $i => $job) {
            $server = $servers->get($i % max(1, $servers->count()));
            $tenant = $tenants->get($i % max(1, $tenants->count()));
            $project = $projects->get($i % max(1, $projects->count()));

            $started = now()->subHours($job['hours_ago'])->subMinutes(random_int(5, 55));
            $completed = $job['status'] === 'running'
                ? null
                : $started->copy()->addSeconds($job['duration'] ?? 60);

            Backup::query()->create(Backup::attributesWithHostedProject($project?->id, [
                'name' => $job['name'],
                'server_id' => $server?->id,
                'tenant_id' => $tenant?->id,
                'backup_type' => $job['type'],
                'size_bytes' => $job['size'],
                'started_at' => $started,
                'completed_at' => $completed,
                'duration_seconds' => $job['duration'],
                'status' => $job['status'],
                'storage_disk' => $i % 2 === 0 ? 's3-primary' : 's3-dr-east',
                'integrity_verified' => $job['status'] === 'successful' && $i % 3 !== 0,
                'is_restore_point' => $job['restore'],
                'notes' => $job['status'] === 'failed' ? __('Checksum mismatch on archive segment 3') : null,
            ]));
        }
    }
}
