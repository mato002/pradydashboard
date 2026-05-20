<?php

namespace App\Http\Controllers;

use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\RoleSwitchService;
use App\Models\UserRoleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActiveRoleController extends Controller
{
    public function switch(Request $request, RoleSwitchService $switchService, ActiveRoleService $activeRoleService): RedirectResponse
    {
        $validated = $request->validate([
            'assignment_id' => ['required', 'integer', 'exists:user_role_assignments,id'],
            'password' => ['nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $assignment = UserRoleAssignment::query()
            ->with('role')
            ->findOrFail($validated['assignment_id']);

        $switchService->switch(
            $request->user(),
            $assignment,
            $validated['password'] ?? null,
            $validated['reason'] ?? null,
        );

        return back()->with('status', __('Active role switched to :role.', ['role' => $assignment->role->name]));
    }
}
