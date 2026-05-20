<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'style',
        'blade_view',
        'css',
        'branding',
        'paper_size',
        'orientation',
        'active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class);
    }

    public function brandingValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->branding ?? [], $key, $default);
    }
}
