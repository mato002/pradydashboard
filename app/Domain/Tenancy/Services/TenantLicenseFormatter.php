<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Tenancy\DTOs\TenantLicenseResult;

class TenantLicenseFormatter
{
    /**
     * Legacy v1 API shape (backward compatible).
     *
     * @return array<string, mixed>
     */
    public function toLegacyArray(TenantLicenseResult $result): array
    {
        $subscription = match ($result->subscriptionStatus) {
            'trial' => 'trial',
            'grace' => 'paid',
            'unpaid' => 'unpaid',
            default => 'paid',
        };

        return [
            'tenant_status' => $result->tenantLifecycleStatus,
            'subscription_status' => $subscription,
            'access_level' => $result->accessLevel,
            'enabled_modules' => $result->enabledModuleKeys,
            'message' => $result->message,
            'expires_at' => $result->expiresAt,
        ];
    }

    /**
     * Enterprise public license API shape.
     *
     * @return array<string, mixed>
     */
    public function toEnterpriseArray(TenantLicenseResult $result): array
    {
        $status = match (true) {
            in_array($result->tenantLifecycleStatus, ['cancelled', 'terminated'], true) => 'terminated',
            $result->tenantLifecycleStatus === 'suspended' => 'suspended',
            $result->accessLevel === 'restricted' => 'restricted',
            $result->subscriptionStatus === 'grace' || $result->tenantLifecycleStatus === 'warning' => 'warning',
            default => 'active',
        };

        return [
            'status' => $status,
            'access_level' => match ($result->accessLevel) {
                'none' => 'suspended',
                default => $result->accessLevel,
            },
            'expires_at' => $result->expiresAt,
            'enabled_modules' => $result->enabledModuleKeys,
            'subscription' => $result->subscriptionStatus,
            'message' => $result->message,
        ];
    }
}
