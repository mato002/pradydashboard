<?php

namespace App\Domain\Deployments;

use App\Models\DeploymentWebhookEvent;
use App\Models\HostedProject;
use App\Models\ProjectDeployment;
use Illuminate\Support\Str;

class DeploymentWebhookProcessor
{
    /**
     * @return array{project: HostedProject|null, deployment: ProjectDeployment|null, skipped: bool, reason: ?string}
     */
    public function process(DeploymentWebhookEvent $event): array
    {
        $payload = $event->payload ?? [];
        $project = $this->resolveProject($event, $payload);

        if ($project === null) {
            $event->update(['status' => 'ignored']);

            return [
                'project' => null,
                'deployment' => null,
                'skipped' => true,
                'reason' => __('No hosted project matched repository in webhook payload.'),
            ];
        }

        $version = $this->resolveVersion($payload);
        $environment = $this->resolveEnvironment($payload);
        $branch = $this->resolveBranch($payload);
        $status = $this->shouldAutoDeploy($event) ? 'queued' : 'queued';

        $notes = DeploymentPipelineBuilder::buildNotes([
            'status' => $status,
            'environment' => $environment,
            'branch' => $branch,
            'triggered_by' => $event->integration?->name ?? __('CI Webhook'),
            'version' => $version,
            'commit' => $this->resolveCommit($payload),
        ], $project);

        $deployment = ProjectDeployment::query()->create([
            ProjectDeployment::hostedProjectForeignKey() => $project->id,
            'version' => $version,
            'deployed_at' => now(),
            'notes' => json_encode($notes),
        ]);

        $event->update([
            'status' => 'processed',
            'hosted_project_id' => $project->id,
            'summary' => __('Deploy queued for :project (:version)', [
                'project' => $project->name,
                'version' => $version,
            ]),
        ]);

        return [
            'project' => $project,
            'deployment' => $deployment,
            'skipped' => false,
            'reason' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveProject(DeploymentWebhookEvent $event, array $payload): ?HostedProject
    {
        if ($event->hosted_project_id) {
            return HostedProject::query()->find($event->hosted_project_id);
        }

        $repoCandidates = array_filter([
            data_get($payload, 'repository.full_name'),
            data_get($payload, 'repository.name'),
            data_get($payload, 'project.path_with_namespace'),
            data_get($payload, 'project.name'),
            data_get($payload, 'repo'),
        ]);

        foreach ($repoCandidates as $repo) {
            $match = HostedProject::query()
                ->where('git_repository', $repo)
                ->orWhere('git_repository', 'like', '%'.$repo.'%')
                ->orWhere('name', $repo)
                ->first();

            if ($match !== null) {
                return $match;
            }
        }

        $settingsRepos = $event->integration?->settings['repositories'] ?? [];
        if (is_array($settingsRepos)) {
            foreach ($settingsRepos as $entry) {
                $projectId = is_array($entry) ? ($entry['hosted_project_id'] ?? null) : null;
                if ($projectId) {
                    return HostedProject::query()->find($projectId);
                }
            }
        }

        return HostedProject::query()->orderBy('id')->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveVersion(array $payload): string
    {
        $sha = $this->resolveCommit($payload);

        if ($sha !== null) {
            return 'v'.substr($sha, 0, 7);
        }

        return 'v'.now()->format('Ymd.His');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveCommit(array $payload): ?string
    {
        return data_get($payload, 'head_commit.id')
            ?? data_get($payload, 'checkout_sha')
            ?? data_get($payload, 'after')
            ?? data_get($payload, 'commit.sha');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveBranch(array $payload): string
    {
        $ref = (string) (data_get($payload, 'ref') ?? data_get($payload, 'branch') ?? 'refs/heads/main');

        if (str_starts_with($ref, 'refs/heads/')) {
            return Str::after($ref, 'refs/heads/');
        }

        return $ref !== '' ? $ref : 'main';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveEnvironment(array $payload): string
    {
        $branch = $this->resolveBranch($payload);

        return match (true) {
            in_array($branch, ['main', 'master', 'production'], true) => 'production',
            str_starts_with($branch, 'release/') => 'staging',
            str_starts_with($branch, 'develop') => 'development',
            default => (string) (data_get($payload, 'environment') ?? 'staging'),
        };
    }

    private function shouldAutoDeploy(DeploymentWebhookEvent $event): bool
    {
        return (bool) ($event->integration?->settings['auto_deploy'] ?? true);
    }
}
