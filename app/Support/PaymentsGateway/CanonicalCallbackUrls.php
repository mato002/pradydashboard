<?php

namespace App\Support\PaymentsGateway;

class CanonicalCallbackUrls
{
    public const LEGACY_PATH_FRAGMENT = '/api/v1/callbacks/';

    public const STATUS_CANONICAL = 'canonical';

    public const STATUS_MISSING = 'missing';

    public const STATUS_LEGACY_INTERNAL = 'legacy_internal';

    public const STATUS_MISMATCHED = 'mismatched';

    /**
     * @return list<string>
     */
    public static function fieldKeys(): array
    {
        return [
            'validation_url',
            'confirmation_url',
            'stk_callback_url',
            'b2c_result_url',
            'b2c_timeout_url',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function paths(): array
    {
        return [
            'validation_url' => '/pay/c2b/validate',
            'confirmation_url' => '/pay/c2b/confirm',
            'stk_callback_url' => '/pay/stk',
            'b2c_result_url' => '/pay/b2c/result',
            'b2c_timeout_url' => '/pay/b2c/timeout',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'validation_url' => 'C2B Validation',
            'confirmation_url' => 'C2B Confirmation',
            'stk_callback_url' => 'STK',
            'b2c_result_url' => 'B2C Result',
            'b2c_timeout_url' => 'B2C Timeout',
        ];
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('payment_gateway.base_url'), '/');
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $baseUrl = self::baseUrl();
        $urls = [];

        foreach (self::paths() as $field => $path) {
            $urls[$field] = $baseUrl.$path;
        }

        return $urls;
    }

    /**
     * @return list<array{field: string, label: string, url: string}>
     */
    public static function referenceTable(): array
    {
        $canonical = self::all();

        return array_map(
            fn (string $field): array => [
                'field' => $field,
                'label' => self::labels()[$field] ?? $field,
                'url' => $canonical[$field],
            ],
            self::fieldKeys()
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function prefillDefaults(array $values = []): array
    {
        $canonical = self::all();
        $prefilled = [];

        foreach (self::fieldKeys() as $field) {
            $current = $values[$field] ?? null;
            $prefilled[$field] = filled($current) ? (string) $current : $canonical[$field];
        }

        return $prefilled;
    }

    public static function isLegacyUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        return str_contains($url, self::LEGACY_PATH_FRAGMENT);
    }

    public static function isCanonical(string $field, ?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $expected = self::all()[$field] ?? null;

        if ($expected === null) {
            return false;
        }

        return self::normalizeUrl($url) === self::normalizeUrl($expected);
    }

    public static function classify(string $field, ?string $url): string
    {
        if (blank($url)) {
            return self::STATUS_MISSING;
        }

        if (self::isLegacyUrl($url)) {
            return self::STATUS_LEGACY_INTERNAL;
        }

        if (self::isCanonical($field, $url)) {
            return self::STATUS_CANONICAL;
        }

        return self::STATUS_MISMATCHED;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fieldsDifferFromCanonical(array $values): bool
    {
        foreach (self::fieldKeys() as $field) {
            $value = $values[$field] ?? null;

            if (blank($value)) {
                continue;
            }

            if (! self::isCanonical($field, (string) $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function hasLegacyUrls(array $values): bool
    {
        foreach (self::fieldKeys() as $field) {
            if (self::isLegacyUrl((string) ($values[$field] ?? ''))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $account
     * @return list<string>
     */
    public static function relevantFieldsForAccount(array $account): array
    {
        $fields = [];

        if ((bool) ($account['supports_c2b'] ?? false)) {
            $fields[] = 'validation_url';
            $fields[] = 'confirmation_url';
        }

        if ((bool) ($account['supports_stk'] ?? false)) {
            $fields[] = 'stk_callback_url';
        }

        if ((bool) ($account['supports_b2c'] ?? false)) {
            $fields[] = 'b2c_result_url';
            $fields[] = 'b2c_timeout_url';
        }

        return $fields !== [] ? $fields : self::fieldKeys();
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array{
     *     overall_status: string,
     *     needs_url_update: bool,
     *     fields: array<string, array{status: string, url: string|null, label: string}>
     * }
     */
    public static function assessAccount(array $account): array
    {
        $fields = [];
        $statuses = [];

        foreach (self::relevantFieldsForAccount($account) as $field) {
            $url = filled($account[$field] ?? null) ? (string) $account[$field] : null;
            $status = self::classify($field, $url);

            $fields[$field] = [
                'status' => $status,
                'url' => $url,
                'label' => self::labels()[$field] ?? $field,
            ];
            $statuses[] = $status;
        }

        $needsUpdate = collect($statuses)->contains(
            fn (string $status): bool => in_array($status, [self::STATUS_LEGACY_INTERNAL, self::STATUS_MISMATCHED], true)
        );

        $overallStatus = self::STATUS_CANONICAL;

        if (collect($statuses)->contains(self::STATUS_LEGACY_INTERNAL)) {
            $overallStatus = self::STATUS_LEGACY_INTERNAL;
        } elseif (collect($statuses)->contains(self::STATUS_MISMATCHED)) {
            $overallStatus = self::STATUS_MISMATCHED;
        } elseif (collect($statuses)->contains(self::STATUS_MISSING)) {
            $overallStatus = self::STATUS_MISSING;
        }

        return [
            'overall_status' => $overallStatus,
            'needs_url_update' => $needsUpdate,
            'fields' => $fields,
        ];
    }

    protected static function normalizeUrl(string $url): string
    {
        return rtrim(trim($url), '/');
    }
}
