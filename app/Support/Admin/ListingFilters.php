<?php

namespace App\Support\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ListingFilters
{
    /**
     * @param  list<string>  $columns
     */
    public static function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $query->where(function (Builder $q) use ($search, $columns): void {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn (Builder $rel) => $rel->where($field, 'like', "%{$search}%"));
                } else {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });

        return $query;
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public static function fromRequest(Request $request, array $keys): array
    {
        $filters = [];

        foreach ($keys as $key) {
            $filters[$key] = $request->query($key);
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    public static function queryExceptPage(Request $request): array
    {
        return $request->except('page');
    }
}
