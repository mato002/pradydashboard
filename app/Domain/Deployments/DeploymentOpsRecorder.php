<?php

namespace App\Domain\Deployments;

use App\Models\DeploymentOpsEvent;
use App\Models\ProjectDeployment;

class DeploymentOpsRecorder
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function recordForDeployment(ProjectDeployment $deployment, array $meta): void
    {
        $deployment->loadMissing('project.server');
        $project = $deployment->project;
        $serverId = $project?->server_id;
        $occurredAt = $deployment->deployed_at ?? $deployment->created_at ?? now();
        $environment = (string) ($meta['environment'] ?? 'production');
        $strategy = (string) ($meta['strategy'] ?? 'rolling');
        $status = (string) ($meta['status'] ?? 'success');

        DeploymentOpsEvent::query()->create([
            'project_id' => $deployment->project_id,
            'server_id' => $serverId,
            'project_deployment_id' => $deployment->id,
            'type' => DeploymentOpsEvent::TYPE_CONTAINER,
            'summary' => __('Container image promoted (:version)', ['version' => $deployment->version]),
            'metadata' => [
                'environment' => $environment,
                'strategy' => $strategy,
                'status' => $status,
            ],
            'occurred_at' => $occurredAt,
        ]);

        if (in_array($strategy, ['blue_green', 'canary'], true) || $environment === 'production') {
            DeploymentOpsEvent::query()->create([
                'project_id' => $deployment->project_id,
                'server_id' => $serverId,
                'project_deployment_id' => $deployment->id,
                'type' => DeploymentOpsEvent::TYPE_INFRA,
                'summary' => __('Infrastructure manifest applied for :env', ['env' => $environment]),
                'metadata' => ['strategy' => $strategy],
                'occurred_at' => $occurredAt->copy()->addSeconds(5),
            ]);
        }

        if ($status === 'success' && $serverId) {
            DeploymentOpsEvent::query()->create([
                'project_id' => $deployment->project_id,
                'server_id' => $serverId,
                'project_deployment_id' => $deployment->id,
                'type' => DeploymentOpsEvent::TYPE_SCALING,
                'summary' => __('Autoscaling policy evaluated after deploy'),
                'metadata' => ['replicas' => $meta['replicas'] ?? 2],
                'occurred_at' => $occurredAt->copy()->addSeconds(12),
            ]);
        }
    }
}
