<?php

namespace Tests\Feature;

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
}
