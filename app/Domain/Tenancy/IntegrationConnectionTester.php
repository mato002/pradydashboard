<?php

namespace App\Domain\Tenancy;

use App\Domain\Tenancy\Support\IntegrationApiErrorFormatter;
use App\Models\TenantProjectServiceIntegration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class IntegrationConnectionTester
{
    public function __construct(
        private readonly TenantSystemApiClient $tenantSystemApiClient,
    ) {}

    /**
     * @return array{last_test_status: string, last_error: ?string, status: string, response_code?: int, response_time_ms?: int, payload_summary?: ?array}
     */
    public function test(TenantProjectServiceIntegration $integration): array
    {
        if ($integration->isTenantSystem()) {
            return $this->mapTenantSystemResult(
                $this->tenantSystemApiClient->testConnection($integration),
            );
        }

        return $this->testProvider($integration);
    }

    /**
     * @param  array{success: bool, response_code: int, response_time_ms: int, error: ?string, payload_summary: ?array}  $result
     * @return array{last_test_status: string, last_error: ?string, status: string, response_code: int, response_time_ms: int, payload_summary: ?array}
     */
    private function mapTenantSystemResult(array $result): array
    {
        return [
            'last_test_status' => $result['success'] ? 'pass' : 'fail',
            'last_error' => $result['error'],
            'status' => $result['success'] ? 'active' : 'failing',
            'response_code' => $result['response_code'],
            'response_time_ms' => $result['response_time_ms'],
            'payload_summary' => $result['payload_summary'],
        ];
    }

    /**
     * @return array{last_test_status: string, last_error: ?string, status: string, response_code: int, response_time_ms: int}
     */
    private function testProvider(TenantProjectServiceIntegration $integration): array
    {
        if (! filled($integration->endpoint_url)) {
            return [
                'last_test_status' => 'pending',
                'last_error' => __('No endpoint URL configured.'),
                'status' => $integration->status === 'not_configured' ? 'not_configured' : 'pending',
                'response_code' => 0,
                'response_time_ms' => 0,
            ];
        }

        $timeout = max(5, min(10, (int) config('integrations.api_timeout_seconds', 8)));
        $connectTimeout = max(3, min(8, (int) config('integrations.api_connect_timeout_seconds', 5)));
        $started = microtime(true);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->withOptions(['allow_redirects' => true, 'http_errors' => false])
                ->send('HEAD', $integration->endpoint_url);

            if (in_array($response->status(), [405, 501], true)) {
                $response = Http::timeout($timeout)
                    ->connectTimeout($connectTimeout)
                    ->withOptions(['allow_redirects' => true, 'http_errors' => false])
                    ->get($integration->endpoint_url);
            }

            $responseTimeMs = (int) round((microtime(true) - $started) * 1000);
            $ok = $response->successful();

            return [
                'last_test_status' => $ok ? 'pass' : 'fail',
                'last_error' => $ok ? null : IntegrationApiErrorFormatter::format(null, $response->status()),
                'status' => $ok ? 'active' : 'failing',
                'response_code' => $response->status(),
                'response_time_ms' => $responseTimeMs,
            ];
        } catch (ConnectionException $e) {
            return $this->providerFailure($started, IntegrationApiErrorFormatter::format($e));
        } catch (\Throwable $e) {
            return $this->providerFailure($started, IntegrationApiErrorFormatter::format($e));
        }
    }

    /**
     * @return array{last_test_status: string, last_error: string, status: string, response_code: int, response_time_ms: int}
     */
    private function providerFailure(float $started, string $error): array
    {
        return [
            'last_test_status' => 'fail',
            'last_error' => $error,
            'status' => 'failing',
            'response_code' => 0,
            'response_time_ms' => (int) round((microtime(true) - $started) * 1000),
        ];
    }
}
