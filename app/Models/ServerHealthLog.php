<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerHealthLog extends Model
{
    protected $fillable = [
        'server_id',
        'cpu_percent',
        'ram_percent',
        'disk_percent',
        'uptime_seconds',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'cpu_percent' => 'decimal:2',
            'ram_percent' => 'decimal:2',
            'disk_percent' => 'decimal:2',
            'checked_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
