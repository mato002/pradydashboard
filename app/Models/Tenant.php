<?php

namespace App\Models;

use App\Models\Concerns\HasStaffAssignments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasStaffAssignments;

    protected $fillable = [
        'external_key',
        'payments_gateway_tenant_uuid',
        'payments_gateway_linked_at',
        'payments_gateway_status',
        'tenant_key',
        'license_secret',
        'access_level',
        'hosted_project_id',
        'product_id',
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
        'tenant_code',
        'industry',
        'registration_number',
        'county_city',
        'website',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'billing_contact_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_tax_pin',
        'billing_preferred_currency',
        'billing_payment_terms',
        'billing_tax_exempt',
        'billing_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'renewal_date' => 'date',
            'payments_gateway_linked_at' => 'datetime',
            'subscription_amount' => 'decimal:2',
            'penalties_total' => 'decimal:2',
            'billing_tax_exempt' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            if (empty($tenant->external_key)) {
                $tenant->external_key = (string) Str::uuid();
            }
            if (empty($tenant->tenant_key) && filled($tenant->company_name)) {
                $tenant->tenant_key = static::generateTenantKey($tenant->company_name);
            }
            if (empty($tenant->tenant_code) && filled($tenant->tenant_key)) {
                $tenant->tenant_code = strtoupper($tenant->tenant_key);
            }
            if (empty($tenant->license_secret)) {
                $tenant->license_secret = Str::random(64);
            }
            if (empty($tenant->access_level)) {
                $tenant->access_level = 'full';
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->hostedProject();
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class, 'hosted_project_id');
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->hosted_project_id;
    }

    public function setProjectIdAttribute(?int $value): void
    {
        $this->attributes['hosted_project_id'] = $value;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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

    public function communications(): HasMany
    {
        return $this->hasMany(TenantCommunication::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(TenantNotice::class);
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

    public function licenseCheckLogs(): HasMany
    {
        return $this->hasMany(LicenseCheckLog::class)->orderByDesc('checked_at');
    }

    public function projectSubscriptions(): HasMany
    {
        return $this->hasMany(TenantProjectSubscription::class);
    }

    public function operationalDocuments(): HasMany
    {
        return $this->hasMany(OperationalDocument::class);
    }

    public static function generateTenantKey(string $companyName): string
    {
        $base = Str::slug($companyName);
        $key = $base !== '' ? $base : 'tenant';
        $suffix = 0;

        while (static::query()->where('tenant_key', $key)->exists()) {
            $suffix++;
            $key = $base.'-'.$suffix;
        }

        return $key;
    }

    public function isPaymentsGatewayLinked(): bool
    {
        return filled($this->payments_gateway_tenant_uuid);
    }
}
