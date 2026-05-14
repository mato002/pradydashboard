<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'external_key',
        'project_id',
        'server_id',
        'company_name',
        'business_type',
        'kra_pin',
        'physical_address',
        'country',
        'logo_path',
        'contact_person',
        'phone',
        'email',
        'subscription_plan',
        'subscription_amount',
        'tenant_currency',
        'billing_cycle',
        'start_date',
        'renewal_date',
        'grace_days',
        'status',
        'cpanel_account_ref',
        'database_ref',
        'login_url',
        'tenant_domain',
        'deployment_version',
        'penalties_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'renewal_date' => 'date',
            'subscription_amount' => 'decimal:2',
            'penalties_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            if (empty($tenant->external_key)) {
                $tenant->external_key = (string) Str::uuid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }

    public function accessControls(): HasMany
    {
        return $this->hasMany(TenantAccessControl::class);
    }

    public function latestAccessControl(): HasOne
    {
        return $this->hasOne(TenantAccessControl::class)->latestOfMany();
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function licenseModules(): BelongsToMany
    {
        return $this->belongsToMany(LicenseModule::class, 'tenant_modules', 'tenant_id', 'license_module_id')
            ->withPivot('enabled')
            ->withTimestamps();
    }

    public function usageMetric(): HasOne
    {
        return $this->hasOne(TenantUsageMetric::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(TenantActivityLog::class)->orderByDesc('id');
    }

    public function reportedUsers(): HasMany
    {
        return $this->hasMany(TenantReportedUser::class)->orderByDesc('last_seen_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(TenantAlert::class)->orderByDesc('id');
    }
}
