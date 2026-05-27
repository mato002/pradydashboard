<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\TenantUsageMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantUsageHeartbeatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            return response()->json(['message' => 'Project context missing.'], 500);
        }

        $data = $request->validate([
            'tenant_key' => ['required', 'uuid'],
            'active_users' => ['nullable', 'integer', 'min:0'],
            'database_size_mb' => ['nullable', 'numeric', 'min:0'],
            'storage_usage_mb' => ['nullable', 'numeric', 'min:0'],
            'last_login_at' => ['nullable', 'date'],
            'server_cpu_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reported_app_version' => ['nullable', 'string', 'max:64'],
        ]);

        $tenant = Tenant::query()
            ->where('external_key', $data['tenant_key'])
            ->where('hosted_project_id', $project->id)
            ->first();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not found for this product.'], 404);
        }

        $metric = TenantUsageMetric::query()->firstOrNew(['tenant_id' => $tenant->id]);

        if (array_key_exists('active_users', $data)) {
            $metric->active_users = $data['active_users'];
        }
        if (array_key_exists('database_size_mb', $data)) {
            $metric->database_size_mb = $data['database_size_mb'];
        }
        if (array_key_exists('storage_usage_mb', $data)) {
            $metric->storage_usage_mb = $data['storage_usage_mb'];
        }
        if (array_key_exists('last_login_at', $data) && $data['last_login_at'] !== null) {
            $metric->last_login_at = $data['last_login_at'];
        }
        if (array_key_exists('server_cpu_percent', $data)) {
            $metric->server_cpu_percent = $data['server_cpu_percent'];
        }
        if (array_key_exists('reported_app_version', $data)) {
            $metric->reported_app_version = $data['reported_app_version'];
        }

        $metric->last_sync_at = now();
        $metric->captured_at = now();
        $metric->save();

        return response()->json(['accepted' => true, 'captured_at' => now()->toIso8601String()]);
    }
}
