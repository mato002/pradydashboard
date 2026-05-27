<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\Services\TenantActivityLogger;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Tenant;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Support\Rbac\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TenantQuickActionsController extends Controller
{
    public function __construct(
        private readonly TenantProjectProvisioner $projectProvisioner,
        private readonly TenantActivityLogger $activityLogger,
    ) {}

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->authorize('suspend', $tenant);

        $tenant->update(['status' => 'suspended']);
        $this->syncSubscriptionWhenLinked($tenant);

        $this->activityLogger->log(
            $tenant,
            'tenant.suspended',
            __('Tenant suspended from control center drawer.'),
        );

        return $this->back($tenant, __(':tenant has been suspended.', ['tenant' => $tenant->company_name]));
    }

    public function openApp(Tenant $tenant): RedirectResponse
    {
        $this->authorize('view', $tenant);

        $url = $this->resolveTenantAppUrl($tenant);
        if ($url === null) {
            return $this->back(
                $tenant,
                __('No login URL or tenant domain is configured for :tenant.', ['tenant' => $tenant->company_name]),
            );
        }

        $this->activityLogger->log(
            $tenant,
            'tenant.app_opened',
            __('Opened tenant application from control center.'),
            ['url' => $url],
        );

        return redirect()->away($url);
    }

    public function forceBackup(Tenant $tenant): RedirectResponse
    {
        $this->authorize('view', $tenant);
        abort_unless(Rbac::can('backups.create'), 403);

        Backup::query()->create(Backup::attributesWithHostedProject($tenant->hosted_project_id, [
            'name' => __('On-demand: :tenant', ['tenant' => $tenant->company_name]),
            'server_id' => $tenant->server_id,
            'tenant_id' => $tenant->id,
            'backup_type' => 'full',
            'status' => 'queued',
            'started_at' => now(),
            'notes' => __('Queued from tenant control center.'),
        ]));

        $this->activityLogger->log(
            $tenant,
            'tenant.backup_queued',
            __('On-demand backup queued from control center drawer.'),
        );

        return $this->back($tenant, __('Backup job queued for :tenant.', ['tenant' => $tenant->company_name]));
    }

    public function resetLicense(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $subscription = $this->primarySubscription($tenant);
        if ($subscription === null) {
            return $this->back(
                $tenant,
                __('No product subscription found for :tenant. Open the command center to provision one.', ['tenant' => $tenant->company_name]),
            );
        }

        $subscription->update([
            'product_status' => 'active',
            'license_status' => 'active',
            'disabled_reason' => null,
        ]);

        $this->syncSubscriptionWhenLinked($tenant);

        $this->activityLogger->log(
            $tenant,
            'tenant.license_reset',
            __('License reset to active from control center drawer.'),
            ['subscription_id' => $subscription->id],
        );

        return $this->back($tenant, __('License reset to active for :tenant.', ['tenant' => $tenant->company_name]));
    }

    public function restartServices(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $subscription = $this->primarySubscription($tenant);
        if ($subscription === null) {
            return $this->back(
                $tenant,
                __('No product subscription found — configure integrations in the command center.'),
            );
        }

        $integration = TenantProjectServiceIntegration::query()
            ->where('tenant_project_subscription_id', $subscription->id)
            ->where('is_tenant_system', true)
            ->orderByDesc('updated_at')
            ->first();

        if ($integration === null) {
            return redirect()
                ->to(route('tenants.show', $tenant).'?tab=integrations')
                ->with('status', __('No tenant system integration configured. Open Integrations to connect the hosted app.'));
        }

        return redirect()->route(
            'tenants.project-subscriptions.integrations.heartbeat',
            [$tenant, $subscription, $integration],
        );
    }

    private function primarySubscription(Tenant $tenant): ?TenantProjectSubscription
    {
        return $tenant->projectSubscriptions()->orderBy('id')->first();
    }

    private function resolveTenantAppUrl(Tenant $tenant): ?string
    {
        if (filled($tenant->login_url)) {
            return $tenant->login_url;
        }

        $domain = $tenant->tenant_domain ?? $tenant->project?->domain;
        if (! filled($domain) || $domain === '—') {
            return null;
        }

        $host = Str::lower(trim(preg_replace('#^https?://#i', '', $domain) ?? ''));

        return $host !== '' ? 'https://'.$host : null;
    }

    private function syncSubscriptionWhenLinked(Tenant $tenant): void
    {
        $tenant = $tenant->fresh(['project.product']);
        $productId = $tenant->product_id ?? $tenant->project?->product_id;

        if (! $tenant->project || $productId === null) {
            return;
        }

        try {
            $this->projectProvisioner->syncPrimarySubscription($tenant);
        } catch (\InvalidArgumentException) {
            // Tenant is not linked to a provisionable hosted project yet.
        }
    }

    private function back(Tenant $tenant, string $message): RedirectResponse
    {
        return redirect()
            ->route('tenants.index')
            ->with('status', $message)
            ->with('tenant_drawer', $tenant->id);
    }
}
