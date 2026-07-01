<?php

namespace App\Support\Discovery;

use App\Models\User;
use App\Support\Rbac\Rbac;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class FeatureRegistry
{
    /**
     * @return list<array<string, mixed>>
     */
    public function index(): array
    {
        $entries = [];

        foreach (config('feature_registry.entries', []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $entry = $this->makeEntry($item);

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $this->deduplicateEntries($entries);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query): array
    {
        $tokens = $this->tokenize($query);

        if ($tokens === []) {
            return [];
        }

        return array_values(array_filter(
            $this->index(),
            fn (array $entry) => $this->entryMatchesTokens($entry, $tokens),
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchForClient(string $query, int $limit = 24): array
    {
        return array_map(
            fn (array $entry) => $this->serializeForClient($entry),
            array_slice($this->search($query), 0, $limit),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function indexForClient(): array
    {
        return array_map(function (array $entry) {
            $client = $this->serializeForClient($entry);
            $client['search_text'] = (string) ($entry['search_text'] ?? '');

            return $client;
        }, $this->index());
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    public function serializeForClient(array $entry): array
    {
        return [
            'id' => $entry['id'],
            'label' => $entry['label'],
            'description' => $entry['description'] ?? '',
            'path' => $entry['path'],
            'url' => $entry['url'],
            'category' => $entry['category'] ?? 'features',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    protected function makeEntry(array $item): ?array
    {
        if (! $this->itemIsAccessible($item)) {
            return null;
        }

        $routeName = (string) ($item['route'] ?? '');
        $routeParams = is_array($item['route_params'] ?? null) ? $item['route_params'] : [];

        if ($routeName === '' || ! Route::has($routeName)) {
            return null;
        }

        $label = (string) ($item['label'] ?? '');
        $group = (string) ($item['group'] ?? '');
        $path = $group !== '' ? $group.' → '.$label : $label;
        $url = route($routeName, $routeParams);
        $keywords = $this->expandKeywords(is_array($item['keywords'] ?? null) ? $item['keywords'] : []);

        $searchText = Str::lower(Str::ascii(implode(' ', array_filter([
            $label,
            $group,
            $path,
            $item['description'] ?? '',
            implode(' ', $keywords),
        ]))));

        return [
            'id' => hash('sha256', $routeName.'|'.json_encode($routeParams).'|'.$label),
            'label' => $label,
            'description' => (string) ($item['description'] ?? ''),
            'path' => $path,
            'url' => $url,
            'category' => (string) ($item['category'] ?? 'features'),
            'search_text' => $searchText,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function itemIsAccessible(array $item): bool
    {
        $permission = $item['permission'] ?? null;

        if (is_string($permission) && $permission !== '') {
            $user = auth()->user();

            if (! $user instanceof User || ! Rbac::userCan($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $keywords
     * @return list<string>
     */
    protected function expandKeywords(array $keywords): array
    {
        $aliases = config('feature_registry.keyword_aliases', []);
        $expanded = $keywords;

        foreach ($keywords as $keyword) {
            foreach ($aliases[$keyword] ?? [] as $alias) {
                $expanded[] = $alias;
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  list<string>  $tokens
     */
    protected function entryMatchesTokens(array $entry, array $tokens): bool
    {
        $haystack = (string) ($entry['search_text'] ?? '');

        foreach ($tokens as $token) {
            if (! $this->tokenMatchesHaystack($token, $haystack)) {
                return false;
            }
        }

        return true;
    }

    protected function tokenMatchesHaystack(string $token, string $haystack): bool
    {
        if (str_contains($haystack, $token)) {
            return true;
        }

        if (str_ends_with($token, 's') && strlen($token) > 3) {
            $singular = substr($token, 0, -1);

            if ($singular !== '' && str_contains($haystack, $singular)) {
                return true;
            }
        }

        if (! str_ends_with($token, 's') && str_contains($haystack, $token.'s')) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function tokenize(string $value): array
    {
        $normalized = Str::lower(Str::ascii($value));
        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', $normalized) ?? '';
        $parts = preg_split('/\s+/', trim($normalized)) ?: [];

        return array_values(array_filter($parts, fn (string $part) => $part !== ''));
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return list<array<string, mixed>>
     */
    protected function deduplicateEntries(array $entries): array
    {
        $seen = [];

        return array_values(array_filter($entries, function (array $entry) use (&$seen) {
            $id = (string) ($entry['id'] ?? '');

            if ($id === '' || isset($seen[$id])) {
                return false;
            }

            $seen[$id] = true;

            return true;
        }));
    }
}
