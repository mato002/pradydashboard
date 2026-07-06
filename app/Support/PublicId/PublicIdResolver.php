<?php

namespace App\Support\PublicId;

use Illuminate\Database\Eloquent\Model;

class PublicIdResolver
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function resolve(string $modelClass, mixed $value): ?Model
    {
        if (! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        /** @var Model $modelClass */
        if (method_exists($modelClass, 'isLegacyNumericId') && $modelClass::isLegacyNumericId($value)) {
            return $modelClass::query()->whereKey((int) $value)->first();
        }

        return $modelClass::query()->where((new $modelClass)->getRouteKeyName(), $value)->first();
    }
}
