<?php

namespace App\Http\Controllers\Api;

use App\Domain\Licensing\LicenseCheckService;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseCheckController extends Controller
{
    public function __construct(
        private readonly LicenseCheckService $licenseCheckService,
    ) {}

    /**
     * Central license gate for all hosted product systems.
     *
     * POST /api/v1/license/check
     * Authorization: Bearer {project_api_token}
     * Optional: X-Prady-Signature (HMAC-SHA256 of raw JSON body using tenant license_secret)
     */
    public function __invoke(Request $request): JsonResponse
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            return response()->json(['message' => 'Product context missing.'], 500);
        }

        $data = $request->validate([
            'tenant_key' => ['required', 'string', 'max:120'],
            'product_key' => ['required', 'string', 'max:80'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->licenseCheckService->check(
            $request,
            $project,
            $data['tenant_key'],
            $data['product_key'],
            $data['domain'],
        );

        return response()->json($result['payload'], $result['http_status']);
    }
}
