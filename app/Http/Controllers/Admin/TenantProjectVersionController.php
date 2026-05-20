<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Tenancy\ProjectVersionRolloutSummary;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Support\TenantOpsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantProjectVersionController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ProjectVersionRolloutSummary $rolloutSummary,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function update(Request $request, Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $subscription->load('project.versions');

        $data = $request->validate([
            'current_version' => ['nullable', 'string', 'max:50'],
            'latest_version' => ['nullable', 'string', 'max:50'],
            'update_status' => ['required', Rule::in(array_keys(TenantOpsFormOptions::updateStatus()))],
            'commit_hash' => ['nullable', 'string', 'max:80'],
            'build_number' => ['nullable', 'string', 'max:50'],
            'last_checked_at' => ['nullable', 'date'],
            'last_updated_at' => ['nullable', 'date'],
            'update_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $projectCurrent = $this->rolloutSummary->projectCurrentVersion($subscription->project);
        $projectLatest = $this->rolloutSummary->projectLatestVersion($subscription->project, $projectCurrent);

        if (empty($data['latest_version']) && $projectLatest) {
            $data['latest_version'] = $projectLatest;
        }

        if ($data['update_status'] !== 'critical_update_required') {
            $data['update_status'] = $this->inferUpdateStatus(
                $data['current_version'] ?? null,
                $data['latest_version'] ?? $projectLatest,
                $data['update_status']
            );
        }

        $existing = TenantProjectVersion::query()
            ->where('tenant_project_subscription_id', $subscription->id)
            ->first();

        $tracking = TenantProjectVersion::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $subscription->id],
            $data
        );

        $this->activityLogger->log(
            'tenant.version_tracking_updated',
            ActivityLogCategory::TENANT,
            __('Version tracking updated for :tenant — :project', [
                'tenant' => $tenant->company_name,
                'project' => $subscription->project?->name ?? __('subscription'),
            ]),
            $tracking,
            $existing?->only(array_keys($data)),
            $tracking->only(array_keys($data)),
        );

        return redirect()
            ->route('tenants.show', [
                'tenant' => $tenant,
                'tab' => 'versions',
                'subscription' => $subscription->id,
            ])
            ->with('status', __('Version tracking saved.'));
    }

    private function inferUpdateStatus(?string $current, ?string $latest, string $requested): string
    {
        if (! $current) {
            return 'unknown';
        }

        if (! $latest) {
            return $requested === 'latest' ? 'latest' : 'unknown';
        }

        return version_compare($current, $latest, '>=') ? 'latest' : 'outdated';
    }
}
