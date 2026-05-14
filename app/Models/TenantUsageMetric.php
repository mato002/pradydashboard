<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsageMetric extends Model
{
    protected $fillable = [
        'tenant_id',
        'active_users',
        'database_size_mb',
        'storage_usage_mb',
        'last_login_at',
        'last_sync_at',
        'server_cpu_percent',
        'reported_app_version',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'active_users' => 'integer',
            'database_size_mb' => 'decimal:2',
            'storage_usage_mb' => 'decimal:2',
            'last_login_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'server_cpu_percent' => 'decimal:2',
            'captured_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
