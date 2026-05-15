<?php

namespace Database\Seeders;

use App\Domain\Deployments\DeploymentOpsRecorder;
use App\Domain\Deployments\DeploymentPipelineBuilder;
use App\Models\DeploymentIntegration;
use App\Models\DeploymentWebhookEvent;
use App\Models\Project;
use App\Models\ProjectDeployment;
use App\Models\Server;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeploymentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIntegrations();

        if (ProjectDeployment::query()->count() >= 12) {
            return;
        }

        if (Project::query()->doesntExist()) {
            $this->bootstrapProjects();
        }

        $projects = Project::query()->with('server')->get();
        if ($projects->isEmpty()) {
            return;
        }

        $statuses = ['success', 'success', 'success', 'failed', 'in_progress', 'queued', 'rolled_back', 'success', 'cancelled'];
        $branches = ['main', 'main', 'develop', 'release/2.4', 'hotfix/billing', 'feature/tenant-ui'];
        $triggers = ['GitHub Actions', 'GitLab CI', 'Manual Ops', 'Webhook', 'Scheduled'];
        $environments = ['production', 'staging', 'development', 'qa', 'sandbox'];

        $i = 0;
        foreach ($projects as $project) {
            foreach (range(0, 4) as $offset) {
                $status = $statuses[($i + $offset) % count($statuses)];
                $deployedAt = $status === 'queued' ? null : now()->subHours($i * 6 + $offset * 2);
                $version = 'v2.'.(4 + ($i % 3)).'.'.($offset + 1);
                $environment = $environments[($i + $offset) % count($environments)];
                $triggeredBy = $triggers[($i + $offset) % count($triggers)];

                $notes = DeploymentPipelineBuilder::buildNotes([
                    'status' => $status,
                    'environment' => $environment,
                    'branch' => $branches[($i + $offset) % count($branches)],
                    'triggered_by' => $triggeredBy,
                    'duration_sec' => 45 + (($i * 17 + $offset * 11) % 240),
                    'commit' => substr(Str::uuid()->toString(), 0, 7),
                    'strategy' => ($i % 3) === 0 ? 'blue_green' : (($i % 3) === 1 ? 'canary' : 'rolling'),
                    'version' => $version,
                    'deployed_at' => $deployedAt,
                ], $project);

                $deployment = ProjectDeployment::query()->create([
                    'project_id' => $project->id,
                    'version' => $version,
                    'deployed_at' => $deployedAt,
                    'notes' => json_encode($notes),
                ]);

                if ($deployedAt) {
                    DeploymentOpsRecorder::recordForDeployment($deployment, $notes);
                }
            }
            $i++;
        }
    }

    private function seedIntegrations(): void
    {
        if (DeploymentIntegration::query()->exists()) {
            return;
        }

        $catalog = [
            ['provider' => 'github', 'name' => 'GitHub', 'status' => 'connected', 'repositories_count' => 12, 'webhooks_count' => 8],
            ['provider' => 'gitlab', 'name' => 'GitLab', 'status' => 'connected', 'repositories_count' => 4, 'webhooks_count' => 3],
            ['provider' => 'webhook', 'name' => 'Webhooks', 'status' => 'active', 'repositories_count' => 6, 'webhooks_count' => 14],
            ['provider' => 'cron', 'name' => 'Cron deploys', 'status' => 'active', 'repositories_count' => 3, 'webhooks_count' => 0],
        ];

        foreach ($catalog as $row) {
            $integration = DeploymentIntegration::query()->create([
                ...$row,
                'settings' => ['auto_deploy' => true],
                'last_synced_at' => now()->subMinutes(random_int(5, 120)),
            ]);

            if ($row['webhooks_count'] > 0) {
                $project = Project::query()->inRandomOrder()->first();
                foreach (range(1, min(3, $row['webhooks_count'])) as $n) {
                    DeploymentWebhookEvent::query()->create([
                        'deployment_integration_id' => $integration->id,
                        'project_id' => $project?->id,
                        'event_type' => 'push',
                        'status' => 'delivered',
                        'summary' => __('Deploy hook received for :repo', ['repo' => $row['name'].'/'.$n]),
                        'payload' => ['ref' => 'refs/heads/main'],
                        'received_at' => now()->subHours($n * 4),
                    ]);
                }
            }
        }
    }

    private function bootstrapProjects(): void
    {
        (new ServerHealthDemoSeeder)->run();

        $server = Server::query()->first();
        if (! $server) {
            return;
        }

        $catalog = [
            ['Prady Core API', 'api.prady.local', 'active'],
            ['Tenant Portal', 'portal.prady.local', 'active'],
            ['Billing Service', 'billing.prady.local', 'active'],
            ['License Gateway', 'license.prady.local', 'maintenance'],
            ['Analytics Worker', 'analytics.prady.local', 'active'],
        ];

        foreach ($catalog as [$name, $domain, $status]) {
            Project::query()->firstOrCreate(
                ['domain' => $domain],
                [
                    'server_id' => $server->id,
                    'name' => $name,
                    'status' => $status,
                    'version' => 'v2.4.0',
                    'api_token' => Str::random(64),
                ]
            );
        }
    }
}
