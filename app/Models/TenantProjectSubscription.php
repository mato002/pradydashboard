<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantProjectSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'package_name',
        'billing_cycle',
        'start_date',
        'renewal_date',
        'trial_expires_at',
        'contract_status',
        'license_status',
        'product_status',
        'monthly_fee',
        'setup_fee',
        'currency',
        'discount',
        'payment_terms',
        'grace_period_days',
        'kill_switch_enabled',
        'offline_mode_allowed',
        'contract_document_path',
        'signed_contract_date',
        'last_license_check_at',
        'last_heartbeat_at',
        'disabled_reason',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'renewal_date' => 'date',
            'trial_expires_at' => 'date',
            'signed_contract_date' => 'date',
            'monthly_fee' => 'decimal:2',
            'setup_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'kill_switch_enabled' => 'boolean',
            'offline_mode_allowed' => 'boolean',
            'last_license_check_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @deprecated Use product() — subscriptions are product-scoped after hosted_projects migration. */
    public function project(): BelongsTo
    {
        return $this->product();
    }

    public function infrastructure(): HasOne
    {
        return $this->hasOne(TenantProjectInfrastructure::class);
    }

    public function versionTracking(): HasOne
    {
        return $this->hasOne(TenantProjectVersion::class);
    }

    public function serviceIntegrations(): HasMany
    {
        return $this->hasMany(TenantProjectServiceIntegration::class);
    }

    public function moduleSubscriptions(): HasMany
    {
        return $this->hasMany(TenantProjectModuleSubscription::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OperationalDocument::class);
    }

    public function monthlyRecurring(): float
    {
        return (float) ($this->monthly_fee ?? 0);
    }
}
