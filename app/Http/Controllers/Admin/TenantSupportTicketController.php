<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Support\ActivityLogCategory;
use App\Models\SupportTicketComment;
use App\Models\Tenant;
use App\Support\SupportOpsOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantSupportTicketController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $data = $this->validatedTicket($request, $tenant);
        $data['tenant_id'] = $tenant->id;
        $data['project_id'] = $data['project_id'] ?? $tenant->project_id;
        $data['opened_at'] = now();
        $data['status'] = $data['status'] ?? 'open';

        $ticket = SupportTicket::query()->create($data);

        $this->activityLogger->log(
            'support.ticket_created',
            ActivityLogCategory::SUPPORT,
            __('Support ticket created: :subject', ['subject' => $ticket->subject]),
            $ticket,
        );

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'support', 'ticket' => $ticket->id])
            ->with('status', __('Support ticket created.'));
    }

    public function update(Request $request, Tenant $tenant, SupportTicket $ticket): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($ticket->tenant_id === $tenant->id, 404);

        $oldStatus = $ticket->status;
        $oldAssignee = $ticket->assigned_staff_id;
        $ticket->update($this->validatedTicket($request, $tenant, $ticket));

        if ($oldStatus !== $ticket->status) {
            $this->logStatusChange($ticket, $oldStatus, $request->user()?->id);
        }

        if ($oldAssignee !== $ticket->assigned_staff_id) {
            $this->activityLogger->log(
                'support.ticket_assigned',
                ActivityLogCategory::SUPPORT,
                __('Ticket assigned to staff #:id', ['id' => $ticket->assigned_staff_id ?? 'none']),
                $ticket,
                ['assigned_staff_id' => $oldAssignee],
                ['assigned_staff_id' => $ticket->assigned_staff_id],
            );
        }

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'support', 'ticket' => $ticket->id])
            ->with('status', __('Ticket updated.'));
    }

    public function resolve(Request $request, Tenant $tenant, SupportTicket $ticket): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($ticket->tenant_id === $tenant->id, 404);

        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:10000'],
        ]);

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $data['resolution_notes'],
        ]);

        SupportTicketComment::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()?->id,
            'comment_type' => 'resolution',
            'message' => $data['resolution_notes'],
            'visibility' => 'internal',
        ]);

        $this->activityLogger->log(
            'support.ticket_resolved',
            ActivityLogCategory::SUPPORT,
            __('Ticket resolved: :subject', ['subject' => $ticket->subject]),
            $ticket,
            ['status' => 'in_progress'],
            ['status' => 'resolved'],
        );

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'support', 'ticket' => $ticket->id])
            ->with('status', __('Ticket marked resolved.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedTicket(Request $request, Tenant $tenant, ?SupportTicket $ticket = null): array
    {
        $subscriptionIds = $tenant->projectSubscriptions()->pluck('id');

        return $request->validate([
            'tenant_project_subscription_id' => ['nullable', Rule::in($subscriptionIds->all())],
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_staff_id' => ['nullable', 'exists:staff_profiles,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'category' => ['required', Rule::in(array_keys(SupportOpsOptions::categories()))],
            'priority' => ['required', Rule::in(array_keys(SupportOpsOptions::priorities()))],
            'status' => ['sometimes', Rule::in(array_keys(SupportOpsOptions::ticketStatuses()))],
            'source' => ['required', Rule::in(array_keys(SupportOpsOptions::sources()))],
            'due_at' => ['nullable', 'date'],
        ]);
    }

    private function logStatusChange(SupportTicket $ticket, string $from, ?int $userId): void
    {
        SupportTicketComment::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $userId,
            'comment_type' => 'status_change',
            'message' => __('Status changed from :from to :to.', [
                'from' => $from,
                'to' => $ticket->status,
            ]),
            'visibility' => 'internal',
        ]);
    }
}
