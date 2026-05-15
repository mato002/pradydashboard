<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseCheckLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'project_id',
        'tenant_key',
        'product_key',
        'domain',
        'decision',
        'allowed',
        'tenant_status',
        'access_level',
        'http_status',
        'ip_address',
        'user_agent',
        'auth_method',
        'request_meta',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'allowed' => 'boolean',
            'request_meta' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
