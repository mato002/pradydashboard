<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupAction extends Model
{
    protected $fillable = [
        'backup_id',
        'action',
        'actor_label',
        'actor_user_id',
        'result',
        'meta',
        'ip_address',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }
}
