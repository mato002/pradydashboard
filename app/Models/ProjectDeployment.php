<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDeployment extends Model
{
    protected $fillable = [
        'hosted_project_id',
        'version',
        'deployed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deployed_at' => 'datetime',
        ];
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class, 'hosted_project_id');
    }

    /** @deprecated Use hostedProject() */
    public function project(): BelongsTo
    {
        return $this->hostedProject();
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->hosted_project_id;
    }

    public function setProjectIdAttribute(?int $value): void
    {
        $this->attributes['hosted_project_id'] = $value;
    }
}
