<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\ActivityLogCategory;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectSubscription;
use App\Support\TenantOpsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantProjectInfrastructureController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function update(Request $request, Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $data = $request->validate([
            'server_id' => ['nullable', 'exists:servers,id'],
            'cpanel_account' => ['nullable', 'string', 'max:255'],
            'whm_account_reference' => ['nullable', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'subdomain' => ['nullable', 'string', 'max:255'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'database_user' => ['nullable', 'string', 'max:255'],
            'disk_quota_mb' => ['nullable', 'integer', 'min:0'],
            'disk_used_mb' => ['nullable', 'integer', 'min:0'],
            'bandwidth_quota_mb' => ['nullable', 'integer', 'min:0'],
            'bandwidth_used_mb' => ['nullable', 'integer', 'min:0'],
            'ssl_status' => ['nullable', Rule::in(array_keys(TenantOpsFormOptions::sslStatus()))],
            'ssl_expiry_date' => ['nullable', 'date'],
            'backup_policy' => ['nullable', Rule::in(array_keys(TenantOpsFormOptions::backupPolicy()))],
            'backup_status' => ['nullable', Rule::in(array_keys(TenantOpsFormOptions::backupStatus()))],
            'last_backup_at' => ['nullable', 'date'],
            'deployment_path' => ['nullable', 'string', 'max:500'],
            'public_url' => ['nullable', 'url', 'max:500'],
            'admin_url' => ['nullable', 'url', 'max:500'],
            'health_check_url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $this->rbacScope()->assertCanAccessServer(
            isset($data['server_id']) ? (int) $data['server_id'] : null
        );

        $existing = TenantProjectInfrastructure::query()
            ->where('tenant_project_subscription_id', $subscription->id)
            ->first();

        $infra = TenantProjectInfrastructure::query()->updateOrCreate(
            ['tenant_project_subscription_id' => $subscription->id],
            $data
        );

        $subscription->load('project');
        $this->activityLogger->log(
            'tenant.infrastructure_updated',
            ActivityLogCategory::TENANT,
            __('Infrastructure updated for :tenant — :project', [
                'tenant' => $tenant->company_name,
                'project' => $subscription->project?->name ?? __('subscription'),
            ]),
            $infra,
            $existing?->only(array_keys($data)),
            $infra->only(array_keys($data)),
        );

        return redirect()
            ->route('tenants.show', [
                'tenant' => $tenant,
                'tab' => 'infrastructure',
                'subscription' => $subscription->id,
            ])
            ->with('status', __('Infrastructure allocation saved.'));
    }
}
