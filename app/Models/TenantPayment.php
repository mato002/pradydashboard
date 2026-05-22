<?php

namespace App\Models;

use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Billing\PaymentSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'tenant_id',
        'tenant_invoice_id',
        'source',
        'payer_name',
        'payer_phone',
        'payer_email',
        'amount',
        'unapplied_amount',
        'currency',
        'status',
        'reconciliation_status',
        'paid_at',
        'method',
        'gateway',
        'reference',
        'bank_source',
        'narration',
        'notes',
        'matched_at',
        'matched_by',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'unapplied_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'matched_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class, 'tenant_invoice_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function matchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function displayId(): string
    {
        return $this->transaction_id ?? 'TXN-'.$this->id;
    }

    public function formattedAmount(): string
    {
        $currency = $this->currency ?? 'KES';

        return $currency.' '.number_format((float) $this->amount, 2);
    }

    public function allocatedAmount(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    public function remainingToAllocate(): float
    {
        return max(0, round((float) $this->amount - $this->allocatedAmount(), 2));
    }

    public function sourceLabel(): string
    {
        return PaymentSource::label($this->source ?? PaymentSource::MANUAL);
    }

    public function reconciliationLabel(): string
    {
        return PaymentReconciliationStatus::label($this->reconciliation_status ?? PaymentReconciliationStatus::UNRECONCILED);
    }

    public function reconciliationVariant(): string
    {
        return PaymentReconciliationStatus::variant($this->reconciliation_status ?? PaymentReconciliationStatus::UNRECONCILED);
    }

    public function gatewayLabel(): string
    {
        return match ($this->gateway ?? $this->source) {
            'mpesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'flutterwave' => 'Flutterwave',
            'bank_transfer' => 'Bank Transfer',
            default => $this->method ?? $this->sourceLabel(),
        };
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'successful' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'purple',
            'reversed' => 'neutral',
            default => 'neutral',
        };
    }

    public function isReconciled(): bool
    {
        return in_array($this->reconciliation_status, [
            PaymentReconciliationStatus::MATCHED,
            PaymentReconciliationStatus::PARTIALLY_MATCHED,
        ], true);
    }
}
