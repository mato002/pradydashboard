<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaffAssignment extends Model
{
    protected $fillable = [
        'staff_profile_id',
        'assignable_type',
        'assignable_id',
        'role_on_assignment',
        'responsibility_notes',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignableLabel(): string
    {
        $target = $this->assignable;

        return match (true) {
            $target instanceof Project => $target->name,
            $target instanceof Tenant => $target->company_name,
            $target instanceof Server => $target->name,
            $target instanceof SupportTicket => $target->subject,
            $target instanceof InternalTask => $target->title,
            default => class_basename((string) $this->assignable_type).' #'.$this->assignable_id,
        };
    }

    public function assignableTypeLabel(): string
    {
        return match ($this->assignable_type) {
            Project::class => __('Project'),
            Tenant::class => __('Tenant'),
            Server::class => __('Server'),
            SupportTicket::class => __('Support ticket'),
            InternalTask::class => __('Internal task'),
            default => class_basename((string) $this->assignable_type),
        };
    }
}
