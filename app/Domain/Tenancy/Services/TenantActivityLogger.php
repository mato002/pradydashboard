<?php

namespace App\Domain\Tenancy\Services;

use App\Models\Tenant;
use App\Models\TenantActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;

class TenantActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $properties
     */
    public function log(Tenant $tenant, string $action, ?string $summary = null, ?array $properties = null, ?Authenticatable $actor = null): void
    {
        TenantActivityLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'summary' => $summary,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
