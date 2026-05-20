<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\Tenant;
use App\Support\ActivityLogCategory;
use App\Support\SupportOpsOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketCommentController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function storeForTenant(Request $request, Tenant $tenant, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $tenant);
        abort_unless($ticket->tenant_id === $tenant->id, 404);

        return $this->store($request, $ticket, redirect()->route('tenants.show', [
            'tenant' => $tenant,
            'tab' => 'support',
            'ticket' => $ticket->id,
        ]));
    }

    public function storeGlobal(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->exists, 404);

        return $this->store($request, $ticket, redirect()->route('support-tickets.show', $ticket->id));
    }

    private function store(Request $request, SupportTicket $ticket, RedirectResponse $redirect): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:20000'],
            'comment_type' => ['required', Rule::in(array_keys(SupportOpsOptions::commentTypes()))],
            'visibility' => ['required', Rule::in(array_keys(SupportOpsOptions::visibilities()))],
            'staff_profile_id' => ['nullable', 'exists:staff_profiles,id'],
        ]);

        SupportTicketComment::query()->create([
            ...$data,
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()?->id,
        ]);

        $this->activityLogger->log(
            'support.comment_added',
            ActivityLogCategory::SUPPORT,
            __('Comment added on ticket #:id', ['id' => $ticket->id]),
            $ticket,
            null,
            [
                'comment_type' => $data['comment_type'],
                'visibility' => $data['visibility'],
            ],
        );

        return $redirect->with('status', __('Comment added.'));
    }
}
