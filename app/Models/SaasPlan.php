<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tier',
        'monthly_price',
        'annual_price',
        'currency',
        'features',
        'api_quota',
        'storage_gb',
        'max_tenants',
        'max_seats',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'annual_price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function formattedMonthly(): string
    {
        return $this->currency.' '.number_format((float) $this->monthly_price, 0);
    }
}
