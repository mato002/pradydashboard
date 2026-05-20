<?php

namespace App\Support\Rbac;

enum RoleScopeType: string
{
    case Global = 'global';
    case Tenant = 'tenant';
    case Project = 'project';
    case Server = 'server';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
