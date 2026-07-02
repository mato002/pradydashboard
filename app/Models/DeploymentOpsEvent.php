<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class DeploymentOpsEvent extends Model
{
    public const TYPE_CONTAINER = 'container_deploy';

    public const TYPE_INFRA = 'infra_change';

    public const TYPE_SCALING = 'scaling';

    protected $fillable = [
        'hosted_project_id',
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

    public static function hostedProjectForeignKey(): string
    {
        static $key = null;

        if ($key === null) {
            $table = (new static)->getTable();
            $key = Schema::hasColumn($table, 'hosted_project_id') ? 'hosted_project_id' : 'project_id';
        }

        return $key;
    }

    public function hostedProject(): BelongsTo
    {
        return $this->belongsTo(HostedProject::class, static::hostedProjectForeignKey());
    }

    /** @deprecated Use hostedProject() */
    public function project(): BelongsTo
    {
        return $this->hostedProject();
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->{static::hostedProjectForeignKey()};
    }

    public function setProjectIdAttribute(?int $value): void
    {
        $this->attributes[static::hostedProjectForeignKey()] = $value;
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
