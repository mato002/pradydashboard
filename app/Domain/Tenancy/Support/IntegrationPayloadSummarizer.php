<?php

namespace App\Domain\Tenancy\Support;

use Illuminate\Support\Str;

final class IntegrationPayloadSummarizer
{
    /** @var list<string> */
    private const VERSION_KEYS = ['version', 'build', 'commit', 'environment', 'status', 'health'];

    /** @var list<string> */
    private const SENSITIVE_FRAGMENTS = [
        'password',
        'secret',
        'token',
        'authorization',
        'api_key',
        'apikey',
        'credential',
        'private',
        'bearer',
    ];

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function summarizeResponse(array $body, bool $heartbeat = false): array
    {
        $summary = [
            'has_version' => $this->hasVersionFields($body),
            'has_usage' => $this->hasUsageFields($body),
        ];

        if ($heartbeat) {
            $summary['heartbeat_at'] = now()->toIso8601String();
        }

        foreach (self::VERSION_KEYS as $key) {
            $value = data_get($body, $key)
                ?? data_get($body, "data.{$key}")
                ?? data_get($body, "app.{$key}");

            if ($value !== null && is_scalar($value)) {
                $summary[$key] = Str::limit((string) $value, 120);
            }
        }

        return $summary;
    }

    /**
     * @param  mixed  $usage
     * @return array<string, mixed>|null
     */
    public function summarizeUsage(mixed $usage): ?array
    {
        if ($usage === null) {
            return null;
        }

        try {
            if (is_object($usage)) {
                $usage = json_decode(json_encode($usage, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable) {
            return ['note' => __('Usage payload could not be parsed.')];
        }

        if (! is_array($usage)) {
            return is_scalar($usage)
                ? ['value' => Str::limit((string) $usage, 120)]
                : null;
        }

        return $this->flattenSafeMetrics($usage, depth: 0);
    }

    public function normalizeVersion(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $version = trim($value);

        if ($version === '' || strlen($version) > 64) {
            return null;
        }

        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9._\-+]*$/', $version)) {
            return null;
        }

        return $version;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function hasVersionFields(array $body): bool
    {
        return $this->normalizeVersion(
            data_get($body, 'version')
            ?? data_get($body, 'data.version')
            ?? data_get($body, 'app.version')
        ) !== null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function hasUsageFields(array $body): bool
    {
        return data_get($body, 'usage') !== null
            || data_get($body, 'metrics') !== null
            || data_get($body, 'data.usage') !== null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function flattenSafeMetrics(array $data, int $depth): array
    {
        if ($depth > 2) {
            return ['truncated' => true];
        }

        $out = [];
        $count = 0;

        foreach ($data as $key => $value) {
            if ($count >= 15) {
                $out['_truncated'] = true;
                break;
            }

            $keyString = (string) $key;

            if ($this->isSensitiveKey($keyString)) {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->flattenSafeMetrics($value, $depth + 1);
                if ($nested !== []) {
                    $out[$keyString] = $nested;
                    $count++;
                }

                continue;
            }

            if (is_scalar($value)) {
                $out[$keyString] = is_numeric($value)
                    ? $value
                    : Str::limit((string) $value, 80);
                $count++;
            }
        }

        return $out;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        foreach (self::SENSITIVE_FRAGMENTS as $fragment) {
            if (str_contains($lower, $fragment)) {
                return true;
            }
        }

        return $lower === 'key' || str_ends_with($lower, '_key');
    }
}
