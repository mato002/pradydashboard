<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Rbac\RolePermissionResolver;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\ActivityLogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly RolePermissionResolver $permissionResolver,
    ) {}

    public function edit(Role $role): View
    {
        $permissions = Permission::query()->orderBy('group')->orderBy('code')->get();
        $assignedIds = $role->permissions()->pluck('permissions.id')->all();

        return view('admin.access-control.roles.permissions', compact('role', 'permissions', 'assignedIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
            'wildcard_codes' => ['nullable', 'string'],
        ]);

        $allowedPrefixes = config('rbac.wildcard_prefixes', []);

        $wildcardCodes = collect(preg_split('/[\s,]+/', (string) ($validated['wildcard_codes'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(function (string $code) use ($allowedPrefixes) {
                if (! preg_match('/^[a-z0-9_.]+\.\*$/', $code)) {
                    return false;
                }
                $prefix = substr($code, 0, -1);

                foreach ($allowedPrefixes as $allowed) {
                    if (str_starts_with($prefix, $allowed)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        $permissionIds = $validated['permission_ids'] ?? [];

        foreach ($wildcardCodes as $code) {
            $permission = Permission::query()->firstOrCreate(
                ['code' => $code],
                ['name' => $code, 'description' => 'Wildcard permission', 'group' => explode('.', $code)[0]]
            );
            $permissionIds[] = $permission->id;
        }

        $old = $role->permissions()->pluck('code')->all();
        $role->permissions()->sync(array_unique($permissionIds));
        $this->permissionResolver->clearCache();

        $this->activityLogger->log(
            'rbac.role.permissions.updated',
            ActivityLogCategory::SYSTEM,
            __('Updated permissions for role :name', ['name' => $role->name]),
            $role,
            ['permissions' => $old],
            ['permissions' => $role->permissions()->pluck('code')->all()],
        );

        return redirect()->route('access-control.roles.show', $role)->with('status', __('Role permissions updated.'));
    }
}
