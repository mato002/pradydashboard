<?php

namespace App\Support\Rbac;

use App\Domain\Rbac\RbacGuard;
use App\Models\User;

class Rbac
{
    public static function guard(): RbacGuard
    {
        return app(RbacGuard::class);
    }

    public static function can(string $permission, array $scope = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && static::guard()->can($user, $permission, $scope);
    }

    public static function userCan(?User $user, string $permission, array $scope = []): bool
    {
        if (! $user) {
            return false;
        }

        return static::guard()->can($user, $permission, $scope);
    }
}
