<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAccessControl extends Model
{
    protected $fillable = [
        'tenant_id',
        'level',
        'restrict_login',
        'disabled_modules',
        'effective_from',
        'effective_until',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'restrict_login' => 'boolean',
            'disabled_modules' => 'array',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
