<?php

namespace App\Domain\Rbac;

use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\RoleScopeType;

class LoginRoleActivationService
{
    public function __construct(
        private readonly ActiveRoleService $activeRoleService,
    ) {}

    /**
     * Bind an activatable role to the current session after successful login.
     */
    public function activateForSession(User $user, bool $passwordVerified = true): ?UserActiveRole
    {
        $sessionId = session()->getId();

        $assignment = $this->preferredAssignment($user);

        if (! $assignment) {
            $this->activeRoleService->clearActive($user);

            return null;
        }

        $elevationAt = $this->elevationTimestamp($user, $assignment, $passwordVerified);

        return $this->activeRoleService->setActive(
            $user,
            $assignment,
            filled($sessionId) ? $sessionId : null,
            $elevationAt,
        );
    }

    private function elevationTimestamp(
        User $user,
        UserRoleAssignment $assignment,
        bool $passwordVerified,
    ): ?\DateTimeInterface {
        if (! $passwordVerified) {
            return null;
        }

        if ($assignment->role?->requires_elevation) {
            return now();
        }

        return null;
    }

    private function preferredAssignment(User $user): ?UserRoleAssignment
    {
        $assignments = collect($this->activeRoleService->activatableAssignments($user))
            ->sortByDesc(fn (UserRoleAssignment $assignment) => $this->assignmentPriority($assignment))
            ->values();

        return $assignments->first();
    }

    private function assignmentPriority(UserRoleAssignment $assignment): int
    {
        $priority = 0;

        if ($assignment->role?->isSuperAdmin()) {
            $priority += 1000;
        }

        if ($assignment->scope_type === RoleScopeType::Global) {
            $priority += 100;
        }

        return $priority;
    }
}
