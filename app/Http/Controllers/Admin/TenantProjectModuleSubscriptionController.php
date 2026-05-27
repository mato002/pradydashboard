<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\ProjectModule;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use App\Models\TenantProjectModuleSubscription;
use App\Models\TenantProjectSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantProjectModuleSubscriptionController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function update(Request $request, Tenant $tenant, TenantProjectSubscription $subscription): RedirectResponse
    {
        $this->authorizeTenantSubscriptionRbac($tenant, $subscription);

        $subscription->load('project');
        $projectId = $subscription->project_id;

        $validated = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*.enabled' => ['nullable', 'boolean'],
            'modules.*.subscribed' => ['nullable', 'boolean'],
            'modules.*.billing_status' => ['nullable', Rule::in(['active', 'trial', 'suspended', 'waived', 'cancelled'])],
            'modules.*.monthly_price_override' => ['nullable', 'numeric', 'min:0'],
            'modules.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $moduleIds = ProjectModule::query()
            ->where('product_id', $projectId)
            ->pluck('id');

        $changedModules = [];

        foreach ($validated['modules'] ?? [] as $moduleId => $payload) {
            $moduleId = (int) $moduleId;
            if (! $moduleIds->contains($moduleId)) {
                continue;
            }

            $enabled = $request->boolean("modules.{$moduleId}.enabled");
            $subscribed = $request->boolean("modules.{$moduleId}.subscribed");

            $existing = TenantProjectModuleSubscription::query()
                ->where('tenant_project_subscription_id', $subscription->id)
                ->where('project_module_id', $moduleId)
                ->first();

            $activatedAt = $existing?->activated_at;
            $suspendedAt = $existing?->suspended_at;

            if ($enabled && ! $existing?->activated_at) {
                $activatedAt = now();
                $suspendedAt = null;
            } elseif (! $enabled && $existing?->activated_at) {
                $suspendedAt = now();
            } elseif ($enabled) {
                $suspendedAt = null;
            }

            $record = TenantProjectModuleSubscription::query()->updateOrCreate(
                [
                    'tenant_project_subscription_id' => $subscription->id,
                    'project_module_id' => $moduleId,
                ],
                [
                    'enabled' => $enabled,
                    'subscribed' => $subscribed,
                    'billing_status' => $payload['billing_status'] ?? 'active',
                    'monthly_price_override' => filled($payload['monthly_price_override'] ?? null)
                        ? $payload['monthly_price_override']
                        : null,
                    'notes' => $payload['notes'] ?? null,
                    'activated_at' => $activatedAt,
                    'suspended_at' => $suspendedAt,
                ]
            );

            if ($record->wasRecentlyCreated || $record->wasChanged()) {
                $moduleName = ProjectModule::query()->whereKey($moduleId)->value('name') ?? (string) $moduleId;
                $changedModules[] = $moduleName;
            }
        }

        if ($changedModules !== []) {
            $subscription->load('project');
            $this->activityLogger->log(
                'tenant.modules_updated',
                ActivityLogCategory::TENANT,
                __('Module subscriptions updated for :tenant — :project (:count modules)', [
                    'tenant' => $tenant->company_name,
                    'project' => $subscription->project?->name ?? __('subscription'),
                    'count' => count($changedModules),
                ]),
                $subscription,
                null,
                ['modules' => $changedModules],
            );
        }

        return redirect()
            ->route('tenants.show', [
                'tenant' => $tenant,
                'tab' => 'modules',
                'subscription' => $subscription->id,
            ])
            ->with('status', __('Module subscriptions updated.'));
    }
}
