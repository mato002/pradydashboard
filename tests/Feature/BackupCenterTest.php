<?php

namespace Tests\Feature;

use App\Domain\Backups\BackupArchiveWriter;
use App\Jobs\Backups\RunBackupJob;
use App\Models\Backup;
use App\Models\BackupAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_backup_center(): void
    {
        $this->get(route('backups.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_backup_center(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('backups.index'))
            ->assertOk()
            ->assertSee(__('Backup Management Center'))
            ->assertSee(__('Backup Jobs'))
            ->assertSee(__('Disaster Recovery'));
    }

    public function test_backup_detail_shows_action_log_and_downloads_archive(): void
    {
        $user = User::factory()->create();

        $backup = Backup::query()->create([
            'name' => 'Test backup',
            'backup_type' => 'full',
            'status' => 'successful',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'duration_seconds' => 60,
            'size_bytes' => 1024,
            'storage_disk' => 'local',
        ]);

        app(BackupArchiveWriter::class)->writeSimulatedArchive($backup->fresh());
        BackupAction::query()->create([
            'backup_id' => $backup->id,
            'action' => 'backup.completed',
            'actor_label' => 'system',
            'result' => 'success',
            'performed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('backups.show', $backup))
            ->assertOk()
            ->assertSee(__('Action log'))
            ->assertSee('backup.completed');

        $this->actingAs($user)
            ->get(route('backups.download', $backup))
            ->assertOk();

        $this->assertDatabaseHas('backup_actions', [
            'backup_id' => $backup->id,
            'action' => 'backup.downloaded',
        ]);
    }

    public function test_run_backup_job_writes_archive_for_download(): void
    {
        $backup = Backup::query()->create([
            'name' => 'Queued backup',
            'backup_type' => 'database',
            'status' => 'queued',
            'started_at' => now(),
            'storage_disk' => 'local',
        ]);

        RunBackupJob::dispatchSync($backup->id);

        $backup->refresh();
        $this->assertContains($backup->status, ['successful', 'warning']);
        $this->assertTrue($backup->hasDownloadableArchive());
        $this->assertDatabaseHas('backup_actions', [
            'backup_id' => $backup->id,
            'action' => 'backup.completed_with_warning',
        ]);
    }
}
