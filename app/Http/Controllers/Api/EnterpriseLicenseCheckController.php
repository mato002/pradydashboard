<?php

namespace App\Http\Controllers\Api;

use App\Domain\Tenancy\Services\TenantLicenseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tenant;
use App\Services\TenantLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnterpriseLicenseCheckController extends Controller
{
    public function __construct(
        private readonly TenantLicenseService $licenseService,
        private readonly TenantLicenseFormatter $formatter
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            return response()->json(['message' => 'Project context missing.'], 500);
        }

        $data = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'domain' => ['required', 'string', 'max:255'],
            'product' => ['required', 'string', 'max:80'],
        ]);

        if (! $project->product_slug) {
            return response()->json([
                'message' => 'This product is not configured with a product_slug. Set it on the hosted project record.',
            ], 422);
        }

        if ($project->product_slug !== $data['product']) {
            return response()->json(['message' => 'Product does not match this API credential.'], 403);
        }

        $tenant = Tenant::query()
            ->whereKey($data['tenant_id'])
            ->where('hosted_project_id', $project->id)
            ->with(['latestAccessControl', 'licenseModules'])
            ->first();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not found for this product.'], 404);
        }

        if (! $this->domainMatchesTenant($tenant, $project, $data['domain'])) {
            return response()->json(['message' => 'Domain does not match this tenant registration.'], 403);
        }

        $result = $this->licenseService->evaluateResult($project, $tenant);

        return response()->json(
            $this->formatter->toEnterpriseArray($result)
        );
    }

    private function domainMatchesTenant(Tenant $tenant, Project $project, string $domain): bool
    {
        $needle = Str::lower(trim($domain));
        $registered = $tenant->tenant_domain ? Str::lower(trim($tenant->tenant_domain)) : null;

        if ($registered !== null && $registered !== '') {
            return $needle === $registered;
        }

        $base = Str::lower(trim($project->domain));

        return $needle === $base || Str::endsWith($needle, '.'.$base);
    }
}
