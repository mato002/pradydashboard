<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedSecret;
use App\Support\IntegrationServiceOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProjectServiceIntegration extends Model
{
    use HasEncryptedSecret;

    protected $fillable = [
        'tenant_project_subscription_id',
        'integration_category',
        'service_type',
        'provider_name',
        'display_name',
        'api_name',
        'status',
        'api_secret',
        'endpoint_url',
        'authentication_type',
        'purpose',
        'account_reference',
        'balance_credits',
        'monthly_quota',
        'used_quota',
        'last_tested_at',
        'last_test_status',
        'last_checked_at',
        'last_success_at',
        'last_failure_at',
        'last_response_code',
        'last_response_time_ms',
        'success_count',
        'failure_count',
        'uptime_percentage',
        'average_response_time_ms',
        'last_payload_summary',
        'last_error',
        'notes',
    ];

    protected $hidden = ['api_secret'];

    protected function casts(): array
    {
        return [
            'balance_credits' => 'decimal:2',
            'last_tested_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'last_payload_summary' => 'array',
            'uptime_percentage' => 'decimal:2',
            'success_count' => 'integer',
            'failure_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $integration) {
            if (! filled($integration->integration_category)) {
                $integration->integration_category = $integration->isTenantSystem()
                    ? IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM
                    : IntegrationServiceOptions::CATEGORY_PROVIDER;
            }
            if ($integration->isTenantSystem() && ! filled($integration->service_type)) {
                $integration->service_type = 'tenant_system';
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function scopeProvider(Builder $query): Builder
    {
        return $query->where('integration_category', IntegrationServiceOptions::CATEGORY_PROVIDER);
    }

    public function scopeTenantSystem(Builder $query): Builder
    {
        return $query->where('integration_category', IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM);
    }

    public function isTenantSystem(): bool
    {
        return $this->integration_category === IntegrationServiceOptions::CATEGORY_TENANT_SYSTEM
            || $this->service_type === 'tenant_system'
            || IntegrationServiceOptions::isTenantSystemPurpose($this->purpose);
    }

    public function isProvider(): bool
    {
        return ! $this->isTenantSystem();
    }

    public function hasStoredSecret(): bool
    {
        return filled($this->attributes['api_secret'] ?? null);
    }

    public function resolvedApiName(): string
    {
        return $this->api_name ?: $this->display_name;
    }

    public function serviceTypeLabel(): string
    {
        if ($this->isTenantSystem()) {
            return IntegrationServiceOptions::tenantSystemPurposes()[$this->purpose ?? 'custom']
                ?? __('Tenant system API');
        }

        return IntegrationServiceOptions::providerServiceTypes()[$this->service_type]
            ?? IntegrationServiceOptions::serviceTypes()[$this->service_type]
            ?? $this->service_type;
    }

    public function purposeLabel(): string
    {
        return IntegrationServiceOptions::tenantSystemPurposes()[$this->purpose ?? ''] ?? ($this->purpose ?? '—');
    }

    public function authenticationTypeLabel(): string
    {
        return IntegrationServiceOptions::authenticationTypes()[$this->authentication_type ?? 'none']
            ?? $this->authentication_type;
    }

    public function recordCheckResult(bool $success, int $responseCode, int $responseTimeMs, ?string $error = null): void
    {
        $now = now();
        $this->last_checked_at = $now;
        $this->last_tested_at = $now;
        $this->last_response_code = $responseCode > 0 ? $responseCode : null;
        $this->last_response_time_ms = max(0, $responseTimeMs);

        if ($success) {
            $this->last_success_at = $now;
            $this->last_test_status = 'pass';
            $this->status = 'active';
            $this->last_error = null;
            $this->success_count = (int) $this->success_count + 1;
        } else {
            $this->last_failure_at = $now;
            $this->last_test_status = 'fail';
            $this->status = 'failing';
            $this->last_error = $error !== null && $error !== ''
                ? \Illuminate\Support\Str::limit($error, 500)
                : __('Request failed.');
            $this->failure_count = (int) $this->failure_count + 1;
        }

        $total = (int) $this->success_count + (int) $this->failure_count;
        if ($total > 0) {
            $this->uptime_percentage = round(((int) $this->success_count / $total) * 100, 2);
        }

        $this->average_response_time_ms = $this->nextAverageResponseTimeMs($responseTimeMs, $total);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function safeUsageSummary(): ?array
    {
        $usage = $this->last_payload_summary['usage'] ?? null;

        return is_array($usage) ? $usage : null;
    }

    public function contractHealth(): ?string
    {
        $health = $this->last_payload_summary['contract_health'] ?? null;

        return is_string($health) && in_array($health, ['valid', 'partial', 'invalid'], true)
            ? $health
            : null;
    }

    public function contractHealthLabel(): ?string
    {
        return match ($this->contractHealth()) {
            'valid' => __('Valid'),
            'partial' => __('Partial'),
            'invalid' => __('Invalid'),
            default => null,
        };
    }

    private function nextAverageResponseTimeMs(int $responseTimeMs, int $totalChecks): int
    {
        if ($totalChecks <= 0) {
            return max(0, $responseTimeMs);
        }

        $previous = $this->average_response_time_ms;

        if ($previous === null || $totalChecks === 1) {
            return max(0, $responseTimeMs);
        }

        return (int) round($previous + (($responseTimeMs - $previous) / $totalChecks));
    }
}
