<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const LOGO_KEY = 'branding.logo_path';

    public const LOGO_DARK_KEY = 'branding.logo_dark_path';

    public const FAVICON_KEY = 'branding.favicon_path';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember(
            self::cacheKey($key),
            config('redis_cache.ttl.settings', 3600),
            fn () => static::query()->where('key', $key)->value('value') ?? $default
        );
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::cacheKey($key));
    }

    /**
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public static function getJson(string $key, array $default = []): array
    {
        $raw = static::get($key);

        if ($raw === null || $raw === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_merge($default, $decoded) : $default;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public static function setJson(string $key, array $value): void
    {
        static::set($key, json_encode($value));
    }

    public static function assetUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    public static function deleteStoredFile(?string $path): void
    {
        if ($path && ! filter_var($path, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function logoPath(): ?string
    {
        return static::get(self::LOGO_KEY);
    }

    public static function logoUrl(): ?string
    {
        $path = static::logoPath();

        if ($path === null || $path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    public static function deleteStoredLogo(): void
    {
        static::deleteStoredFile(static::logoPath());
    }

    public static function logoDarkUrl(): ?string
    {
        return static::assetUrl(static::get(self::LOGO_DARK_KEY));
    }

    public static function faviconUrl(): ?string
    {
        return static::assetUrl(static::get(self::FAVICON_KEY));
    }

    private static function cacheKey(string $key): string
    {
        return 'setting.'.str_replace('.', '_', $key);
    }
}
