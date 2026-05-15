<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantInvoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'product_name',
        'amount_due',
        'amount_paid',
        'penalty_amount',
        'tax_amount',
        'due_date',
        'issued_at',
        'status',
        'payment_method',
        'generated_by',
        'is_recurring',
        'pdf_generated',
        'email_delivered_at',
        'collection_failed',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'due_date' => 'date',
            'issued_at' => 'datetime',
            'is_recurring' => 'boolean',
            'pdf_generated' => 'boolean',
            'email_delivered_at' => 'datetime',
            'collection_failed' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    public function balance(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid + (float) $this->penalty_amount);
    }

    public function formattedAmount(): string
    {
        return self::formatMoney((float) $this->amount_due);
    }

    public function formattedBalance(): string
    {
        return self::formatMoney($this->balance());
    }

    public static function formatMoney(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return 'KES '.number_format($amount / 1_000_000, 2).'M';
        }
        if ($amount >= 1_000) {
            return 'KES '.number_format($amount / 1_000, 1).'K';
        }

        return 'KES '.number_format($amount, 0);
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'partial' => 'info',
            'overdue' => 'danger',
            'cancelled' => 'neutral',
            default => 'warning',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => __('Paid'),
            'partial' => __('Partial'),
            'overdue' => __('Overdue'),
            'cancelled' => __('Cancelled'),
            default => __('Pending'),
        };
    }
}
