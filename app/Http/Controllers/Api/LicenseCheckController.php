<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tenant;
use App\Services\TenantLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseCheckController extends Controller
{
    public function __construct(
        private readonly TenantLicenseService $licenseService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            return response()->json(['message' => 'Project context missing.'], 500);
        }

        $data = $request->validate([
            'tenant_key' => ['required', 'uuid'],
        ]);

        $tenant = Tenant::query()
            ->where('external_key', $data['tenant_key'])
            ->where('project_id', $project->id)
            ->with(['latestAccessControl', 'licenseModules'])
            ->first();

        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant not found for this product.',
            ], 404);
        }

        return response()->json($this->licenseService->evaluate($project, $tenant));
    }
}
