<?php

namespace App\Models;

use App\Models\Concerns\HasStaffAssignments;
use App\Domain\Servers\Support\ServerConnectionConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class Server extends Model
{
    use HasStaffAssignments;

    protected $fillable = [
        'name',
        'provider',
        'ip_address',
        'whm_cpanel_reference',
        'cpu_cores',
        'ram_gb',
        'storage_gb',
        'disk_usage_percent',
        'load_average',
        'ram_usage_percent',
        'status',
        'ssl_status',
        'ssl_days_remaining',
        'backup_status',
        'account_count',
        'hosted_domains',
        'renewal_expires_at',
        'billing_status',
        'monthly_cost',
        'currency',
        'monthly_revenue',
        'notes',
        'provisioning_meta',
        'telemetry_mode',
        'last_synced_at',
        'sync_status',
        'sync_message',
        'telemetry_source',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'provisioning_meta' => 'array',
            'hosted_domains' => 'array',
            'renewal_expires_at' => 'date',
            'monthly_cost' => 'decimal:2',
            'monthly_revenue' => 'decimal:2',
            'ram_gb' => 'decimal:2',
            'storage_gb' => 'decimal:2',
            'disk_usage_percent' => 'decimal:2',
            'ram_usage_percent' => 'decimal:2',
            'load_average' => 'decimal:2',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(HostedProject::class);
    }

    public function hostedProjects(): HasMany
    {
        return $this->projects();
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function tenantProjectDeployments(): HasMany
    {
        return $this->hasMany(TenantProjectInfrastructure::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    public function healthLogs(): HasMany
    {
        return $this->hasMany(ServerHealthLog::class);
    }

    public function latestHealthLog(): HasOne
    {
        return $this->hasOne(ServerHealthLog::class)->latestOfMany('checked_at');
    }

    public function providerNotices(): HasMany
    {
        return $this->hasMany(ServerProviderNotice::class);
    }

    public function openProviderNotices(): HasMany
    {
        return $this->providerNotices()->where('status', 'open');
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        $meta = is_array($this->provisioning_meta) ? $this->provisioning_meta : [];

        return $meta[$key] ?? $default;
    }

    public function hostname(): ?string
    {
        return ServerConnectionConfig::hostname($this);
    }

    public function hasWhmCredentials(): bool
    {
        return ServerConnectionConfig::whmCredentials($this) !== null;
    }

    public function hasStoredApiToken(): bool
    {
        return filled($this->meta('api_token')) || filled(config('infrastructure.whm.api_token'));
    }

    public function decryptedApiToken(): ?string
    {
        $stored = $this->meta('api_token');

        if ($stored) {
            try {
                return Crypt::decryptString($stored);
            } catch (\Throwable) {
                return is_string($stored) ? $stored : null;
            }
        }

        return config('infrastructure.whm.api_token') ?: null;
    }

    public function mergeProvisioningMeta(array $incoming): void
    {
        $existing = is_array($this->provisioning_meta) ? $this->provisioning_meta : [];

        foreach (['api_token', 'hostinger_api_token', 'provider_api_token'] as $tokenKey) {
            if (! array_key_exists($tokenKey, $incoming)) {
                continue;
            }

            $token = $incoming[$tokenKey];
            if ($token === null || $token === '' || $token === ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER) {
                unset($incoming[$tokenKey]);
            } else {
                $incoming[$tokenKey] = Crypt::encryptString($token);
            }
        }

        $this->provisioning_meta = array_merge($existing, $incoming);
    }

    public function telemetryModeLabel(): string
    {
        return match ($this->telemetry_mode) {
            'whm' => __('WHM live'),
            'basic' => __('Basic checks'),
            default => __('Manual monitoring'),
        };
    }

    public function renewalRisk(): string
    {
        if (! $this->renewal_expires_at) {
            return 'none';
        }

        $days = now()->startOfDay()->diffInDays($this->renewal_expires_at, false);

        if ($days < 0) {
            return 'overdue';
        }

        if ($days <= 30) {
            return 'soon';
        }

        return 'ok';
    }

    public function monthlyProfit(): string
    {
        $rev = (float) ($this->monthly_revenue ?? 0);
        $cost = (float) ($this->monthly_cost ?? 0);

        return number_format($rev - $cost, 2, '.', '');
    }

    public function displayLoad(): ?float
    {
        if ($this->load_average !== null) {
            return (float) $this->load_average;
        }

        $log = $this->relationLoaded('latestHealthLog')
            ? $this->latestHealthLog
            : $this->latestHealthLog()->first();

        if ($log && $log->cpu_percent !== null && $this->cpu_cores) {
            return round(((float) $log->cpu_percent / 100) * max(1, (int) $this->cpu_cores), 2);
        }

        return null;
    }

    public function displayRamPercent(): ?float
    {
        if ($this->ram_usage_percent !== null) {
            return (float) $this->ram_usage_percent;
        }

        $log = $this->relationLoaded('latestHealthLog')
            ? $this->latestHealthLog
            : $this->latestHealthLog()->first();

        return $log?->ram_percent !== null ? (float) $log->ram_percent : null;
    }
}
