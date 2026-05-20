<?php

namespace App\Domain\Tenancy;

use App\Domain\Tenancy\Support\IntegrationApiErrorFormatter;
use App\Domain\Tenancy\Support\IntegrationPayloadSummarizer;
use App\Domain\Tenancy\Support\TenantSystemInfoValidator;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectVersion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TenantSystemApiClient
{
    public function __construct(
        private readonly IntegrationPayloadSummarizer $payloadSummarizer,
        private readonly TenantSystemInfoValidator $contractValidator,
    ) {}

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    public function testConnection(TenantProjectServiceIntegration $integration): array
    {
        return $this->performGet($integration, syncVersion: false, syncUsage: false);
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    public function pullSystemInfo(TenantProjectServiceIntegration $integration): array
    {
        return $this->performGet($integration, syncVersion: true, syncUsage: true);
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    public function pullVersionInfo(TenantProjectServiceIntegration $integration): array
    {
        return $this->performGet($integration, syncVersion: true, syncUsage: false);
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    public function pullUsageStats(TenantProjectServiceIntegration $integration): array
    {
        return $this->performGet($integration, syncVersion: false, syncUsage: true);
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    public function recordHeartbeat(TenantProjectServiceIntegration $integration): array
    {
        return $this->performGet($integration, syncVersion: false, syncUsage: false, heartbeat: true);
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}
     */
    private function performGet(
        TenantProjectServiceIntegration $integration,
        bool $syncVersion,
        bool $syncUsage,
        bool $heartbeat = false,
    ): array {
        if (! filled($integration->endpoint_url)) {
            return [
                'success' => false,
                'response_code' => 0,
                'response_time_ms' => 0,
                'error' => __('No endpoint URL configured.'),
                'payload_summary' => null,
            ];
        }

        $started = microtime(true);

        try {
            $response = $this->httpClient($integration)->get($integration->endpoint_url);
            $responseTimeMs = (int) round((microtime(true) - $started) * 1000);
            $success = $response->successful();
            $error = $success
                ? null
                : IntegrationApiErrorFormatter::format(null, $response->status());

            $payloadSummary = null;
            $body = $response->json();

            if (is_array($body)) {
                $payloadSummary = $this->payloadSummarizer->summarizeResponse($body, $heartbeat);
                $payloadSummary = array_merge(
                    $payloadSummary,
                    $this->contractSummary($body, $this->shouldValidateContract($integration)),
                );

                if ($syncVersion) {
                    $this->syncVersionFromPayload($integration, $body);
                }

                if ($syncUsage) {
                    $this->syncUsageFromPayload($integration, $body);
                }
            }

            return [
                'success' => $success,
                'response_code' => $response->status(),
                'response_time_ms' => $responseTimeMs,
                'error' => $error,
                'payload_summary' => $payloadSummary,
            ];
        } catch (ConnectionException $e) {
            return $this->failureResult($started, IntegrationApiErrorFormatter::format($e));
        } catch (\Throwable $e) {
            return $this->failureResult($started, IntegrationApiErrorFormatter::format($e));
        }
    }

    /**
     * @return array{success: bool, response_code: int, response_time_ms: int, error: string, payload_summary: null}
     */
    private function failureResult(float $started, string $error): array
    {
        return [
            'success' => false,
            'response_code' => 0,
            'response_time_ms' => (int) round((microtime(true) - $started) * 1000),
            'error' => $error,
            'payload_summary' => null,
        ];
    }

    private function httpClient(TenantProjectServiceIntegration $integration): PendingRequest
    {
        $timeout = max(5, min(10, (int) config('integrations.api_timeout_seconds', 8)));
        $connectTimeout = max(3, min(8, (int) config('integrations.api_connect_timeout_seconds', 5)));

        $client = Http::timeout($timeout)
            ->connectTimeout($connectTimeout)
            ->withOptions(['allow_redirects' => true, 'http_errors' => false])
            ->acceptJson();

        $secret = $integration->decryptedApiSecret();
        $authType = $integration->authentication_type ?? 'none';

        return match ($authType) {
            'bearer_token' => $secret ? $client->withToken($secret) : $client,
            'api_key_header' => $secret ? $client->withHeaders(['X-API-Key' => $secret]) : $client,
            'basic_auth' => $this->withBasicAuth($client, $secret),
            default => $client,
        };
    }

    private function shouldValidateContract(TenantProjectServiceIntegration $integration): bool
    {
        if ($integration->purpose === 'system_info') {
            return true;
        }

        $url = strtolower((string) $integration->endpoint_url);

        return str_contains($url, 'system/info');
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function contractSummary(array $body, bool $validate): array
    {
        if (! $validate) {
            return [];
        }

        $validation = $this->contractValidator->validate($body);

        return [
            'contract_health' => $validation['status'],
            'contract_missing_required' => $validation['missing_required'],
            'contract_missing_recommended' => $validation['missing_recommended'],
            'contract_issues' => $validation['issues'],
        ];
    }

    private function withBasicAuth(PendingRequest $client, ?string $secret): PendingRequest
    {
        if (! $secret || ! str_contains($secret, ':')) {
            return $client;
        }

        [$user, $pass] = explode(':', $secret, 2);

        return $client->withBasicAuth($user, $pass);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function syncVersionFromPayload(TenantProjectServiceIntegration $integration, array $body): void
    {
        $version = $this->payloadSummarizer->normalizeVersion(
            data_get($body, 'version')
            ?? data_get($body, 'data.version')
            ?? data_get($body, 'app.version')
        );

        if ($version === null) {
            return;
        }

        $build = data_get($body, 'build') ?? data_get($body, 'data.build') ?? data_get($body, 'app.build');
        $commit = data_get($body, 'commit') ?? data_get($body, 'data.commit') ?? data_get($body, 'app.commit');
        $environment = data_get($body, 'environment') ?? data_get($body, 'data.environment');

        $buildNumber = is_scalar($build) ? trim((string) $build) : null;
        $commitHash = is_scalar($commit) ? trim((string) $commit) : null;

        if ($buildNumber !== null && strlen($buildNumber) > 64) {
            $buildNumber = null;
        }

        if ($commitHash !== null && strlen($commitHash) > 128) {
            $commitHash = null;
        }

        $notes = collect([
            is_scalar($environment) && $environment !== ''
                ? __('Environment: :env', ['env' => Str::limit((string) $environment, 40)])
                : null,
        ])->filter()->implode(' · ');

        TenantProjectVersion::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $integration->tenant_project_subscription_id],
            array_filter([
                'current_version' => $version,
                'build_number' => $buildNumber,
                'commit_hash' => $commitHash,
                'last_checked_at' => now(),
                'update_notes' => $notes !== '' ? $notes : null,
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function syncUsageFromPayload(TenantProjectServiceIntegration $integration, array $body): void
    {
        try {
            $usage = data_get($body, 'usage')
                ?? data_get($body, 'metrics')
                ?? data_get($body, 'data.usage');

            $summary = $this->payloadSummarizer->summarizeUsage($usage);

            if ($summary === null || $summary === []) {
                return;
            }

            $integration->last_payload_summary = array_merge(
                $integration->last_payload_summary ?? [],
                ['usage' => $summary],
            );
        } catch (\Throwable) {
            // Never break the request flow on unexpected usage shapes.
        }
    }
}
