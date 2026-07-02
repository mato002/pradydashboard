<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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
}
