<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleSwitchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'from_assignment_id',
        'to_assignment_id',
        'from_role_name',
        'to_role_name',
        'reason',
        'elevation_method',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAssignment(): BelongsTo
    {
        return $this->belongsTo(UserRoleAssignment::class, 'from_assignment_id');
    }

    public function toAssignment(): BelongsTo
    {
        return $this->belongsTo(UserRoleAssignment::class, 'to_assignment_id');
    }
}
