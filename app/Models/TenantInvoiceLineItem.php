<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TenantInvoiceLineItem extends Model
{
    protected $fillable = [
        'tenant_invoice_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate',
        'tax_amount',
        'line_total',
        'related_model_type',
        'related_model_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class, 'tenant_invoice_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_model_type', 'related_model_id');
    }
}
