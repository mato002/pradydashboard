<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDepartment extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_staff_id',
        'status',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'manager_staff_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'hr_department_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
