<?php

namespace App\Observers;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Tenancy\Services\TenantActivityLogger;
use App\Models\LicenseModule;
use App\Models\Tenant;
use App\Support\ActivityLogCategory;

class TenantObserver
{
    public function __construct(
        private readonly TenantActivityLogger $tenantActivityLogger,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function created(Tenant $tenant): void
    {
        $ids = LicenseModule::query()->orderBy('sort_order')->pluck('id');
        if ($ids->isNotEmpty()) {
            $tenant->licenseModules()->sync(
                $ids->mapWithKeys(fn (int $id): array => [$id => ['enabled' => true]])
            );
        }

        $this->tenantActivityLogger->log(
            $tenant,
            'tenant.created',
            'Tenant created in command center',
            ['company_name' => $tenant->company_name],
            auth()->user()
        );

        $this->activityLogger->log(
            'tenant.created',
            ActivityLogCategory::TENANT,
            __('Tenant :name created', ['name' => $tenant->company_name]),
            $tenant,
            null,
            ['company_name' => $tenant->company_name],
        );
    }

    public function updated(Tenant $tenant): void
    {
        $changes = array_intersect_key($tenant->getChanges(), array_flip($tenant->getFillable()));
        unset($changes['updated_at']);
        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $tenant->getOriginal($key);
        }

        $this->tenantActivityLogger->log(
            $tenant,
            'tenant.updated',
            'Tenant profile updated',
            ['changes' => $changes],
            auth()->user()
        );

        $this->activityLogger->log(
            'tenant.updated',
            ActivityLogCategory::TENANT,
            __('Tenant :name updated', ['name' => $tenant->company_name]),
            $tenant,
            $old,
            $changes,
        );
    }
}
