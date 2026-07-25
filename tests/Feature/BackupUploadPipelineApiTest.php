<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\BackupAgent;
use App\Models\HostedProject;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupUploadPipelineApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_upload_verify_and_retain_five(): void
    {
        Storage::fake('local');

        $product = Product::query()->create([
            'name' => 'MFI',
            'slug' => 'mfi',
            'status' => 'active',
        ]);

        $project = HostedProject::query()->create([
            'product_id' => $product->id,
            'name' => 'MFI Prod',
            'domain' => 'mfi.test',
            'product_key' => 'mfi',
            'api_token' => 'test-project-token',
            'status' => 'active',
        ]);

        BackupAgent::query()->create([
            'hosted_project_id' => $project->id,
            'agent_key' => 'mfi-production-01',
            'status' => 'registered',
            'last_heartbeat_at' => now(),
        ]);

        $bytes = random_bytes(2048);
        $checksum = hash('sha256', $bytes);
        $size = strlen($bytes);

        // Create 5 prior successful agent backups so the 6th drops the oldest.
        for ($i = 1; $i <= 5; $i++) {
            $path = "backups/agent/mfi-production-01/old-{$i}/old.zip";
            Storage::disk('local')->put($path, 'old-'.$i);
            Backup::query()->create(Backup::attributesWithHostedProject($project->id, [
                'name' => "Old {$i}",
                'backup_type' => 'full',
                'size_bytes' => 10,
                'started_at' => now()->subHours(6 - $i),
                'completed_at' => now()->subHours(6 - $i),
                'status' => 'successful',
                'storage_disk' => 'local',
                'archive_path' => $path,
                'archive_filename' => 'old.zip',
                'checksum' => hash('sha256', 'old-'.$i),
                'integrity_verified' => true,
            ]));
        }

        $session = $this->withToken('test-project-token')
            ->postJson('/api/v1/backups/upload-session', [
                'agent_key' => 'mfi-production-01',
                'artifact_name' => 'fresh.zip',
                'checksum' => $checksum,
                'size_bytes' => $size,
                'backup_type' => 'full',
                'name' => 'Hourly backup',
                'retention_policy' => 'keep_5',
                'local_job_id' => 99,
            ])
            ->assertCreated()
            ->json();

        $this->assertNotEmpty($session['upload_id']);
        $this->assertNotEmpty($session['upload_token']);
        $this->assertNotEmpty($session['upload_url']);

        $this->call(
            'PUT',
            '/api/v1/backups/upload/'.$session['upload_id'],
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/zip',
                'HTTP_X_BACKUP_UPLOAD_TOKEN' => $session['upload_token'],
                'CONTENT_LENGTH' => (string) $size,
            ],
            $bytes
        )->assertOk();

        $complete = $this->withToken('test-project-token')
            ->postJson('/api/v1/backups/upload-complete', [
                'upload_id' => $session['upload_id'],
                'upload_token' => $session['upload_token'],
                'backup_id' => $session['backup_id'],
                'checksum' => $checksum,
                'size_bytes' => $size,
                'object_key' => $session['object_key'],
                'retention_policy' => 'keep_5',
            ])
            ->assertOk()
            ->json();

        $this->assertTrue($complete['passed']);
        $this->assertSame('completed', $complete['backup']['status']);
        $this->assertCount(1, $complete['retention']['deleted_ids'] ?? []);

        $this->assertSame(5, Backup::query()
            ->where('hosted_project_id', $project->id)
            ->where('status', 'successful')
            ->count());

        $this->withToken('test-project-token')
            ->getJson('/api/v1/backups/'.$session['backup_id'].'/retention')
            ->assertOk()
            ->assertJsonPath('retain', 5)
            ->assertJsonPath('assigned', true);
    }

    public function test_unregistered_agent_cannot_open_upload_session(): void
    {
        $product = Product::query()->create([
            'name' => 'MFI',
            'slug' => 'mfi',
            'status' => 'active',
        ]);

        HostedProject::query()->create([
            'product_id' => $product->id,
            'name' => 'MFI Prod',
            'domain' => 'mfi.test',
            'product_key' => 'mfi',
            'api_token' => 'test-project-token',
            'status' => 'active',
        ]);

        $this->withToken('test-project-token')
            ->postJson('/api/v1/backups/upload-session', [
                'agent_key' => 'unknown-agent',
                'artifact_name' => 'fresh.zip',
                'checksum' => str_repeat('a', 64),
                'size_bytes' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Agent not registered.');
    }
}
