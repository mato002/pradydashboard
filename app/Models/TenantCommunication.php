<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantCommunication extends Model
{
    protected $fillable = [
        'tenant_id',
        'tenant_project_subscription_id',
        'staff_profile_id',
        'channel',
        'direction',
        'subject',
        'message',
        'communication_date',
        'follow_up_required',
        'follow_up_date',
        'status',
        'related_support_ticket_id',
    ];

    protected function casts(): array
    {
        return [
            'communication_date' => 'datetime',
            'follow_up_date' => 'date',
            'follow_up_required' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantProjectSubscription::class, 'tenant_project_subscription_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'related_support_ticket_id');
    }

    public function isOverdueFollowUp(): bool
    {
        return $this->follow_up_required
            && $this->status === 'pending_follow_up'
            && $this->follow_up_date
            && $this->follow_up_date->isPast();
    }
}
