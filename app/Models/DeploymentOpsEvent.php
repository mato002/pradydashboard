<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentOpsEvent extends Model
{
    public const TYPE_CONTAINER = 'container_deploy';

    public const TYPE_INFRA = 'infra_change';

    public const TYPE_SCALING = 'scaling';

    protected $fillable = [
        'project_id',
        'server_id',
        'project_deployment_id',
        'type',
        'summary',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(ProjectDeployment::class, 'project_deployment_id');
    }
}
