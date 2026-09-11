<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'saas_plan_id',
        'plan_name',
        'product_name',
        'amount',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'status',
        'auto_renew',
        'replaces_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'auto_renew' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function saasPlan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class);
    }

    public function replacesSubscription(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_subscription_id');
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'trial' => 'info',
            'pending' => 'warning',
            'grace_period', 'grace' => 'warning',
            'overdue' => 'danger',
            'suspended' => 'danger',
            'cancelled' => 'neutral',
            default => 'neutral',
        };
    }
}
