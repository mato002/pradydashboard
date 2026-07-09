<?php

namespace App\Domain\Backups;

use App\Models\Backup;
use App\Models\BackupAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class BackupActionLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        Backup $backup,
        string $action,
        ?User $actor = null,
        array $meta = [],
        string $result = 'success',
        ?Request $request = null,
    ): void {
        if (! Schema::hasTable('backup_actions')) {
            return;
        }

        try {
            BackupAction::query()->create([
                'backup_id' => $backup->id,
                'action' => $action,
                'actor_label' => $actor?->name ?? $actor?->email ?? ($meta['actor'] ?? 'system'),
                'actor_user_id' => $actor?->id,
                'result' => $result,
                'meta' => $meta,
                'ip_address' => $request?->ip(),
                'performed_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never block backup flows on action log failures.
        }
    }
}
