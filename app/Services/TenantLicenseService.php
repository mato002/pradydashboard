<?php

namespace App\Services;

use App\Domain\Tenancy\DTOs\TenantLicenseResult;
use App\Domain\Tenancy\Services\TenantLicenseEvaluator;
use App\Domain\Tenancy\Services\TenantLicenseFormatter;
use App\Models\Project;
use App\Models\Tenant;

/**
 * Application façade for license evaluation (v1 legacy JSON + callers that need the DTO).
 */
class TenantLicenseService
{
    public function __construct(
        private readonly TenantLicenseEvaluator $evaluator,
        private readonly TenantLicenseFormatter $formatter
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function evaluate(Project $project, Tenant $tenant): array
    {
        return $this->formatter->toLegacyArray($this->evaluator->evaluate($project, $tenant));
    }

    public function evaluateResult(Project $project, Tenant $tenant): TenantLicenseResult
    {
        return $this->evaluator->evaluate($project, $tenant);
    }
}
