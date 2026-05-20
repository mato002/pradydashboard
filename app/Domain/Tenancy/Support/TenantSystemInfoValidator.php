<?php

namespace App\Domain\Tenancy\Support;

final class TenantSystemInfoValidator
{
    public function __construct(
        private readonly IntegrationPayloadSummarizer $payloadSummarizer,
    ) {}

    /**
     * @return array{
     *     status: 'valid'|'partial'|'invalid',
     *     missing_required: list<string>,
     *     missing_recommended: list<string>,
     *     present_fields: list<string>,
     *     issues: list<string>
     * }
     */
    public function validate(mixed $body): array
    {
        if (! is_array($body)) {
            return $this->result('invalid', TenantSystemInfoContract::coreFields(), TenantSystemInfoContract::recommendedFields(), [], [
                __('Response body must be a JSON object.'),
            ]);
        }

        if ($this->containsSensitiveValues($body)) {
            return $this->result('invalid', [], [], [], [
                __('Response contains sensitive field names; remove secrets from the payload.'),
            ]);
        }

        $issues = [];
        $present = [];
        $missingRequired = [];
        $missingRecommended = [];

        foreach (TenantSystemInfoContract::coreFields() as $field) {
            if ($this->fieldPresent($body, $field)) {
                $present[] = $field;
            } else {
                $missingRequired[] = $field;
            }
        }

        if (in_array('version', $present, true) && ! $this->versionIsValid($body)) {
            $issues[] = __('Version field is missing or not in a supported format.');
            $missingRequired[] = 'version';
            $present = array_values(array_diff($present, ['version']));
        }

        foreach (TenantSystemInfoContract::recommendedFields() as $field) {
            if ($this->fieldPresent($body, $field)) {
                $present[] = $field;
            } else {
                $missingRecommended[] = $field;
            }
        }

        if ($missingRequired !== []) {
            return $this->result('invalid', $missingRequired, $missingRecommended, $present, $issues);
        }

        if ($missingRecommended !== []) {
            return $this->result('partial', [], $missingRecommended, $present, $issues);
        }

        return $this->result('valid', [], [], $present, $issues);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function fieldPresent(array $body, string $field): bool
    {
        $value = data_get($body, $field)
            ?? data_get($body, "data.{$field}")
            ?? data_get($body, "app.{$field}");

        if ($value === null) {
            return false;
        }

        if (in_array($field, ['usage', 'health'], true)) {
            return is_array($value) && $value !== [];
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return is_scalar($value);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function versionIsValid(array $body): bool
    {
        $version = data_get($body, 'version')
            ?? data_get($body, 'data.version')
            ?? data_get($body, 'app.version');

        return $this->payloadSummarizer->normalizeVersion($version) !== null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function containsSensitiveValues(array $data, int $depth = 0): bool
    {
        if ($depth > 3) {
            return false;
        }

        foreach ($data as $key => $value) {
            $keyString = strtolower((string) $key);

            foreach (['password', 'secret', 'private_key', 'api_secret', 'license_secret'] as $fragment) {
                if (str_contains($keyString, $fragment)) {
                    return true;
                }
            }

            if (is_array($value) && $this->containsSensitiveValues($value, $depth + 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $missingRequired
     * @param  list<string>  $missingRecommended
     * @param  list<string>  $present
     * @param  list<string>  $issues
     * @return array{
     *     status: 'valid'|'partial'|'invalid',
     *     missing_required: list<string>,
     *     missing_recommended: list<string>,
     *     present_fields: list<string>,
     *     issues: list<string>
     * }
     */
    private function result(
        string $status,
        array $missingRequired,
        array $missingRecommended,
        array $present,
        array $issues,
    ): array {
        return [
            'status' => $status,
            'missing_required' => array_values(array_unique($missingRequired)),
            'missing_recommended' => array_values(array_unique($missingRecommended)),
            'present_fields' => array_values(array_unique($present)),
            'issues' => $issues,
        ];
    }
}
