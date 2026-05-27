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
     * Public control-plane API (POST /api/v1/license/check).
     *
     * @return array<string, mixed>
     */
    public function toPublicApiArray(TenantLicenseResult $result): array
    {
        $tenantStatus = match (true) {
            in_array($result->tenantLifecycleStatus, ['cancelled', 'terminated'], true) => 'terminated',
            $result->tenantLifecycleStatus === 'suspended' => 'suspended',
            $result->accessLevel === 'restricted' => 'restricted',
            $result->tenantLifecycleStatus === 'overdue' || $result->subscriptionStatus === 'unpaid' => 'overdue',
            $result->subscriptionStatus === 'grace' || $result->tenantLifecycleStatus === 'warning' => 'warning',
            default => 'active',
        };

        $accessLevel = match (true) {
            in_array($tenantStatus, ['terminated', 'suspended'], true) => 'blocked',
            $result->accessLevel === 'restricted' => 'read_only',
            in_array($tenantStatus, ['overdue', 'warning'], true) => 'warning',
            default => 'full',
        };

        $allowed = ! in_array($accessLevel, ['blocked', 'read_only'], true)
            && ! in_array($tenantStatus, ['overdue', 'restricted', 'suspended', 'terminated'], true);

        $message = $result->message ?? match ($accessLevel) {
            'full' => 'Access granted',
            'warning' => 'Your subscription is overdue. Please renew to avoid service interruption.',
            'read_only' => 'Your account is restricted due to pending payment.',
            'blocked' => 'Your account has been suspended. Contact PradytecAI.',
            default => null,
        };

        return [
            'allowed' => $allowed,
            'tenant_status' => $tenantStatus,
            'access_level' => $accessLevel,
            'message' => $message,
            'enabled_modules' => $allowed ? $result->enabledModuleKeys : [],
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
