<?php

namespace App\Models;

use App\Support\OperationalDocumentOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalDocument extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_project_subscription_id',
        'project_id',
        'document_type',
        'title',
        'file_path',
        'status',
        'uploaded_by',
        'signed_date',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date?->isPast() ?? false;
    }

    public function typeLabel(): string
    {
        return OperationalDocumentOptions::documentTypes()[$this->document_type] ?? $this->document_type;
    }

    public function statusLabel(): string
    {
        return OperationalDocumentOptions::statuses()[$this->status] ?? $this->status;
    }
}
