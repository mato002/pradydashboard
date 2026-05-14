<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Tenancy\DTOs\TenantLicenseResult;
use App\Models\LicenseModule;
use App\Models\Project;
use App\Models\Tenant;

class TenantLicenseEvaluator
{
    public function __construct(
        private readonly TenantAccessResolver $accessResolver
    ) {}

    public function evaluate(Project $project, Tenant $tenant): TenantLicenseResult
    {
        $tenant->loadMissing(['latestAccessControl', 'licenseModules']);

        $subscriptionStatus = $this->accessResolver->subscriptionStatus($tenant);
        $access = $tenant->latestAccessControl;
        $level = $access?->level;
        $lifecycle = $tenant->status;

        $expiresAt = $tenant->renewal_date?->toDateString();

        if (in_array($lifecycle, ['cancelled', 'terminated'], true)) {
            return new TenantLicenseResult(
                tenantLifecycleStatus: $lifecycle === 'cancelled' ? 'cancelled' : 'terminated',
                subscriptionStatus: 'unpaid',
                accessLevel: 'none',
                enabledModuleKeys: [],
                message: 'This tenant account has been closed.',
                expiresAt: $expiresAt,
            );
        }

        if ($lifecycle === 'suspended' || $level === 'suspended' || $level === 'terminated') {
            return new TenantLicenseResult(
                tenantLifecycleStatus: 'suspended',
                subscriptionStatus: $subscriptionStatus,
                accessLevel: 'none',
                enabledModuleKeys: [],
                message: 'Access is suspended. Please contact PradytecAI support.',
                expiresAt: $expiresAt,
            );
        }

        if ($lifecycle === 'overdue' || $subscriptionStatus === 'unpaid') {
            return new TenantLicenseResult(
                tenantLifecycleStatus: 'overdue',
                subscriptionStatus: 'unpaid',
                accessLevel: 'restricted',
                enabledModuleKeys: $this->resolveModuleKeys($tenant, restricted: true),
                message: 'Your subscription is overdue. Please renew to continue using the system.',
                expiresAt: $expiresAt,
            );
        }

        if ($level === 'restricted' || $lifecycle === 'restricted') {
            return new TenantLicenseResult(
                tenantLifecycleStatus: $lifecycle,
                subscriptionStatus: $subscriptionStatus,
                accessLevel: 'restricted',
                enabledModuleKeys: $this->resolveModuleKeys($tenant, restricted: true),
                message: 'Your account is in restricted mode until billing requirements are met.',
                expiresAt: $expiresAt,
            );
        }

        if ($level === 'warning' || $subscriptionStatus === 'grace' || $lifecycle === 'warning') {
            return new TenantLicenseResult(
                tenantLifecycleStatus: $lifecycle === 'warning' ? 'warning' : $lifecycle,
                subscriptionStatus: $subscriptionStatus,
                accessLevel: 'full',
                enabledModuleKeys: $this->resolveModuleKeys($tenant, restricted: false),
                message: $subscriptionStatus === 'grace'
                    ? 'Your renewal date has passed; you are within the grace period. Please pay to avoid interruption.'
                    : 'Payment reminder: please settle any outstanding balance to avoid service interruption.',
                expiresAt: $expiresAt,
            );
        }

        return new TenantLicenseResult(
            tenantLifecycleStatus: $lifecycle,
            subscriptionStatus: $subscriptionStatus,
            accessLevel: 'full',
            enabledModuleKeys: $this->resolveModuleKeys($tenant, restricted: false),
            message: null,
            expiresAt: $expiresAt,
        );
    }

    /**
     * @return list<string>
     */
    private function resolveModuleKeys(Tenant $tenant, bool $restricted): array
    {
        $disabledByControl = $tenant->latestAccessControl?->disabled_modules ?? [];
        if (! is_array($disabledByControl)) {
            $disabledByControl = [];
        }

        $keys = $tenant->licenseModules
            ->where('pivot.enabled', true)
            ->pluck('key')
            ->values()
            ->all();

        if ($keys === []) {
            $keys = $tenant->licenseModules()->wherePivot('enabled', true)->pluck('key')->all();
        }

        if ($keys === []) {
            $keys = $tenant->licenseModules()->pluck('key')->all();
        }

        if ($keys === []) {
            $keys = LicenseModule::query()->orderBy('sort_order')->pluck('key')->all();
        }

        $keys = array_values(array_diff($keys, $disabledByControl));

        if ($restricted) {
            $keys = array_values(array_diff($keys, ['billing', 'payroll', 'accounting']));
        }

        return $keys;
    }
}
