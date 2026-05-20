<?php

namespace App\Models;

use App\Support\Rbac\RoleScopeType;
use App\Support\Rbac\UserRoleAssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoleAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'scope_type',
        'tenant_id',
        'project_id',
        'server_id',
        'starts_at',
        'expires_at',
        'status',
        'assigned_by',
        'assignment_reason',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'scope_type' => RoleScopeType::class,
            'status' => UserRoleAssignmentStatus::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActivatable(): bool
    {
        if ($this->status !== UserRoleAssignmentStatus::Active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if (! $this->role?->isActive()) {
            return false;
        }

        return true;
    }

    public function scopeLabel(): string
    {
        return match ($this->scope_type) {
            RoleScopeType::Global => __('Global'),
            RoleScopeType::Tenant => __('Tenant #:id', ['id' => $this->tenant_id]),
            RoleScopeType::Project => __('Project #:id', ['id' => $this->project_id]),
            RoleScopeType::Server => __('Server #:id', ['id' => $this->server_id]),
        };
    }
}
