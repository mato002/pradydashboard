<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProjectModuleSubscription extends Model
{
    protected $fillable = [
        'tenant_project_subscription_id',
        'project_module_id',
        'enabled',
        'subscribed',
        'billing_status',
        'monthly_price_override',
        'setup_price_override',
        'activated_at',
        'suspended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'subscribed' => 'boolean',
            'monthly_price_override' => 'decimal:2',
            'setup_price_override' => 'decimal:2',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function projectModule(): BelongsTo
    {
        return $this->belongsTo(ProjectModule::class);
    }
}
