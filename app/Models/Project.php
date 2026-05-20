<?php

namespace App\Models;

use App\Models\Concerns\HasStaffAssignments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasStaffAssignments;

    protected $fillable = [
        'server_id',
        'name',
        'domain',
        'product_slug',
        'product_key',
        'description',
        'base_url',
        'technology_stack',
        'git_repository',
        'database_name',
        'status',
        'version',
        'monthly_revenue',
        'monthly_cost',
        'api_token',
        'notes',
        'system_code',
        'owner_department',
        'internal_notes',
        'min_supported_version',
        'latest_release_date',
        'business_model',
        'deployment_type',
        'default_setup_fee',
        'default_monthly_fee',
        'billing_model',
        'currency',
        'trial_days',
        'minimum_contract_term',
        'license_validation_mode',
        'grace_period_days',
        'kill_switch_allowed',
        'offline_mode_allowed',
        'contract_document_required',
        'requires_server',
        'requires_domain',
        'requires_ssl',
        'requires_whm',
        'default_disk_quota_mb',
        'default_database_required',
        'backup_required',
    ];

    protected function casts(): array
    {
        return [
            'monthly_revenue' => 'decimal:2',
            'monthly_cost' => 'decimal:2',
            'default_setup_fee' => 'decimal:2',
            'default_monthly_fee' => 'decimal:2',
            'latest_release_date' => 'date',
            'trial_days' => 'integer',
            'minimum_contract_term' => 'integer',
            'grace_period_days' => 'integer',
            'kill_switch_allowed' => 'boolean',
            'offline_mode_allowed' => 'boolean',
            'contract_document_required' => 'boolean',
            'requires_server' => 'boolean',
            'requires_domain' => 'boolean',
            'requires_ssl' => 'boolean',
            'requires_whm' => 'boolean',
            'default_database_required' => 'boolean',
            'backup_required' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            if (empty($project->api_token)) {
                $project->api_token = Str::random(64);
            }
            if (empty($project->product_key) && filled($project->product_slug)) {
                $project->product_key = $project->product_slug;
            }
            if (empty($project->product_slug) && filled($project->product_key)) {
                $project->product_slug = $project->product_key;
            }
            if (empty($project->base_url) && filled($project->domain)) {
                $project->base_url = 'https://'.$project->domain;
            }
        });

        static::saving(function (Project $project): void {
            if (filled($project->product_slug) && empty($project->product_key)) {
                $project->product_key = $project->product_slug;
            }
        });
    }

    public function resolveProductKey(): string
    {
        return (string) ($this->product_key ?: $this->product_slug ?: '');
    }

    public function licenseCheckLogs(): HasMany
    {
        return $this->hasMany(LicenseCheckLog::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(ProjectDeployment::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class);
    }

    public function currentVersionRecord(): HasOne
    {
        return $this->hasOne(ProjectVersion::class)->where('is_current', true)->latestOfMany();
    }

    public function tenantProjectSubscriptions(): HasMany
    {
        return $this->hasMany(TenantProjectSubscription::class);
    }

    public function monthlyProfit(): string
    {
        $rev = (float) ($this->monthly_revenue ?? 0);
        $cost = (float) ($this->monthly_cost ?? 0);

        return number_format($rev - $cost, 2, '.', '');
    }
}
