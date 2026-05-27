<?php

namespace App\Domain\Rbac;

use App\Models\User;
use App\Models\UserActiveRole;
use App\Models\UserRoleAssignment;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Support\Facades\DB;

class ActiveRoleService
{
    public function getActiveRecord(User $user): ?UserActiveRole
    {
        return UserActiveRole::query()
            ->with(['assignment.role'])
            ->where('user_id', $user->id)
            ->first();
    }

    public function getActiveAssignment(User $user): ?UserRoleAssignment
    {
        $record = $this->getActiveRecord($user);

        if (! $record) {
            return null;
        }

        if (filled($record->session_id)) {
            $currentSessionId = session()->getId();
            if (! filled($currentSessionId) || ! hash_equals($record->session_id, $currentSessionId)) {
                $record->delete();

                return null;
            }
        }

        $assignment = $record->assignment;

        if (! $assignment) {
            $record->delete();

            return null;
        }

        if ($assignment->expires_at && $assignment->expires_at->isPast()) {
            $assignment->update(['status' => UserRoleAssignmentStatus::Expired]);
            $record->delete();

            return null;
        }

        if ($record->expires_at && $record->expires_at->isPast()) {
            $record->delete();

            return null;
        }

        if (! $assignment->isActivatable()) {
            $record->delete();

            return null;
        }

        if (
            $assignment->role?->requires_elevation
            && ! $record->hasValidElevation()
            && ! $assignment->role->isSuperAdmin()
        ) {
            $record->delete();

            return null;
        }

        return $assignment;
    }

    /** @return list<UserRoleAssignment> */
    public function activatableAssignments(User $user): array
    {
        return UserRoleAssignment::query()
            ->with('role')
            ->where('user_id', $user->id)
            ->where('status', UserRoleAssignmentStatus::Active)
            ->get()
            ->filter(fn (UserRoleAssignment $a) => $a->isActivatable())
            ->values()
            ->all();
    }

    public function setActive(
        User $user,
        UserRoleAssignment $assignment,
        ?string $sessionId = null,
        ?\DateTimeInterface $elevationVerifiedAt = null,
    ): UserActiveRole {
        return DB::transaction(function () use ($user, $assignment, $sessionId, $elevationVerifiedAt) {
            UserActiveRole::query()->where('user_id', $user->id)->delete();

            return UserActiveRole::query()->create([
                'user_id' => $user->id,
                'user_role_assignment_id' => $assignment->id,
                'activated_at' => now(),
                'expires_at' => $assignment->expires_at,
                'elevation_verified_at' => $elevationVerifiedAt,
                'session_id' => $sessionId,
            ]);
        });
    }

    public function clearActive(User $user): void
    {
        UserActiveRole::query()->where('user_id', $user->id)->delete();
    }
}
