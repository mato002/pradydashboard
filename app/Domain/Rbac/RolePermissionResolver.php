<?php

namespace App\Domain\Rbac;

use App\Models\Role;
use App\Support\Rbac\RoleStatus;
use Illuminate\Support\Collection;

class RolePermissionResolver
{
    /** @var array<int, list<string>> */
    private array $cache = [];

    /** @return list<string> */
    public function resolvePermissionCodes(Role $role): array
    {
        if (! $role->isActive()) {
            return [];
        }

        $roleId = $role->id;

        if (isset($this->cache[$roleId])) {
            return $this->cache[$roleId];
        }

        $role->loadMissing(['permissions', 'parentRoles']);

        $codes = $role->permissions->pluck('code')->all();

        foreach ($role->parentRoles as $parent) {
            if ($parent->status !== RoleStatus::Active) {
                continue;
            }
            $codes = array_merge($codes, $this->resolvePermissionCodes($parent));
        }

        $this->cache[$roleId] = array_values(array_unique($codes));

        return $this->cache[$roleId];
    }

    /** @return Collection<int, \App\Models\Permission> */
    public function resolvePermissions(Role $role): Collection
    {
        $codes = $this->resolvePermissionCodes($role);

        if ($codes === []) {
            return collect();
        }

        return \App\Models\Permission::query()->whereIn('code', $codes)->orderBy('code')->get();
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
