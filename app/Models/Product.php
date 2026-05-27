<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'status',
        'default_billing_model',
        'default_license_mode',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug) && filled($product->name)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });
    }

    public function hostedProjects(): HasMany
    {
        return $this->hasMany(HostedProject::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class);
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'product';
        $suffix = 0;

        while (static::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }
}
