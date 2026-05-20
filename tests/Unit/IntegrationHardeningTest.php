<?php

namespace Tests\Unit;

use App\Domain\Tenancy\Support\IntegrationApiErrorFormatter;
use App\Domain\Tenancy\Support\IntegrationPayloadSummarizer;
use App\Models\TenantProjectServiceIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Tests\TestCase;

class IntegrationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_uptime_and_average_response_time_calculations(): void
    {
        $integration = TenantProjectServiceIntegration::query()->make([
            'tenant_project_subscription_id' => 1,
            'service_type' => 'tenant_system',
            'display_name' => 'Test',
            'success_count' => 0,
            'failure_count' => 0,
        ]);

        $integration->recordCheckResult(true, 200, 100);
        $this->assertSame(100, $integration->average_response_time_ms);
        $this->assertSame(100.0, (float) $integration->uptime_percentage);

        $integration->recordCheckResult(true, 200, 300);
        $this->assertSame(200, $integration->average_response_time_ms);

        $integration->recordCheckResult(false, 503, 50, 'Service unavailable');
        $this->assertEqualsWithDelta(66.67, (float) $integration->uptime_percentage, 0.01);
        $this->assertSame(150, $integration->average_response_time_ms);
    }

    public function test_payload_summarizer_redacts_sensitive_usage_keys(): void
    {
        $summarizer = new IntegrationPayloadSummarizer;

        $summary = $summarizer->summarizeUsage([
            'active_users' => 42,
            'api_secret' => 'should-not-appear',
            'nested' => ['token' => 'hidden', 'requests' => 10],
        ]);

        $this->assertSame(42, $summary['active_users'] ?? null);
        $this->assertArrayNotHasKey('api_secret', $summary);
        $this->assertArrayNotHasKey('token', $summary['nested'] ?? []);
        $this->assertSame(10, $summary['nested']['requests'] ?? null);
    }

    public function test_version_normalization_rejects_invalid_values(): void
    {
        $summarizer = new IntegrationPayloadSummarizer;

        $this->assertSame('3.4.1', $summarizer->normalizeVersion('3.4.1'));
        $this->assertNull($summarizer->normalizeVersion(''));
        $this->assertNull($summarizer->normalizeVersion('bad version!'));
        $this->assertNull($summarizer->normalizeVersion(['array']));
    }

    public function test_connection_errors_are_human_readable(): void
    {
        $message = IntegrationApiErrorFormatter::format(new ConnectionException('cURL error 7'));

        $this->assertStringContainsString('connect', strtolower($message));
        $this->assertStringNotContainsString('cURL error', $message);
    }
}
