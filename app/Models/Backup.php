<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    public const STATUSES = ['successful', 'running', 'failed', 'queued', 'warning'];

    public const TYPES = ['full', 'database', 'files', 'snapshot', 'incremental'];

    protected $fillable = [
        'name',
        'server_id',
        'tenant_id',
        'project_id',
        'backup_type',
        'size_bytes',
        'started_at',
        'completed_at',
        'duration_seconds',
        'status',
        'storage_disk',
        'integrity_verified',
        'is_restore_point',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'integrity_verified' => 'boolean',
            'is_restore_point' => 'boolean',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'successful' => 'success',
            'running' => 'info',
            'failed' => 'danger',
            'queued' => 'neutral',
            'warning' => 'warning',
            default => 'neutral',
        };
    }

    public function formattedSize(): string
    {
        return self::formatBytes((int) ($this->size_bytes ?? 0));
    }

    public function formattedDuration(): string
    {
        $seconds = (int) ($this->duration_seconds ?? 0);
        if ($seconds < 60) {
            return $seconds.'s';
        }
        if ($seconds < 3600) {
            return (int) floor($seconds / 60).'m '.($seconds % 60).'s';
        }

        return (int) floor($seconds / 3600).'h '.(int) floor(($seconds % 3600) / 60).'m';
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_099_511_627_776) {
            return number_format($bytes / 1_099_511_627_776, 2).' TB';
        }
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }
}
