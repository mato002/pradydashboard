<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceRecurringSchedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'product_name',
        'amount',
        'tax_rate',
        'frequency',
        'cycle',
        'custom_interval_days',
        'next_run_at',
        'auto_email',
        'auto_pdf',
        'enabled',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'next_run_at' => 'datetime',
            'auto_email' => 'boolean',
            'auto_pdf' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency) {
            'quarterly' => __('Quarterly'),
            'annual' => __('Annual'),
            default => __('Monthly'),
        };
    }

    public function totalWithTax(): float
    {
        return round((float) $this->amount * (1 + ((float) $this->tax_rate / 100)), 2);
    }
}
