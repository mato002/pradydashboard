<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAlert extends Model
{
    protected $fillable = [
        'tenant_id',
        'level',
        'title',
        'body',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
