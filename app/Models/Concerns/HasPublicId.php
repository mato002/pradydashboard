<?php

namespace App\Models\Concerns;

use App\Support\PublicId\PublicIdFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function (Model $model): void {
            if (filled($model->public_id)) {
                return;
            }

            $model->public_id = static::generateUniquePublicId();
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('public_id') && filled($model->getOriginal('public_id'))) {
                $model->public_id = $model->getOriginal('public_id');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Resolve binding by public_id, with legacy numeric id fallback during transition.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (static::isLegacyNumericId($value)) {
            return static::query()->whereKey((int) $value)->first();
        }

        return static::query()->where($this->getRouteKeyName(), $value)->first();
    }

    public static function isLegacyNumericId(mixed $value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        return (string) (int) $value === (string) $value;
    }

    public static function isValidPublicIdFormat(string $publicId): bool
    {
        return PublicIdFormat::isValid($publicId);
    }

    public static function generateUniquePublicId(): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $token = Str::lower(Str::random(4)).Str::upper(Str::random(4));

            if (! static::query()->where('public_id', $token)->exists()) {
                return $token;
            }
        }

        return Str::random(12);
    }
}
