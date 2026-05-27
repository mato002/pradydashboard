<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVersion extends Model
{
    protected $fillable = [
        'product_id',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @deprecated Use product() */
    public function project(): BelongsTo
    {
        return $this->product();
    }

    public function getProjectIdAttribute(): ?int
    {
        return $this->product_id;
    }

    public function setProjectIdAttribute(?int $value): void
    {
        $this->attributes['product_id'] = $value;
    }
}
