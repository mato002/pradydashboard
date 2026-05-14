<?php

namespace App\Domain\Tenancy\DTOs;

readonly class TenantLicenseResult
{
    /**
     * @param  list<string>  $enabledModuleKeys
     */
    public function __construct(
        public string $tenantLifecycleStatus,
        public string $subscriptionStatus,
        public string $accessLevel,
        public array $enabledModuleKeys,
        public ?string $message,
        public ?string $expiresAt,
    ) {}
}
