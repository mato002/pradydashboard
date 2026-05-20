<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVersion extends Model
{
    protected $fillable = [
        'project_id',
        'version',
        'release_date',
        'release_type',
        'minimum_supported_version',
        'changelog',
        'migration_notes',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
