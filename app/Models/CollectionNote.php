<?php

namespace App\Models;

use App\Support\Billing\CollectionNoteStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionNote extends Model
{
    protected $fillable = [
        'tenant_invoice_id',
        'tenant_id',
        'user_id',
        'note_type',
        'body',
        'note',
        'promised_at',
        'follow_up_date',
        'promise_to_pay_date',
        'promised_amount',
        'status',
        'outcome',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'promised_at' => 'date',
            'follow_up_date' => 'date',
            'promise_to_pay_date' => 'date',
            'promised_amount' => 'decimal:2',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CollectionNote $model): void {
            if ($model->note && ! $model->body) {
                $model->body = $model->note;
            } elseif ($model->body && ! $model->note) {
                $model->note = $model->body;
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class, 'tenant_invoice_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param  Builder<CollectionNote>  $query */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', CollectionNoteStatus::OPEN);
    }

    public function displayText(): string
    {
        return (string) ($this->note ?: $this->body);
    }
}
