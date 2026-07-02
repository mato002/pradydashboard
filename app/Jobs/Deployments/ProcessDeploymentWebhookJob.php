<?php

namespace App\Jobs\Deployments;

use App\Domain\Deployments\DeploymentOpsRecorder;
use App\Domain\Deployments\DeploymentPipelineBuilder;
use App\Domain\Deployments\DeploymentWebhookProcessor;
use App\Jobs\OperationalJob;
use App\Models\DeploymentWebhookEvent;
use App\Models\ProjectDeployment;
use App\Support\Queue\QueueName;

class ProcessDeploymentWebhookJob extends OperationalJob
{
    public function __construct(
        public int $webhookEventId,
    ) {
        $this->onQueue(QueueName::INTEGRATIONS);
    }

    public function handle(DeploymentWebhookProcessor $processor): void
    {
        $event = DeploymentWebhookEvent::query()
            ->with('integration')
            ->find($this->webhookEventId);

        if ($event === null) {
            return;
        }

        $result = $processor->process($event);

        if ($result['deployment'] instanceof ProjectDeployment) {
            ExecuteDeploymentJob::dispatch($result['deployment']->id);
        }
    }
}
