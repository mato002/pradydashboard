<?php

namespace App\Domain\Rbac;

use App\Domain\Activity\ActivityLogger;
use App\Models\RoleSwitchLog;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RoleSwitchService
{
    public function __construct(
        private readonly ActiveRoleService $activeRoleService,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function switch(
        User $user,
        UserRoleAssignment $toAssignment,
        ?string $password = null,
        ?string $reason = null,
        ?string $elevationMethod = null,
    ): void {
        if ($toAssignment->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'assignment' => __('Invalid role assignment.'),
            ]);
        }

        if (! $toAssignment->isActivatable()) {
            throw ValidationException::withMessages([
                'assignment' => __('This role assignment cannot be activated.'),
            ]);
        }

        $role = $toAssignment->role;

        if ($role->requires_elevation) {
            $method = $elevationMethod ?? 'password';

            if ($method !== 'password') {
                throw ValidationException::withMessages([
                    'password' => __('Only password elevation is available. OTP/MFA are not yet enabled.'),
                ]);
            }

            if (! $password || ! Hash::check($password, $user->password)) {
                $this->activityLogger->log(
                    'role.elevation.failed',
                    ActivityLogCategory::SYSTEM,
                    __('Failed elevation for role :role', ['role' => $role->name]),
                    $user,
                    null,
                    ['role' => $role->name, 'method' => $method],
                );

                throw ValidationException::withMessages([
                    'password' => __('Password confirmation is required for this role.'),
                ]);
            }

            $elevationMethod = 'password';
        }

        $fromRecord = $this->activeRoleService->getActiveRecord($user);
        $fromAssignment = $fromRecord?->assignment;

        $elevationAt = $role->requires_elevation ? now() : null;

        $this->activeRoleService->setActive(
            $user,
            $toAssignment,
            session()->getId(),
            $elevationAt,
        );

        $request = request();

        RoleSwitchLog::query()->create([
            'user_id' => $user->id,
            'from_assignment_id' => $fromAssignment?->id,
            'to_assignment_id' => $toAssignment->id,
            'from_role_name' => $fromAssignment?->role?->name,
            'to_role_name' => $role->name,
            'reason' => $reason,
            'elevation_method' => $elevationMethod,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);

        $this->activityLogger->log(
            'role.switched',
            ActivityLogCategory::SYSTEM,
            __('Switched active role to :role', ['role' => $role->name]),
            $user,
            [
                'from_role' => $fromAssignment?->role?->name,
                'to_role' => $role->name,
                'scope' => $toAssignment->scope_type->value,
            ],
            ['elevation_method' => $elevationMethod],
        );

        if ($elevationAt) {
            $this->activityLogger->log(
                'role.elevation.succeeded',
                ActivityLogCategory::SYSTEM,
                __('Elevation verified for role :role', ['role' => $role->name]),
                $user,
                null,
                ['role' => $role->name, 'method' => $elevationMethod],
            );
        }
    }
}
