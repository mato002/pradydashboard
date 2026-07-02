<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class DeploymentWebhookEvent extends Model
{
    protected $fillable = [
        'deployment_integration_id',
        'hosted_project_id',
        'project_id',
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
}
