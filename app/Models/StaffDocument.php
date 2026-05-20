<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    protected $fillable = [
        'staff_profile_id',
        'title',
        'document_type',
        'file_path',
        'signed_date',
        'expiry_date',
        'notes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isContract(): bool
    {
        return $this->document_type === 'contract';
    }
}
