<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'hr_department_id',
        'staff_number',
        'full_name',
        'email',
        'phone',
        'job_title',
        'employment_type',
        'status',
        'start_date',
        'end_date',
        'monthly_salary',
        'currency',
        'emergency_contact',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_salary' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StaffProfile $staff): void {
            if (empty($staff->staff_number)) {
                $staff->staff_number = static::generateStaffNumber();
            }
        });
    }

    public static function generateStaffNumber(): string
    {
        $year = now()->format('Y');
        $sequence = static::query()->whereYear('created_at', now()->year)->count() + 1;

        return sprintf('STF-%s-%04d', $year, $sequence);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'hr_department_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->where('status', 'active')->orderByDesc('start_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_staff_id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(TenantCommunication::class, 'staff_profile_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function displayLabel(): string
    {
        return $this->full_name.($this->job_title ? ' · '.$this->job_title : '');
    }
}
