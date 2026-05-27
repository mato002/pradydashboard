<?php

namespace App\Models;

use App\Models\Concerns\HasStaffAssignments;
use App\Support\SupportOpsOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasStaffAssignments;

    protected $fillable = [
        'tenant_id',
        'tenant_project_subscription_id',
        'hosted_project_id',
        'assigned_staff_id',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'source',
        'opened_at',
        'due_at',
        'resolved_at',
        'closed_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'due_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function projectSubscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function project(): BelongsTo
    {
        return $this->hostedProject();
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class, 'hosted_project_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'assigned_staff_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class)->orderBy('created_at');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(TenantCommunication::class, 'related_support_ticket_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, SupportOpsOptions::openTicketStatuses(), true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen()
            && $this->due_at
            && $this->due_at->isPast();
    }

    public function isUrgent(): bool
    {
        return in_array($this->priority, ['high', 'urgent', 'critical'], true);
    }
}
