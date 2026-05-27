<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagedDomain extends Model
{
    protected $fillable = [
        'domain',
        'is_subdomain',
        'tenant_id',
        'server_id',
        'hosted_project_id',
        'registrar',
        'status',
        'ssl_status',
        'dns_status',
        'ssl_expires_at',
        'domain_expires_at',
        'auto_renew',
        'is_wildcard',
        'is_tenant_custom',
        'ssl_issuer',
        'routing_target',
        'certificate_chain',
        'renewal_history',
        'last_dns_check_at',
    ];

    protected function casts(): array
    {
        return [
            'is_subdomain' => 'boolean',
            'auto_renew' => 'boolean',
            'is_wildcard' => 'boolean',
            'is_tenant_custom' => 'boolean',
            'ssl_expires_at' => 'datetime',
            'domain_expires_at' => 'date',
            'certificate_chain' => 'array',
            'renewal_history' => 'array',
            'last_dns_check_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class, 'hosted_project_id');
    }

    /** @deprecated Use hostedProject() */
    public function project(): BelongsTo
    {
        return $this->hostedProject();
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->hosted_project_id;
    }

    public function setProjectIdAttribute(?int $value): void
    {
        $this->attributes['hosted_project_id'] = $value;
    }

    public function dnsRecords(): HasMany
    {
        return $this->hasMany(DnsRecord::class);
    }

    public function statusVariant(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'expiring_soon' => 'warning',
            'expired', 'invalid_ssl' => 'danger',
            'dns_error' => 'danger',
            default => 'neutral',
        };
    }

    public function sslStatusVariant(): string
    {
        return match ($this->ssl_status) {
            'active' => 'success',
            'expiring_soon' => 'warning',
            'expired', 'invalid' => 'danger',
            default => 'info',
        };
    }

    public function daysUntilSslExpiry(): ?int
    {
        if (! $this->ssl_expires_at) {
            return null;
        }

        return (int) now()->diffInDays($this->ssl_expires_at, false);
    }

    public function daysUntilDomainExpiry(): ?int
    {
        if (! $this->domain_expires_at) {
            return null;
        }

        return (int) now()->diffInDays(Carbon::parse($this->domain_expires_at), false);
    }

    public function sslExpiryLabel(): string
    {
        $days = $this->daysUntilSslExpiry();
        if ($days === null) {
            return '—';
        }
        if ($days < 0) {
            return __('Expired');
        }
        if ($days === 0) {
            return __('Today');
        }

        return $days.' '.__('days');
    }
}
