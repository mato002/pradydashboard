<?php

namespace App\Domain\Tenancy\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

interface TenantRepositoryInterface
{
    public function findForCommandCenter(int $id): Tenant;

    /**
     * @return Collection<int, Tenant>
     */
    public function recentForDashboard(int $limit = 6): Collection;
}
