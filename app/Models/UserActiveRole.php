<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActiveRole extends Model
{
    protected $fillable = [
        'user_id',
        'user_role_assignment_id',
        'activated_at',
        'expires_at',
        'elevation_verified_at',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'elevation_verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(UserRoleAssignment::class, 'user_role_assignment_id');
    }

    public function hasValidElevation(): bool
    {
        if (! $this->elevation_verified_at) {
            return false;
        }

        $ttl = config('rbac.elevation_ttl_minutes', 15);

        return $this->elevation_verified_at->greaterThan(now()->subMinutes($ttl));
    }
}
