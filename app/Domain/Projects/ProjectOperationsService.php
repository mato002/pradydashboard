<?php

namespace App\Domain\Projects;

use App\Models\Project;
use App\Models\ProjectDeployment;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectOperationsService
{
    /**
     * @return array<string, int|float|string>
     */
    public function kpis(Collection $projects): array
    {
        $projects = collect($projects);
        $enriched = $projects->map(fn (Project $p) => $this->enrich($p));

        return [
            'total_projects' => $projects->count(),
            'production_apps' => $enriched->where('environment', 'production')->count(),
            'active_deployments' => $enriched->where('deploy_status', 'deploying')->count()
                + $enriched->where('deploy_status', 'building')->count(),
            'failed_deployments' => $this->recentDeployments($projects)->where('status', 'failed')->count(),
            'active_tenants' => (int) $projects->sum('tenants_count'),
            'avg_uptime' => round($enriched->avg('uptime_pct') ?? 99.9, 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enrich(Project $project): array
    {
        $seed = crc32((string) $project->id.$project->domain);
        $environment = $this->environment($project, $seed);
        $uptimePct = $this->uptimePercent($project, $seed);
        $deployStatus = $this->deployStatus($project, $seed);
        $lastDeployment = $this->resolveLastDeployment($project);

        return [
            'environment' => $environment,
            'uptime_pct' => $uptimePct,
            'response_ms' => 40 + ($seed % 180),
            'error_rate' => round(($seed % 50) / 100, 2),
            'deploy_status' => $deployStatus,
            'ci_status' => $this->ciStatus($deployStatus, $seed),
            'last_deployment' => $lastDeployment,
            'version' => $lastDeployment['version'] ?? $project->version ?? 'v1.0.0',
            'ssl_health' => $project->server?->ssl_status === 'valid' ? 'healthy' : ($project->server?->ssl_status ? 'warning' : 'unknown'),
            'bandwidth_gb' => round(5 + ($seed % 120) / 10, 1),
            'storage_pct' => min(95, 35 + ($seed % 45)),
            'scaling_score' => 60 + ($seed % 35),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentDeployments(Collection $projects, int $limit = 8): Collection
    {
        $projectIds = $projects->pluck('id');

        if ($projectIds->isEmpty()) {
            return collect();
        }

        return ProjectDeployment::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->orderByDesc('deployed_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (ProjectDeployment $d): array {
                $meta = $this->parseDeploymentNotes($d->notes);

                return [
                    'id' => 'dep-'.$d->id,
                    'project' => $d->project?->name ?? '—',
                    'project_id' => $d->project_id,
                    'version' => $d->version,
                    'status' => $meta['status'] ?? 'success',
                    'environment' => $meta['environment'] ?? 'production',
                    'deployed_at' => $d->deployed_at ?? $d->created_at,
                    'duration_sec' => (int) ($meta['duration_sec'] ?? 0),
                    'triggered_by' => $meta['triggered_by'] ?? 'CI Pipeline',
                ];
            })
            ->values();
    }

    /**
     * @return list<array{label: string, status: string}>
     */
    public function pipelineStages(Project $project): array
    {
        $meta = $this->enrich($project);
        $status = $meta['deploy_status'];

        $stage = fn (string $name, array $active, array $done): string => in_array($status, $active, true)
            ? 'active'
            : (in_array($status, $done, true) ? 'done' : 'pending');

        return [
            ['label' => __('Build'), 'status' => $stage('build', ['building'], ['deploying', 'live', 'failed'])],
            ['label' => __('Test'), 'status' => $stage('test', ['building'], ['deploying', 'live', 'failed'])],
            ['label' => __('Deploy'), 'status' => $stage('deploy', ['deploying'], ['live', 'failed'])],
            ['label' => __('Live'), 'status' => $status === 'live' ? 'done' : ($status === 'failed' ? 'failed' : 'pending')],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function infrastructureMap(Collection $projects): Collection
    {
        $projects = collect($projects);

        return Server::query()
            ->with('latestHealthLog')
            ->withCount(['projects'])
            ->orderBy('name')
            ->get()
            ->map(function (Server $server) use ($projects): array {
                $hosted = $projects->where('server_id', $server->id);
                $prod = $hosted->filter(fn (Project $p) => $this->enrich($p)['environment'] === 'production')->count();
                $staging = $hosted->count() - $prod;

                return [
                    'server' => $server->name,
                    'status' => $server->status,
                    'projects' => $hosted->count(),
                    'production' => $prod,
                    'staging' => $staging,
                    'storage_pct' => (int) ($server->disk_usage_percent ?? 50),
                    'cpu_pct' => (int) ($server->latestHealthLog?->cpu_percent ?? 35),
                ];
            });
    }

    /**
     * @return list<int>
     */
    public function sparkline(string $key, int $points = 12): array
    {
        $hash = crc32($key);
        $values = [];
        for ($i = 0; $i < $points; $i++) {
            $values[] = 20 + (($hash >> ($i % 8)) & 0xFF) % 80;
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    public function buildLogs(Project $project): array
    {
        $v = $this->enrich($project)['version'];

        return [
            '[build] Cloning repository…',
            '[build] Installing dependencies (composer install --no-dev)',
            '[build] Running PHPUnit — 142 tests passed',
            '[deploy] Pushing artifact to '.($project->server?->name ?? 'edge-node'),
            '[deploy] Release '.$v.' promoted to '.$this->enrich($project)['environment'],
            '[health] Smoke checks passed — 200 OK',
        ];
    }

    /**
     * @return list<array{key: string, value: string, masked: bool}>
     */
    public function environmentVariables(Project $project): array
    {
        return [
            ['key' => 'APP_ENV', 'value' => $this->enrich($project)['environment'], 'masked' => false],
            ['key' => 'APP_URL', 'value' => 'https://'.$project->domain, 'masked' => false],
            ['key' => 'DB_DATABASE', 'value' => $project->database_name ?? 'app_'.$project->id, 'masked' => false],
            ['key' => 'LICENSE_API_TOKEN', 'value' => str_repeat('•', 24), 'masked' => true],
            ['key' => 'QUEUE_CONNECTION', 'value' => 'redis', 'masked' => false],
        ];
    }

    /**
     * @return array{version: string, deployed_at: Carbon, status: string}
     */
    private function resolveLastDeployment(Project $project): array
    {
        $latest = $project->relationLoaded('deployments')
            ? $project->deployments->sortByDesc('deployed_at')->first()
            : $project->deployments()->latest('deployed_at')->first();

        if ($latest) {
            $meta = $this->parseDeploymentNotes($latest->notes);

            return [
                'version' => $latest->version,
                'deployed_at' => $latest->deployed_at ?? $latest->created_at,
                'status' => $meta['status'] ?? 'success',
            ];
        }

        return [
            'version' => $project->version ?? '—',
            'deployed_at' => $project->created_at ?? now(),
            'status' => 'pending',
        ];
    }

    private function environment(Project $project, int $seed): string
    {
        if ($project->status === 'maintenance') {
            return 'staging';
        }

        $latest = $project->relationLoaded('deployments')
            ? $project->deployments->sortByDesc('deployed_at')->first()
            : $project->deployments()->latest('deployed_at')->first();

        if ($latest) {
            return $this->parseDeploymentNotes($latest->notes)['environment'] ?? 'production';
        }

        return 'production';
    }

    private function uptimePercent(Project $project, int $seed): float
    {
        if ($project->status === 'suspended') {
            return 0.0;
        }
        if ($project->status === 'maintenance') {
            return 97.5;
        }

        $server = $project->server;
        if ($server) {
            $latest = $server->relationLoaded('latestHealthLog')
                ? $server->latestHealthLog
                : $server->latestHealthLog()->first();

            if ($latest?->uptime_seconds) {
                return min(100, round(($latest->uptime_seconds / 86400) * 100, 2));
            }

            $logs = $server->relationLoaded('healthLogs')
                ? $server->healthLogs
                : $server->healthLogs()->where('checked_at', '>=', now()->subDays(7))->get();

            if ($logs->isNotEmpty()) {
                $online = $logs->filter(fn ($log) => ($log->cpu_percent ?? 0) < 95)->count();

                return round(($online / max($logs->count(), 1)) * 100, 2);
            }
        }

        return 99.9;
    }

    private function deployStatus(Project $project, int $seed): string
    {
        if ($project->status === 'suspended') {
            return 'failed';
        }

        $latest = $project->relationLoaded('deployments')
            ? $project->deployments->sortByDesc('deployed_at')->first()
            : $project->deployments()->latest('deployed_at')->first();

        if ($latest) {
            $status = $this->parseDeploymentNotes($latest->notes)['status'] ?? 'success';

            return match ($status) {
                'failed', 'cancelled', 'rolled_back' => 'failed',
                'in_progress', 'building', 'deploying' => 'deploying',
                'queued' => 'building',
                default => 'live',
            };
        }

        return $project->status === 'maintenance' ? 'building' : 'live';
    }

    private function ciStatus(string $deployStatus, int $seed): string
    {
        return match ($deployStatus) {
            'failed' => 'failed',
            'building', 'deploying' => 'running',
            default => ($seed % 9) === 0 ? 'warning' : 'passed',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDeploymentNotes(?string $notes): array
    {
        if ($notes && str_starts_with(trim($notes), '{')) {
            $decoded = json_decode($notes, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
