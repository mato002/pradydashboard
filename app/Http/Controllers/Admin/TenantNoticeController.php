<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use App\Models\TenantNotice;
use App\Support\SupportOpsOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantNoticeController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $data = $this->validated($request, $tenant);
        $data['tenant_id'] = $tenant->id;

        if (($data['status'] ?? 'draft') === 'sent' && empty($data['sent_at'])) {
            $data['sent_at'] = now();
        }

        TenantNotice::query()->create($data);

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'notices'])
            ->with('status', __('Notice created.'));
    }

    public function markSent(Tenant $tenant, TenantNotice $notice): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($notice->tenant_id === $tenant->id, 404);

        $notice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->activityLogger->log(
            'notice.sent',
            ActivityLogCategory::COMMUNICATION,
            __('Notice sent to :tenant: :title', ['tenant' => $tenant->company_name, 'title' => $notice->title]),
            $notice,
            ['status' => 'draft'],
            ['status' => 'sent'],
        );

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'notices'])
            ->with('status', __('Notice marked as sent.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Tenant $tenant): array
    {
        $subscriptionIds = $tenant->projectSubscriptions()->pluck('id');

        return $request->validate([
            'tenant_project_subscription_id' => ['nullable', Rule::in($subscriptionIds->all())],
            'project_id' => ['nullable', 'exists:projects,id'],
            'notice_type' => ['required', Rule::in(array_keys(SupportOpsOptions::noticeTypes()))],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:20000'],
            'severity' => ['required', Rule::in(array_keys(SupportOpsOptions::severities()))],
            'status' => ['required', Rule::in(array_keys(SupportOpsOptions::noticeStatuses()))],
            'scheduled_at' => ['nullable', 'date'],
        ]);
    }
}
