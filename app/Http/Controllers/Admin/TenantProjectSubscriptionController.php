<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantProjectSubscriptionController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly TenantProjectProvisioner $provisioner
    ) {}

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'package_name' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['nullable', 'in:monthly,yearly,annual,per_user,usage_based,one_off,hybrid'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'setup_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'renewal_date' => ['nullable', 'date'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);

        $subscription = TenantProjectSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'package_name' => $data['package_name'] ?? $product->name,
            'billing_cycle' => $data['billing_cycle'] ?? $product->billing_model ?? 'monthly',
            'monthly_fee' => $data['monthly_fee'] ?? $product->default_monthly_fee,
            'setup_fee' => $data['setup_fee'] ?? $product->default_setup_fee,
            'currency' => $data['currency'] ?? $product->currency ?? 'KES',
            'renewal_date' => $data['renewal_date'] ?? null,
            'contract_status' => 'draft',
            'license_status' => 'active',
            'product_status' => 'active',
            'grace_period_days' => $product->grace_period_days,
            'kill_switch_enabled' => (bool) $product->kill_switch_allowed,
            'offline_mode_allowed' => (bool) $product->offline_mode_allowed,
        ]);

        return back()->with('status', __('Project subscription added.'));
    }

    public function update(Request $request, Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $data = $request->validate([
            'package_name' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['nullable', 'string', 'max:20'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'setup_fee' => ['nullable', 'numeric', 'min:0'],
            'renewal_date' => ['nullable', 'date'],
            'contract_status' => ['nullable', 'in:draft,pending_signature,active,expired,terminated'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $old = $subscription->only(array_keys($data));
        $subscription->update($data);

        $this->activityLogger->log(
            'tenant.subscription_updated',
            ActivityLogCategory::TENANT,
            __('Project subscription updated for :tenant', ['tenant' => $tenant->company_name]),
            $subscription,
            $old,
            $subscription->only(array_keys($data)),
        );

        return back()->with('status', __('Subscription updated.'));
    }

    public function sync(Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        $this->provisioner->syncPrimarySubscription($tenant);

        return back()->with('status', __('Primary project subscription synced from tenant record.'));
    }
}
