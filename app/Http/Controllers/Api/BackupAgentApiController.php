<?php

namespace App\Http\Controllers\Api;

use App\Domain\Backups\BackupRequestHmacVerifier;
use App\Http\Controllers\Controller;
use App\Models\BackupAgent;
use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackupAgentApiController extends Controller
{
    public function __construct(
        private readonly BackupRequestHmacVerifier $hmac,
    ) {}

    public function registerAgent(Request $request): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        $data = $request->validate([
            'agent_key' => ['required', 'string', 'max:191'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'environment' => ['nullable', 'string', 'max:64'],
            'agent_version' => ['nullable', 'string', 'max:64'],
            'backup_agent_version' => ['nullable', 'string', 'max:64'],
            'capabilities' => ['nullable', 'array'],
            'php_version' => ['nullable', 'string', 'max:32'],
            'laravel_version' => ['nullable', 'string', 'max:32'],
            'tenant_key' => ['nullable', 'string', 'max:191'],
        ]);

        $tenant = $this->resolveTenant($project, $data['tenant_key'] ?? null);
        $version = $data['agent_version'] ?? $data['backup_agent_version'] ?? null;

        $agent = BackupAgent::query()->updateOrCreate(
            ['agent_key' => $data['agent_key']],
            [
                'hosted_project_id' => $project->id,
                'tenant_id' => $tenant?->id,
                'hostname' => $data['hostname'] ?? null,
                'environment' => $data['environment'] ?? null,
                'agent_version' => $version,
                'capabilities' => $data['capabilities'] ?? null,
                'php_version' => $data['php_version'] ?? null,
                'laravel_version' => $data['laravel_version'] ?? null,
                'status' => 'registered',
                'last_heartbeat_at' => now(),
            ],
        );

        return response()->json([
            'agent_id' => $agent->id,
            'agent_key' => $agent->agent_key,
            'status' => $agent->status,
        ], 201);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        $data = $request->validate([
            'agent_key' => ['required', 'string', 'max:191'],
            'disk_space_bytes' => ['nullable', 'integer', 'min:0'],
            'free_space_bytes' => ['nullable', 'integer', 'min:0'],
            'health' => ['nullable', 'string', 'max:64'],
            'agent_version' => ['nullable', 'string', 'max:64'],
        ]);

        $agent = BackupAgent::query()
            ->where('agent_key', $data['agent_key'])
            ->where('hosted_project_id', $project->id)
            ->first();

        if (! $agent) {
            return response()->json(['message' => 'Agent not registered.'], 404);
        }

        $agent->fill(array_filter([
            'last_heartbeat_at' => now(),
            'disk_space_bytes' => $data['disk_space_bytes'] ?? null,
            'free_space_bytes' => $data['free_space_bytes'] ?? null,
            'health' => $data['health'] ?? $agent->health,
            'agent_version' => $data['agent_version'] ?? $agent->agent_version,
            'status' => 'online',
        ], fn ($v) => $v !== null))->save();

        return response()->json([
            'accepted' => true,
            'agent_key' => $agent->agent_key,
            'last_heartbeat_at' => $agent->last_heartbeat_at?->toIso8601String(),
        ]);
    }

    private function project(Request $request): Project
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            abort(500, 'Project context missing.');
        }

        return $project;
    }

    private function resolveTenant(Project $project, ?string $tenantKey): ?Tenant
    {
        if (! filled($tenantKey)) {
            return null;
        }

        return Tenant::query()
            ->where('tenant_key', $tenantKey)
            ->where('hosted_project_id', $project->id)
            ->first();
    }
}
