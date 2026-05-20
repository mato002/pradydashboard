<?php

namespace App\Http\Controllers\Admin\Rbac;

use App\Domain\Rbac\UserRoleAssignmentService;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserRoleAssignmentController extends Controller
{
    public function __construct(
        private readonly UserRoleAssignmentService $assignmentService,
    ) {}

    public function index(Request $request): View
    {
        $assignments = UserRoleAssignment::query()
            ->with(['user', 'role', 'assignedBy'])
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $roles = Role::query()->where('status', 'active')->orderBy('name')->get();

        return view('admin.access-control.assignments.index', compact('assignments', 'users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'scope_type' => ['required', Rule::in(RoleScopeType::values())],
            'tenant_id' => ['nullable', 'integer', 'required_if:scope_type,tenant'],
            'project_id' => ['nullable', 'integer', 'required_if:scope_type,project'],
            'server_id' => ['nullable', 'integer', 'required_if:scope_type,server'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'assignment_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $role = Role::query()->findOrFail($validated['role_id']);

        $this->assignmentService->assign($request->user(), $user, $role, $validated);

        return back()->with('status', __('Role assignment created.'));
    }

    public function revoke(Request $request, UserRoleAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'revoke_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->assignmentService->revoke($request->user(), $assignment, $validated['revoke_reason'] ?? null);

        return back()->with('status', __('Role assignment revoked.'));
    }
}
