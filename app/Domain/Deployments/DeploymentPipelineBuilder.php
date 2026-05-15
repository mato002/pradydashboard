<?php

namespace App\Domain\Deployments;

use App\Models\Project;
use Carbon\Carbon;

class DeploymentPipelineBuilder
{
    /**
     * @return list<array{key: string, label: string}>
     */
    public static function stageDefinitions(): array
    {
        return [
            ['key' => 'build', 'label' => __('Build')],
            ['key' => 'test', 'label' => __('Test')],
            ['key' => 'scan', 'label' => __('Security Scan')],
            ['key' => 'staging', 'label' => __('Staging')],
            ['key' => 'approval', 'label' => __('Approval')],
            ['key' => 'production', 'label' => __('Production')],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function buildNotes(array $attributes, ?Project $project = null): array
    {
        $status = (string) ($attributes['status'] ?? 'queued');
        $triggeredBy = (string) ($attributes['triggered_by'] ?? 'CI Pipeline');
        $environment = (string) ($attributes['environment'] ?? 'production');
        $version = (string) ($attributes['version'] ?? 'v0.0.0');

        $pipelineStages = $attributes['pipeline_stages']
            ?? self::stagesForStatus($status, $triggeredBy, $attributes['deployed_at'] ?? null);

        $buildLogs = $attributes['build_logs']
            ?? self::buildLogs($project, $version, $environment, $status);

        return array_merge($attributes, [
            'status' => $status,
            'environment' => $environment,
            'branch' => $attributes['branch'] ?? 'main',
            'triggered_by' => $triggeredBy,
            'duration_sec' => (int) ($attributes['duration_sec'] ?? self::totalDuration($pipelineStages)),
            'commit' => $attributes['commit'] ?? substr(md5($version.$status), 0, 7),
            'strategy' => $attributes['strategy'] ?? 'rolling',
            'pipeline_stages' => $pipelineStages,
            'build_logs' => $buildLogs,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function stagesForStatus(string $status, string $triggeredBy = 'CI Pipeline', mixed $deployedAt = null): array
    {
        $preset = match ($status) {
            'failed' => ['done', 'done', 'failed', 'pending', 'pending', 'pending'],
            'cancelled' => ['done', 'done', 'done', 'cancelled', 'pending', 'pending'],
            'rolled_back' => ['done', 'done', 'done', 'done', 'done', 'failed'],
            'queued' => ['done', 'done', 'done', 'done', 'active', 'pending'],
            'in_progress', 'building', 'deploying' => ['done', 'done', 'done', 'active', 'pending', 'pending'],
            'success' => ['done', 'done', 'done', 'done', 'done', 'done'],
            default => ['pending', 'pending', 'pending', 'pending', 'pending', 'pending'],
        };

        $anchor = $deployedAt instanceof Carbon
            ? $deployedAt
            : ($deployedAt ? Carbon::parse($deployedAt) : now());

        return collect(self::stageDefinitions())->map(function (array $stage, int $i) use ($preset, $triggeredBy, $anchor) {
            $stageStatus = $preset[$i] ?? 'pending';
            if ($stageStatus === 'cancelled') {
                $stageStatus = 'failed';
            }

            $duration = $stageStatus === 'pending' ? 0 : (20 + ($i * 15));
            $completedAt = match ($stageStatus) {
                'done', 'failed' => $anchor->copy()->subMinutes(max(1, 40 - $i * 6)),
                'active' => $anchor,
                default => null,
            };

            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'status' => $stageStatus,
                'duration_sec' => $duration,
                'owner' => $triggeredBy,
                'at' => $completedAt?->diffForHumans() ?? __('Pending'),
            ];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function forApproval(array $meta): array
    {
        $meta['status'] = 'in_progress';
        $meta['pipeline_stages'] = self::stagesForStatus('in_progress', $meta['triggered_by'] ?? 'CI Pipeline', now());

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function forCancellation(array $meta): array
    {
        $meta['status'] = 'cancelled';
        $meta['pipeline_stages'] = self::stagesForStatus('cancelled', $meta['triggered_by'] ?? 'CI Pipeline', now());
        $meta['build_logs'] = array_merge($meta['build_logs'] ?? [], ['[deploy] Cancelled by operator']);

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function forSuccess(array $meta, ?Project $project = null): array
    {
        $meta['status'] = 'success';
        $meta['pipeline_stages'] = self::stagesForStatus('success', $meta['triggered_by'] ?? 'CI Pipeline', now());
        $meta['duration_sec'] = self::totalDuration($meta['pipeline_stages']);
        $meta['build_logs'] = self::buildLogs(
            $project,
            (string) ($meta['version'] ?? 'v0.0.0'),
            (string) ($meta['environment'] ?? 'production'),
            'success'
        );

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function forRollback(array $meta, ?Project $project = null): array
    {
        $meta['status'] = 'rolled_back';
        $meta['pipeline_stages'] = self::stagesForStatus('rolled_back', $meta['triggered_by'] ?? 'Rollback', now());
        $meta['build_logs'] = self::buildLogs(
            $project,
            (string) ($meta['version'] ?? 'v0.0.0'),
            (string) ($meta['environment'] ?? 'production'),
            'rolled_back'
        );

        return $meta;
    }

    /**
     * @return list<string>
     */
    public static function buildLogs(?Project $project, string $version, string $environment, string $status): array
    {
        $host = $project?->server?->name ?? 'edge-node';
        $name = $project?->name ?? 'application';

        $lines = [
            '[pipeline] Triggered deploy for '.$name,
            '[build] docker build -t '.$version,
            '[test] Running automated test suite',
        ];

        return match ($status) {
            'failed' => [...$lines, '[deploy] ERROR — health check failed on '.$host],
            'cancelled' => [...$lines, '[deploy] Cancelled by operator'],
            'rolled_back' => [...$lines, '[deploy] Rolled back to previous stable artifact on '.$host],
            default => [
                ...$lines,
                '[scan] No critical CVEs detected',
                '[deploy] Promoted '.$version.' to '.$environment.' on '.$host,
                '[health] Smoke checks passed — 200 OK',
            ],
        };
    }

    /**
     * @param  list<array<string, mixed>>  $stages
     */
    public static function totalDuration(array $stages): int
    {
        return (int) collect($stages)->sum('duration_sec');
    }
}
