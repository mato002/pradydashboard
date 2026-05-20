<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Support\ActivityLogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantProjectLicenseController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function activate(Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        return $this->transition($tenant, $subscription, [
            'product_status' => 'active',
            'license_status' => 'active',
            'disabled_reason' => null,
        ], __('Product activated.'));
    }

    public function suspend(Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        return $this->transition($tenant, $subscription, [
            'product_status' => 'suspended',
            'license_status' => 'suspended',
            'disabled_reason' => __('Suspended from control plane'),
        ], __('Product suspended.'));
    }

    public function disable(Tenant $tenant, TenantProjectSubscription $subscription, Request $request): RedirectResponse
    {
        return $this->transition($tenant, $subscription, [
            'product_status' => 'disabled',
            'license_status' => 'expired',
            'disabled_reason' => $request->input('disabled_reason', __('Disabled from control plane')),
        ], __('Product disabled.'));
    }

    public function extendGrace(Tenant $tenant, TenantProjectSubscription $subscription, Request $request): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $days = (int) $request->validate(['days' => ['required', 'integer', 'min:1', 'max:365']])['days'];

        $old = ['grace_period_days' => $subscription->grace_period_days, 'license_status' => $subscription->license_status];

        $subscription->update([
            'license_status' => 'grace',
            'grace_period_days' => $days,
        ]);

        $this->activityLogger->log(
            'license.grace_updated',
            ActivityLogCategory::LICENSE,
            __('Grace period set to :days days', ['days' => $days]),
            $subscription,
            $old,
            ['grace_period_days' => $days, 'license_status' => 'grace'],
        );

        return back()->with('status', __('Grace period updated to :days days.', ['days' => $days]));
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function transition(Tenant $tenant, TenantProjectSubscription $subscription, array $attrs, string $message): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $old = array_intersect_key($subscription->getAttributes(), $attrs);
        $subscription->update($attrs);

        $action = match ($attrs['product_status'] ?? '') {
            'active' => 'license.activated',
            'suspended' => 'license.suspended',
            'disabled' => 'license.disabled',
            default => 'license.updated',
        };

        $this->activityLogger->log(
            $action,
            ActivityLogCategory::LICENSE,
            $message,
            $subscription,
            $old,
            $attrs,
        );

        return back()->with('status', $message);
    }
}
