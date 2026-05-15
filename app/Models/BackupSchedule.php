<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupSchedule extends Model
{
    protected $fillable = [
        'name',
        'server_id',
        'tenant_id',
        'schedule_type',
        'cron_expression',
        'next_run_at',
        'retention_policy',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'next_run_at' => 'datetime',
            'enabled' => 'boolean',
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

    public function typeLabel(): string
    {
        return match ($this->schedule_type) {
            'daily' => __('Daily backup'),
            'weekly' => __('Weekly snapshot'),
            'monthly' => __('Monthly archive'),
            'incremental' => __('Incremental backup'),
            'full' => __('Full backup'),
            default => ucfirst($this->schedule_type),
        };
    }
}
