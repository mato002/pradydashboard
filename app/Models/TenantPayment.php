<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'tenant_id',
        'tenant_invoice_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'method',
        'gateway',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function displayId(): string
    {
        return $this->transaction_id ?? 'TXN-'.$this->id;
    }

    public function formattedAmount(): string
    {
        $currency = $this->currency ?? 'KES';

        return $currency.' '.number_format((float) $this->amount, 2);
    }

    public function gatewayLabel(): string
    {
        return match ($this->gateway) {
            'mpesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'flutterwave' => 'Flutterwave',
            'bank_transfer' => 'Bank Transfer',
            default => $this->method ?? __('Unknown'),
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
}
