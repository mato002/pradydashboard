<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Rbac\RolePermissionResolver;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UserRoleAssignment;
use App\Support\ActivityLogCategory;
use App\Support\Rbac\RoleStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly RolePermissionResolver $permissionResolver,
    ) {}

    public function index(): View
    {
        $roles = Role::query()->withCount('assignments')->orderBy('name')->paginate(20);

        return view('admin.access-control.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.access-control.roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRole($request);
        $validated['code'] = strtolower($validated['code']);
        $validated['is_system'] = false;
        $validated['requires_elevation'] = $request->boolean('requires_elevation');

        $role = Role::query()->create($validated);

        $this->activityLogger->log(
            'rbac.role.created',
            ActivityLogCategory::SYSTEM,
            __('Created role :name', ['name' => $role->name]),
            $role,
        );

        return redirect()->route('access-control.roles.show', $role)->with('status', __('Role created.'));
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'parentRoles', 'childRoles']);
        $directPermissions = $role->permissions;
        $inheritedPermissions = $this->permissionResolver->resolvePermissions($role)
            ->whereNotIn('id', $directPermissions->pluck('id'));
        $assignments = UserRoleAssignment::query()
            ->with('user')
            ->where('role_id', $role->id)
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.access-control.roles.show', compact(
            'role',
            'directPermissions',
            'inheritedPermissions',
            'assignments',
        ));
    }

    public function edit(Role $role): View
    {
        return view('admin.access-control.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system && $role->isSuperAdmin()) {
            $request->merge(['code' => $role->code, 'is_system' => true]);
        }

        $validated = $this->validateRole($request, $role);
        $validated['requires_elevation'] = $request->boolean('requires_elevation');
        $old = $role->only(['name', 'status', 'requires_elevation']);
        $role->update($validated);

        $this->activityLogger->log(
            'rbac.role.updated',
            ActivityLogCategory::SYSTEM,
            __('Updated role :name', ['name' => $role->name]),
            $role,
            $old,
            $role->only(['name', 'status', 'requires_elevation']),
        );

        return redirect()->route('access-control.roles.show', $role)->with('status', __('Role updated.'));
    }

    /** @return array<string, mixed> */
    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('roles', 'code')->ignore($role?->id),
                Rule::notIn(config('rbac.reserved_role_codes', ['super_admin'])),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(RoleStatus::values())],
            'requires_elevation' => ['boolean'],
            'elevation_methods' => ['nullable', 'array'],
            'elevation_methods.*' => ['string', Rule::in(['password', 'otp', 'mfa'])],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
