<?php

namespace App\Domain\Tenancy;

use App\Models\Product;
use App\Models\ProjectVersion;
use App\Models\TenantProjectSubscription;
use Illuminate\Support\Collection;

class ProjectVersionRolloutSummary
{
    /**
     * @return array{total: int, latest: int, outdated: int, critical_update_required: int, unknown: int, project_current_version: ?string, project_latest_version: ?string}
     */
    public function forProduct(Product $product): array
    {
        $product->loadMissing(['versions', 'tenantProjectSubscriptions.versionTracking']);

        $productCurrent = $this->projectCurrentVersion($product);
        $subscriptions = $product->tenantProjectSubscriptions;

        $counts = [
            'total' => $subscriptions->count(),
            'latest' => 0,
            'outdated' => 0,
            'critical_update_required' => 0,
            'unknown' => 0,
            'project_current_version' => $productCurrent,
            'project_latest_version' => $this->projectLatestVersion($product, $productCurrent),
        ];

        foreach ($subscriptions as $subscription) {
            $status = $this->resolveSubscriptionStatus($subscription, $productCurrent);
            $counts[$status]++;
        }

        return $counts;
    }

    /** @deprecated Use forProduct() */
    public function forProject(Product $product): array
    {
        return $this->forProduct($product);
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

        $projectCurrent ??= $this->projectCurrentVersion($subscription->product);
        if (! $projectCurrent) {
            return 'unknown';
        }

        return version_compare($current, $projectCurrent, '>=') ? 'latest' : 'outdated';
    }

    public function projectCurrentVersion(?Product $product): ?string
    {
        if (! $product) {
            return null;
        }

        $product->loadMissing('versions');

        return $product->versions->firstWhere('is_current', true)?->version;
    }

    public function projectLatestVersion(?Product $product, ?string $fallbackCurrent = null): ?string
    {
        if (! $product) {
            return null;
        }

        $product->loadMissing('versions');

        /** @var Collection<int, ProjectVersion> $versions */
        $versions = $product->versions;

        if ($versions->isEmpty()) {
            return $fallbackCurrent;
        }

        return $versions->sortByDesc('release_date')->first()?->version
            ?? $fallbackCurrent;
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
