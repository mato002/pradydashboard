<?php

namespace App\Domain\Tenancy\Support;

use Illuminate\Support\Facades\File;

final class TenantSystemContractPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function forDocumentation(): array
    {
        $stubPath = base_path('stubs/tenant-integration');

        return [
            'method' => TenantSystemInfoContract::ENDPOINT_METHOD,
            'path' => TenantSystemInfoContract::ENDPOINT_PATH,
            'full_endpoint' => TenantSystemInfoContract::ENDPOINT_METHOD.' '.TenantSystemInfoContract::ENDPOINT_PATH,
            'sample_payload' => TenantSystemInfoContract::samplePayload(),
            'sample_json' => json_encode(TenantSystemInfoContract::samplePayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'core_fields' => TenantSystemInfoContract::coreFields(),
            'recommended_fields' => TenantSystemInfoContract::recommendedFields(),
            'usage_keys' => TenantSystemInfoContract::usageMetricKeys(),
            'health_keys' => TenantSystemInfoContract::healthComponentKeys(),
            'env_variables' => TenantSystemInfoContract::tenantEnvVariables(),
            'auth_headers' => TenantSystemInfoContract::supportedAuthHeaders(),
            'stubs' => [
                'routes' => $this->readStub($stubPath.'/routes-api-snippet.php'),
                'controller' => $this->readStub($stubPath.'/SystemInfoController.php'),
                'middleware' => $this->readStub($stubPath.'/AuthenticatePradyDashboard.php'),
                'env' => $this->readStub($stubPath.'/env-prady-system-api-snippet.txt'),
            ],
        ];
    }

    private function readStub(string $path): string
    {
        if (! File::isFile($path)) {
            return '';
        }

        return trim(File::get($path));
    }
}
