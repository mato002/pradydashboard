<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Rbac\RoleInheritanceValidator;
use App\Domain\Rbac\RolePermissionResolver;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\ActivityLogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class RoleInheritanceController extends Controller
{
    public function __construct(
        private readonly RoleInheritanceValidator $inheritanceValidator,
        private readonly ActivityLogger $activityLogger,
        private readonly RolePermissionResolver $permissionResolver,
    ) {}

    public function edit(Role $role): View
    {
        $roles = Role::query()->where('id', '!=', $role->id)->orderBy('name')->get();
        $parentIds = $role->parentRoles()->pluck('roles.id')->all();

        return view('admin.access-control.roles.inheritance', compact('role', 'roles', 'parentIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'parent_role_ids' => ['nullable', 'array'],
            'parent_role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $parentIds = $validated['parent_role_ids'] ?? [];

        try {
            foreach ($parentIds as $parentId) {
                $parent = Role::query()->findOrFail($parentId);
                $this->inheritanceValidator->assertCanInherit($parent, $role);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['parent_role_ids' => $e->getMessage()])->withInput();
        }

        $old = $role->parentRoles()->pluck('code')->all();
        $role->parentRoles()->sync($parentIds);
        $this->permissionResolver->clearCache();

        $this->activityLogger->log(
            'rbac.role.inheritance.updated',
            ActivityLogCategory::SYSTEM,
            __('Updated inheritance for role :name', ['name' => $role->name]),
            $role,
            ['parents' => $old],
            ['parents' => $role->parentRoles()->pluck('code')->all()],
        );

        return redirect()->route('access-control.roles.show', $role)->with('status', __('Role inheritance updated.'));
    }
}
