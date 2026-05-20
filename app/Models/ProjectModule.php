<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectModule extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'code',
        'description',
        'status',
        'is_billable',
        'default_enabled',
        'monthly_price',
        'setup_price',
        'dependency_notes',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
            'default_enabled' => 'boolean',
            'monthly_price' => 'decimal:2',
            'setup_price' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantProjectModuleSubscription::class);
    }
}
