<?php

namespace App\Models;

use App\Models\Concerns\HasStaffAssignments;
use Illuminate\Database\Eloquent\Model;

class InternalTask extends Model
{
    use HasStaffAssignments;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }
}
