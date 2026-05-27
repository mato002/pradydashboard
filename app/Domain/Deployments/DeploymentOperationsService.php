<?php

namespace App\Domain\Deployments;

use App\Models\DeploymentIntegration;
use App\Models\DeploymentOpsEvent;
use App\Models\Project;
use App\Models\ProjectDeployment;
use App\Models\ServerHealthLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DeploymentOperationsService
{
    /**
     * @return list<int|float>
     */
    public function sparkline(string $key, int $points = 12): array
    {
        $statusFilter = match ($key) {
            'dep-ok' => ['success'],
            'dep-fail' => ['failed'],
            'dep-rb' => ['rolled_back'],
            'dep-pipe' => ['in_progress', 'building', 'deploying', 'queued'],
            'dep-time' => null,
            default => null,
        };

        $series = [];
        for ($i = $points - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $query = ProjectDeployment::query()->whereDate('deployed_at', $day);

            if ($statusFilter !== null) {
                $query->where(function ($q) use ($statusFilter) {
                    foreach ($statusFilter as $status) {
                        $q->orWhere('notes', 'like', '%"status":"'.$status.'"%');
                    }
                });
            }

            if ($key === 'dep-time') {
                $avg = $this->averageDurationForDay($day);
                $series[] = $avg > 0 ? min(100, (int) round($avg / 3)) : 0;
            } else {
                $series[] = $query->count();
            }
        }

        return $series;
    }

    /**
     * @return array<string, int|float|string>
     */
    public function fleetKpis(Collection $projects): array
    {
        $history = $this->deploymentHistory($projects);
        $successful = $history->where('status', 'success')->count();
        $failed = $history->where('status', 'failed')->count();
        $rollbacks = $history->where('status', 'rolled_back')->count();
        $inFlight = $history->whereIn('status', ['in_progress', 'building', 'deploying', 'queued'])->count();
        $avgDuration = (int) round($history->avg('duration_sec') ?? 0);

        $totalTrend = $this->weekOverWeekTrend();

        return [
            'total' => $history->count(),
            'successful' => $successful,
            'failed' => $failed,
            'rollbacks' => $rollbacks,
            'active_pipelines' => $inFlight,
            'avg_duration' => $avgDuration > 0 ? $this->formatDuration($avgDuration) : '—',
            'avg_duration_sec' => $avgDuration,
            'success_rate' => $history->count() > 0 ? round(($successful / $history->count()) * 100, 1) : 100,
            'total_trend' => $totalTrend,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pipelineFlow(?array $selected = null): array
    {
        if ($selected && ! empty($selected['pipeline_stages'])) {
            return $selected['pipeline_stages'];
        }

        if ($selected) {
            return DeploymentPipelineBuilder::stagesForStatus(
                $selected['status'] ?? 'queued',
                $selected['triggered_by'] ?? 'CI Pipeline',
                $selected['deployed_at'] ?? null
            );
        }

        return DeploymentPipelineBuilder::stagesForStatus('queued');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function deploymentHistory(Collection $projects, int $limit = 50): Collection
    {
        $projectIds = $projects->pluck('id');

        return ProjectDeployment::query()
            ->with('project.server')
            ->when($projectIds->isNotEmpty(), fn ($q) => $q->whereIn('project_id', $projectIds))
            ->orderByDesc('deployed_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ProjectDeployment $d) => $this->mapDeploymentRow($d))
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function environments(Collection $projects): array
    {
        $envs = ['production', 'staging', 'development', 'qa', 'sandbox'];
        $labels = [
            'production' => __('Production'),
            'staging' => __('Staging'),
            'development' => __('Development'),
            'qa' => __('QA'),
            'sandbox' => __('Sandbox'),
        ];

        return collect($envs)->map(function (string $env) use ($projects, $labels) {
            $hosted = $projects->filter(function (Project $p) use ($env) {
                $latestEnv = $this->latestEnvironmentForProject($p);

                return $latestEnv === $env
                    || ($env === 'development' && $p->status === 'maintenance');
            });

            $latest = $this->deploymentHistory($hosted, 1)->first();
            $uptime = $this->environmentUptime($hosted);

            return [
                'key' => $env,
                'label' => $labels[$env],
                'version' => $latest['version'] ?? '—',
                'uptime' => $uptime,
                'last_deploy' => $latest['deployed_at_human'] ?? __('Never'),
                'health' => $hosted->isEmpty() ? 'idle' : ($latest && $latest['status'] === 'failed' ? 'critical' : 'healthy'),
                'rollback_available' => $latest && in_array($latest['status'], ['success', 'failed', 'rolled_back'], true),
                'projects' => $hosted->count(),
            ];
        })->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rollbackCandidates(Collection $projects): Collection
    {
        return $this->deploymentHistory($projects, 20)
            ->where('status', 'success')
            ->take(6)
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function alerts(Collection $projects): array
    {
        $alerts = collect();
        $history = $this->deploymentHistory($projects, 30);

        foreach ($history->where('status', 'failed')->take(3) as $dep) {
            $alerts->push([
                'severity' => 'CRITICAL',
                'title' => __('Deployment failed'),
                'body' => __(':project · :version on :env', ['project' => $dep['project'], 'version' => $dep['version'], 'env' => $dep['environment']]),
                'time' => $dep['deployed_at_human'],
            ]);
        }

        foreach ($history->where('status', 'rolled_back')->take(2) as $dep) {
            $alerts->push([
                'severity' => 'WARNING',
                'title' => __('Rollback executed'),
                'body' => __(':project reverted to prior stable artifact', ['project' => $dep['project']]),
                'time' => $dep['deployed_at_human'],
            ]);
        }

        foreach ($history->where('status', 'queued')->take(2) as $dep) {
            $alerts->push([
                'severity' => 'INFO',
                'title' => __('Approval required'),
                'body' => __('Production release :version awaiting sign-off', ['version' => $dep['version']]),
                'time' => __('Pending'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'severity' => 'INFO',
                'title' => __('Release train nominal'),
                'body' => __('No blocking deployment incidents in the current window.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(8)->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function integrations(): array
    {
        return DeploymentIntegration::query()
            ->withCount([
                'webhookEvents',
                'webhookEvents as recent_webhooks_count' => fn ($q) => $q->where('received_at', '>=', now()->subDays(7)),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (DeploymentIntegration $integration) => [
                'id' => $integration->id,
                'name' => $integration->name,
                'provider' => $integration->provider,
                'status' => $integration->status,
                'repos' => $integration->repositories_count,
                'webhooks' => $integration->webhooks_count,
                'recent_events' => $integration->recent_webhooks_count,
                'last_synced' => $integration->last_synced_at?->diffForHumans() ?? __('Never'),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(Collection $projects): array
    {
        $since = now()->subDays(30);
        $projectIds = $projects->pluck('id');
        $serverIds = $projects->pluck('server_id')->filter()->unique();

        $freq = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $count = ProjectDeployment::query()
                ->when($projectIds->isNotEmpty(), fn ($q) => $q->whereIn('project_id', $projectIds))
                ->whereDate('deployed_at', $day)
                ->count();
            $freq[] = ['label' => $day->format('D'), 'count' => $count];
        }

        $kpis = $this->fleetKpis($projects);

        $opsQuery = DeploymentOpsEvent::query()->where('occurred_at', '>=', $since);
        if ($projectIds->isNotEmpty()) {
            $opsQuery->where(function ($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds)->orWhereNull('project_id');
            });
        }

        return [
            'frequency' => $freq,
            'success_rate' => $kpis['success_rate'],
            'container_deploys' => (clone $opsQuery)->where('type', DeploymentOpsEvent::TYPE_CONTAINER)->count(),
            'infra_changes' => (clone $opsQuery)->where('type', DeploymentOpsEvent::TYPE_INFRA)->count(),
            'scaling_ops' => (clone $opsQuery)->where('type', DeploymentOpsEvent::TYPE_SCALING)->count(),
            'cpu_impact' => $this->cpuImpactSeries($serverIds),
        ];
    }

    /**
     * @param  array<string, mixed>  $deployment
     * @return list<string>
     */
    public function buildLogs(array $deployment): array
    {
        if (! empty($deployment['build_logs']) && is_array($deployment['build_logs'])) {
            return $deployment['build_logs'];
        }

        $project = Project::query()->with('server')->find($deployment['project_id'] ?? 0);

        return DeploymentPipelineBuilder::buildLogs(
            $project,
            (string) ($deployment['version'] ?? 'v0.0.0'),
            (string) ($deployment['environment'] ?? 'production'),
            (string) ($deployment['status'] ?? 'success')
        );
    }

    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        return intdiv($seconds, 60).'m '.($seconds % 60).'s';
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDeploymentRow(ProjectDeployment $d): array
    {
        $meta = $this->parseNotes($d->notes);
        $status = (string) ($meta['status'] ?? 'success');
        $deployedAt = $d->deployed_at ?? $d->created_at;
        $pipelineStages = $meta['pipeline_stages'] ?? DeploymentPipelineBuilder::stagesForStatus($status, $meta['triggered_by'] ?? 'CI Pipeline', $deployedAt);

        return [
            'id' => 'dep-'.$d->id,
            'record_id' => $d->id,
            'deployment_id' => 'DEP-'.str_pad((string) $d->id, 5, '0', STR_PAD_LEFT),
            'project' => $d->project?->name ?? '—',
            'project_id' => $d->project_id,
            'environment' => $meta['environment'] ?? 'production',
            'version' => $d->version,
            'branch' => $meta['branch'] ?? 'main',
            'status' => $status,
            'triggered_by' => $meta['triggered_by'] ?? 'CI Pipeline',
            'duration_sec' => (int) ($meta['duration_sec'] ?? DeploymentPipelineBuilder::totalDuration($pipelineStages)),
            'duration' => $this->formatDuration((int) ($meta['duration_sec'] ?? DeploymentPipelineBuilder::totalDuration($pipelineStages))),
            'deployed_at' => $deployedAt,
            'deployed_at_human' => $deployedAt?->diffForHumans() ?? __('Queued'),
            'commit' => $meta['commit'] ?? '—',
            'strategy' => $meta['strategy'] ?? 'rolling',
            'risk_score' => $this->riskScore($status, $meta),
            'pipeline_stages' => $pipelineStages,
            'build_logs' => $meta['build_logs'] ?? DeploymentPipelineBuilder::buildLogs(
                $d->project,
                $d->version,
                $meta['environment'] ?? 'production',
                $status
            ),
            'notes' => is_string($d->notes) && ! str_starts_with(trim($d->notes), '{') ? $d->notes : null,
            'rollback_available' => $status === 'success',
            'project_url' => $d->project ? route('hosted-projects.show', $d->project) : '#',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function parseNotes(?string $notes): array
    {
        if ($notes && str_starts_with(trim($notes), '{')) {
            $decoded = json_decode($notes, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function riskScore(string $status, array $meta): int
    {
        $base = match ($status) {
            'failed' => 85,
            'rolled_back' => 70,
            'in_progress', 'deploying', 'building' => 45,
            'queued' => 30,
            default => 12,
        };

        if (($meta['environment'] ?? '') === 'production') {
            $base += 10;
        }

        return min(99, $base);
    }

    private function weekOverWeekTrend(): string
    {
        $thisWeek = ProjectDeployment::query()->where('deployed_at', '>=', now()->startOfWeek())->count();
        $lastWeek = ProjectDeployment::query()
            ->whereBetween('deployed_at', [now()->subWeek()->startOfWeek(), now()->startOfWeek()])
            ->count();

        if ($lastWeek === 0) {
            return $thisWeek > 0 ? '+100%' : '0%';
        }

        $pct = round((($thisWeek - $lastWeek) / $lastWeek) * 100);

        return ($pct >= 0 ? '+' : '').$pct.'%';
    }

    private function averageDurationForDay(Carbon $day): float
    {
        $deployments = ProjectDeployment::query()
            ->whereDate('deployed_at', $day)
            ->get();

        if ($deployments->isEmpty()) {
            return 0;
        }

        $total = $deployments->sum(function (ProjectDeployment $d) {
            $meta = $this->parseNotes($d->notes);

            return (int) ($meta['duration_sec'] ?? 0);
        });

        return $total / $deployments->count();
    }

    private function latestEnvironmentForProject(Project $project): ?string
    {
        $latest = $project->relationLoaded('deployments')
            ? $project->deployments->sortByDesc('deployed_at')->first()
            : $project->deployments()->latest('deployed_at')->first();

        if (! $latest) {
            return $project->status === 'maintenance' ? 'staging' : 'production';
        }

        $meta = $this->parseNotes($latest->notes);

        return $meta['environment'] ?? 'production';
    }

    private function environmentUptime(Collection $projects): float
    {
        if ($projects->isEmpty()) {
            return 0;
        }

        $values = $projects->map(function (Project $project) {
            $server = $project->server;
            if (! $server) {
                return null;
            }

            $logs = $server->relationLoaded('healthLogs')
                ? $server->healthLogs
                : $server->healthLogs()->where('checked_at', '>=', now()->subDays(7))->get();

            if ($logs->isEmpty()) {
                $latest = $server->relationLoaded('latestHealthLog')
                    ? $server->latestHealthLog
                    : $server->latestHealthLog()->first();

                if ($latest && $latest->uptime_seconds) {
                    return min(100, round(($latest->uptime_seconds / 86400) * 100, 2));
                }

                return $project->status === 'active' && $server->last_synced_at ? 100.0 : null;
            }

            $online = $logs->filter(fn ($log) => ($log->cpu_percent ?? 0) < 95)->count();

            return round(($online / max($logs->count(), 1)) * 100, 2);
        })->filter(fn ($v) => $v !== null);

        return $values->isEmpty() ? 0 : round($values->avg(), 2);
    }

    /**
     * @param  Collection<int, int|string|null>  $serverIds
     * @return list<float>
     */
    private function cpuImpactSeries(Collection $serverIds): array
    {
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $query = ServerHealthLog::query()->whereDate('checked_at', $day);
            if ($serverIds->isNotEmpty()) {
                $query->whereIn('server_id', $serverIds);
            }
            $avg = (float) $query->avg('cpu_percent');
            $series[] = round($avg > 0 ? $avg : 0, 1);
        }

        return $series;
    }
}
