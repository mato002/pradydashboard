<?php

namespace App\Domain\Rbac;

class PermissionMatcher
{
    public function matches(string $granted, string $required): bool
    {
        if ($granted === $required) {
            return true;
        }

        if (! str_ends_with($granted, '.*')) {
            return false;
        }

        $prefix = substr($granted, 0, -2);

        return $required === $prefix || str_starts_with($required, $prefix.'.');
    }

    /**
     * @param  iterable<string>  $grantedPermissions
     */
    public function anyMatches(iterable $grantedPermissions, string $required): bool
    {
        foreach ($grantedPermissions as $granted) {
            if ($this->matches($granted, $required)) {
                return true;
            }
        }

        return false;
    }
}
