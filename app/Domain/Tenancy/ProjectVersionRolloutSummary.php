<?php

namespace App\Domain\Tenancy;

use App\Models\Project;
use App\Models\ProjectVersion;
use App\Models\TenantProjectSubscription;
use Illuminate\Support\Collection;

class ProjectVersionRolloutSummary
{
    /**
     * @return array{total: int, latest: int, outdated: int, critical_update_required: int, unknown: int, project_current_version: ?string, project_latest_version: ?string}
     */
    public function forProject(Project $project): array
    {
        $project->loadMissing(['versions', 'tenantProjectSubscriptions.versionTracking']);

        $projectCurrent = $this->projectCurrentVersion($project);
        $subscriptions = $project->tenantProjectSubscriptions;

        $counts = [
            'total' => $subscriptions->count(),
            'latest' => 0,
            'outdated' => 0,
            'critical_update_required' => 0,
            'unknown' => 0,
            'project_current_version' => $projectCurrent,
            'project_latest_version' => $this->projectLatestVersion($project, $projectCurrent),
        ];

        foreach ($subscriptions as $subscription) {
            $status = $this->resolveSubscriptionStatus($subscription, $projectCurrent);
            $counts[$status]++;
        }

        return $counts;
    }

    public function resolveSubscriptionStatus(TenantProjectSubscription $subscription, ?string $projectCurrent = null): string
    {
        $tracking = $subscription->versionTracking;

        if ($tracking?->update_status === 'critical_update_required') {
            return 'critical_update_required';
        }

        if ($tracking?->update_status && $tracking->update_status !== 'unknown') {
            return $tracking->update_status;
        }

        $current = $tracking?->current_version;
        if (! $current) {
            return 'unknown';
        }

        $projectCurrent ??= $this->projectCurrentVersion($subscription->project);
        if (! $projectCurrent) {
            return 'unknown';
        }

        return version_compare($current, $projectCurrent, '>=') ? 'latest' : 'outdated';
    }

    public function projectCurrentVersion(?Project $project): ?string
    {
        if (! $project) {
            return null;
        }

        $project->loadMissing('versions');

        $fromRegistry = $project->versions->firstWhere('is_current', true)?->version;

        return $fromRegistry ?: ($project->version ?: null);
    }

    public function projectLatestVersion(?Project $project, ?string $fallbackCurrent = null): ?string
    {
        if (! $project) {
            return null;
        }

        $project->loadMissing('versions');

        /** @var Collection<int, ProjectVersion> $versions */
        $versions = $project->versions;

        if ($versions->isEmpty()) {
            return $fallbackCurrent ?? $project->version;
        }

        return $versions->sortByDesc('release_date')->first()?->version
            ?? $fallbackCurrent
            ?? $project->version;
    }

    /**
     * @return array{label: string, tone: string}
     */
    public function versionDisplayLabel(TenantProjectSubscription $subscription): array
    {
        $status = $this->resolveSubscriptionStatus($subscription);
        $current = $subscription->versionTracking?->current_version;

        if (! $current) {
            return ['label' => __('Unknown'), 'tone' => 'neutral'];
        }

        return match ($status) {
            'latest' => ['label' => $current, 'tone' => 'success'],
            'outdated' => ['label' => __('Outdated').' ('.$current.')', 'tone' => 'warning'],
            'critical_update_required' => ['label' => __('Critical').' ('.$current.')', 'tone' => 'danger'],
            default => ['label' => __('Unknown'), 'tone' => 'neutral'],
        };
    }
}
