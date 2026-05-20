<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantCommunication;
use App\Support\ActivityLogCategory;
use App\Support\SupportOpsOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantCommunicationController extends Controller
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

        $communication = TenantCommunication::query()->create($data);

        $this->activityLogger->log(
            'communication.logged',
            ActivityLogCategory::COMMUNICATION,
            __('Communication logged via :channel', ['channel' => $data['channel']]),
            $communication,
            null,
            ['status' => $data['status'], 'follow_up_required' => $data['follow_up_required']],
        );

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'communications'])
            ->with('status', __('Communication logged.'));
    }

    public function updateStatus(Request $request, Tenant $tenant, TenantCommunication $communication): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($communication->tenant_id === $tenant->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(SupportOpsOptions::communicationStatuses()))],
        ]);

        $old = ['status' => $communication->status];
        $communication->update($data);

        if ($data['status'] === 'completed') {
            $this->activityLogger->log(
                'communication.follow_up_completed',
                ActivityLogCategory::COMMUNICATION,
                __('Follow-up marked completed'),
                $communication,
                $old,
                $data,
            );
        }

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'communications'])
            ->with('status', __('Communication updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Tenant $tenant): array
    {
        $subscriptionIds = $tenant->projectSubscriptions()->pluck('id');

        $data = $request->validate([
            'tenant_project_subscription_id' => ['nullable', Rule::in($subscriptionIds->all())],
            'staff_profile_id' => ['nullable', 'exists:staff_profiles,id'],
            'channel' => ['required', Rule::in(array_keys(SupportOpsOptions::channels()))],
            'direction' => ['required', Rule::in(array_keys(SupportOpsOptions::directions()))],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:20000'],
            'communication_date' => ['required', 'date'],
            'follow_up_required' => ['sometimes', 'boolean'],
            'follow_up_date' => ['nullable', 'date'],
            'related_support_ticket_id' => ['nullable', 'exists:support_tickets,id'],
        ]);

        $data['follow_up_required'] = $request->boolean('follow_up_required');
        $data['status'] = $data['follow_up_required'] ? 'pending_follow_up' : 'logged';

        return $data;
    }
}
