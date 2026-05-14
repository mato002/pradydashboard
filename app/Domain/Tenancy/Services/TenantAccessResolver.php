<?php

namespace App\Domain\Tenancy\Services;

use App\Models\Tenant;

class TenantAccessResolver
{
    /**
     * @return 'trial'|'paid'|'grace'|'unpaid'
     */
    public function subscriptionStatus(Tenant $tenant): string
    {
        if ($tenant->status === 'trial') {
            return 'trial';
        }

        if ($tenant->renewal_date === null) {
            return 'paid';
        }

        if (! $tenant->renewal_date->isBefore(today())) {
            return 'paid';
        }

        $graceDays = max(0, (int) ($tenant->grace_days ?? 7));
        $graceEnd = $tenant->renewal_date->copy()->addDays($graceDays);

        if ($graceEnd->isBefore(today())) {
            return 'unpaid';
        }

        return 'grace';
    }

    public function isAfterGracePeriod(Tenant $tenant): bool
    {
        return $this->subscriptionStatus($tenant) === 'unpaid';
    }
}
