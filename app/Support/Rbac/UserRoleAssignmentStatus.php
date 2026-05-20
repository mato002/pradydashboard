<?php

namespace App\Support\Rbac;

enum UserRoleAssignmentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
