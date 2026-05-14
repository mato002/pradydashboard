<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LicenseModule extends Model
{
    protected $table = 'license_module_catalog';

    protected $fillable = [
        'key',
        'label',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_modules', 'license_module_id', 'tenant_id')
            ->withPivot('enabled')
            ->withTimestamps();
    }
}
