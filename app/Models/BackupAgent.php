<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupAgent extends Model
{
    protected $fillable = [
        'hosted_project_id',
        'tenant_id',
        'agent_key',
        'hostname',
        'environment',
        'agent_version',
        'last_heartbeat_at',
        'capabilities',
        'status',
        'disk_space_bytes',
        'free_space_bytes',
        'health',
        'php_version',
        'laravel_version',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
            'capabilities' => 'array',
            'disk_space_bytes' => 'integer',
            'free_space_bytes' => 'integer',
        ];
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
