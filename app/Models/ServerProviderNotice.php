<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerProviderNotice extends Model
{
    public const TYPES = [
        'invoice', 'renewal', 'downtime', 'resource_warning',
        'cpanel_license', 'ssl', 'domain', 'backup', 'general',
    ];

    public const SEVERITIES = ['info', 'warning', 'critical'];

    public const STATUSES = ['open', 'resolved', 'ignored'];

    protected $fillable = [
        'server_id',
        'source',
        'notice_type',
        'title',
        'body',
        'severity',
        'notice_date',
        'due_date',
        'status',
        'source_reference',
        'attachment_reference',
    ];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
