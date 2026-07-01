<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentWebhookEvent extends Model
{
    protected $fillable = [
        'deployment_integration_id',
        'hosted_project_id',
        'event_type',
        'status',
        'summary',
        'payload',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(DeploymentIntegration::class, 'deployment_integration_id');
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
