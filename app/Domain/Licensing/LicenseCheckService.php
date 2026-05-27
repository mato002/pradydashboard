<?php

namespace App\Domain\Licensing;

use App\Domain\Tenancy\Services\TenantLicenseEvaluator;
use App\Domain\Tenancy\Services\TenantLicenseFormatter;
use App\Models\LicenseCheckLog;
use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LicenseCheckService
{
    public function __construct(
        private readonly TenantLicenseEvaluator $evaluator,
        private readonly TenantLicenseFormatter $formatter,
    ) {}

    /**
     * @return array{payload: array<string, mixed>, http_status: int, tenant: ?Tenant, project: Project}
     */
    public function check(Request $request, Project $project, string $tenantKey, string $productKey, string $domain): array
    {
        $resolvedProductKey = $project->resolveProductKey();

        if (strcasecmp($productKey, $resolvedProductKey) !== 0) {
            return [
                'payload' => ['message' => 'Product key does not match this API credential.'],
                'http_status' => 403,
                'tenant' => null,
                'project' => $project,
            ];
        }

        $tenant = $this->resolveTenant($project, $tenantKey);

        if (! $tenant) {
            return [
                'payload' => [
                    'message' => 'Tenant not found for this hosted project.',
                    'hint' => 'Confirm the tenant is linked to this hosted project and PRADY_TENANT_KEY matches tenant_key on the tenant record.',
                    'tenant_key' => $tenantKey,
                    'hosted_project_id' => $project->id,
                    'hosted_project' => $project->name,
                ],
                'http_status' => 404,
                'tenant' => null,
                'project' => $project,
            ];
        }

        if (! $this->domainMatchesTenant($tenant, $project, $domain)) {
            return [
                'payload' => ['message' => 'Domain does not match tenant registration.'],
                'http_status' => 403,
                'tenant' => $tenant,
                'project' => $project,
            ];
        }

        if (! $this->verifyTenantSignature($request, $tenant)) {
            return [
                'payload' => ['message' => 'Invalid license signature.'],
                'http_status' => 401,
                'tenant' => $tenant,
                'project' => $project,
            ];
        }

        $result = $this->evaluator->evaluate($project, $tenant);
        $payload = $this->formatter->toPublicApiArray($result);

        if (! ($payload['allowed'] ?? true)) {
            $billing = app(TenantLicenseBillingContext::class)->forTenant($tenant);
            $payload['billing'] = $billing ?? app(TenantLicenseBillingContext::class)->fallbackForTenant($tenant);
        }

        $tenant->update([
            'access_level' => $payload['access_level'],
        ]);

        $this->logCheck($request, $project, $tenant, $tenantKey, $productKey, $domain, $payload, 200);

        return [
            'payload' => $payload,
            'http_status' => 200,
            'tenant' => $tenant,
            'project' => $project,
        ];
    }

    public function resolveTenant(Project $project, string $tenantKey): ?Tenant
    {
        return Tenant::query()
            ->where('hosted_project_id', $project->id)
            ->where(function ($q) use ($tenantKey) {
                $q->where('tenant_key', $tenantKey)
                    ->orWhere('external_key', $tenantKey);
            })
            ->with(['latestAccessControl', 'licenseModules'])
            ->first();
    }

    private function domainMatchesTenant(Tenant $tenant, Project $project, string $domain): bool
    {
        $needle = Str::lower(trim($domain));

        if ($tenant->tenant_domain) {
            $registered = Str::lower(trim($tenant->tenant_domain));

            return $needle === $registered;
        }

        $base = Str::lower(trim($project->domain));

        return $needle === $base || Str::endsWith($needle, '.'.$base);
    }

    private function verifyTenantSignature(Request $request, Tenant $tenant): bool
    {
        if (! filled($tenant->license_secret)) {
            return true;
        }

        if (! config('prady.license.require_signature_when_secret_set', true)) {
            return true;
        }

        $signature = $request->header('X-Prady-Signature')
            ?? $request->header('X-License-Signature');

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $tenant->license_secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function logCheck(
        Request $request,
        Project $project,
        ?Tenant $tenant,
        string $tenantKey,
        string $productKey,
        string $domain,
        array $payload,
        int $httpStatus,
    ): void {
        if (! config('prady.license.log_checks', true)) {
            return;
        }

        LicenseCheckLog::query()->create([
            'tenant_id' => $tenant?->id,
            'hosted_project_id' => $project->id,
            'tenant_key' => $tenantKey,
            'product_key' => $productKey,
            'domain' => $domain,
            'decision' => $payload['access_level'] ?? 'unknown',
            'allowed' => (bool) ($payload['allowed'] ?? false),
            'tenant_status' => $payload['tenant_status'] ?? null,
            'access_level' => $payload['access_level'] ?? null,
            'http_status' => $httpStatus,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
            'auth_method' => $request->bearerToken() ? 'bearer' : 'project_token',
            'request_meta' => [
                'has_signature' => $request->hasHeader('X-Prady-Signature'),
            ],
            'checked_at' => now(),
        ]);
    }
}
