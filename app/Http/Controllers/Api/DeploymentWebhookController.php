<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Deployments\ProcessDeploymentWebhookJob;
use App\Models\DeploymentIntegration;
use App\Models\DeploymentWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeploymentWebhookController extends Controller
{
    public function __invoke(Request $request, DeploymentIntegration $integration): JsonResponse
    {
        $payload = $request->all();
        $eventType = $this->resolveEventType($payload);
        $summary = $this->resolveSummary($payload, $integration);

        $event = DeploymentWebhookEvent::query()->create([
            'deployment_integration_id' => $integration->id,
            'hosted_project_id' => null,
            'event_type' => $eventType,
            'status' => 'received',
            'summary' => $summary,
            'payload' => $payload,
            'received_at' => now(),
        ]);

        ProcessDeploymentWebhookJob::dispatch($event->id);

        $integration->update([
            'last_synced_at' => now(),
            'webhooks_count' => (int) $integration->webhooks_count + 1,
        ]);

        return response()->json([
            'received' => true,
            'event_id' => $event->id,
        ], 202);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveEventType(array $payload): string
    {
        return (string) (
            $payload['action']
            ?? $payload['event']
            ?? $payload['object_kind']
            ?? $payload['event_type']
            ?? 'push'
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveSummary(array $payload, DeploymentIntegration $integration): string
    {
        $ref = $payload['ref'] ?? $payload['branch'] ?? null;
        $repo = data_get($payload, 'repository.full_name')
            ?? data_get($payload, 'project.path_with_namespace')
            ?? data_get($payload, 'repository.name');

        if ($repo && $ref) {
            return __(':provider hook for :repo (:ref)', [
                'provider' => $integration->name,
                'repo' => $repo,
                'ref' => $ref,
            ]);
        }

        return __(':provider deployment webhook received.', ['provider' => $integration->name]);
    }
}
