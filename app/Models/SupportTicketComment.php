<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketComment extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'staff_profile_id',
        'user_id',
        'comment_type',
        'message',
        'visibility',
        'attachment_path',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorName(): string
    {
        return $this->staffProfile?->full_name
            ?? $this->user?->name
            ?? __('System');
    }
}
