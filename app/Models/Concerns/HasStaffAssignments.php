<?php

namespace App\Models\Concerns;

use App\Models\StaffAssignment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStaffAssignments
{
    public function staffAssignments(): MorphMany
    {
        return $this->morphMany(StaffAssignment::class, 'assignable');
    }

    public function activeStaffAssignments(): MorphMany
    {
        return $this->staffAssignments()->where('status', 'active')->with('staffProfile.department');
    }
}
