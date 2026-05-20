<?php

namespace App\Models;

use App\Support\Rbac\RoleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'is_system',
        'requires_elevation',
        'elevation_methods',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'requires_elevation' => 'boolean',
            'elevation_methods' => 'array',
            'status' => RoleStatus::class,
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function parentRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_inheritance', 'child_role_id', 'parent_role_id');
    }

    public function childRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_inheritance', 'parent_role_id', 'child_role_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function isSuperAdmin(): bool
    {
        $code = config('rbac.super_admin_role_code') ?: 'super_admin';

        return $this->is_system && $this->code === $code;
    }

    public function isActive(): bool
    {
        return $this->status === RoleStatus::Active;
    }
}
