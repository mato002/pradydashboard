<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProjectVersion extends Model
{
    protected $fillable = [
        'tenant_project_subscription_id',
        'current_version',
        'latest_version',
        'update_status',
        'commit_hash',
        'build_number',
        'last_checked_at',
        'last_updated_at',
        'update_notes',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'last_updated_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }
}
