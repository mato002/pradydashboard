<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionNote extends Model
{
    protected $fillable = [
        'tenant_invoice_id',
        'user_id',
        'note_type',
        'body',
        'promised_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'promised_at' => 'date',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class, 'tenant_invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
