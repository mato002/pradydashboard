<?php

namespace App\Observers;

use App\Domain\Tenancy\Services\TenantActivityLogger;
use App\Models\LicenseModule;
use App\Models\Tenant;

class TenantObserver
{
    public function __construct(
        private readonly TenantActivityLogger $activityLogger
    ) {}

    public function created(Tenant $tenant): void
    {
        $ids = LicenseModule::query()->orderBy('sort_order')->pluck('id');
        if ($ids->isNotEmpty()) {
            $tenant->licenseModules()->sync(
                $ids->mapWithKeys(fn (int $id): array => [$id => ['enabled' => true]])
            );
        }

        $this->activityLogger->log(
            $tenant,
            'tenant.created',
            'Tenant created in command center',
            ['company_name' => $tenant->company_name],
            auth()->user()
        );
    }

    public function updated(Tenant $tenant): void
    {
        $changes = array_intersect_key($tenant->getChanges(), array_flip($tenant->getFillable()));
        if ($changes === []) {
            return;
        }

        $this->activityLogger->log(
            $tenant,
            'tenant.updated',
            'Tenant profile updated',
            ['changes' => $changes],
            auth()->user()
        );
    }
}
