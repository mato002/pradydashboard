<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Hr\HrOverview;
use App\Domain\Support\SupportOperationsSummary;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\Project;
use App\Models\StaffProfile;
use App\Support\OperationalMetrics;
use App\Support\SupportOpsOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketsController extends Controller
{
    public function __construct(
        private readonly HrOverview $hrOverview,
        private readonly SupportOperationsSummary $supportSummary,
        private readonly ActivityLogQuery $activityQuery,
    ) {}

    public function index(): View
    {
        $summary = $this->supportSummary->platform();
        $openStatuses = SupportOpsOptions::openTicketStatuses();

        $dbTickets = app(\App\Domain\Rbac\RbacScopeFilter::class)
            ->applyTenantForeignScope(SupportTicket::query())
            ->with(['tenant:id,company_name', 'assignedStaff:id,full_name', 'project:id,name'])
            ->latest('opened_at')
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $tickets = $dbTickets->map(fn (SupportTicket $ticket) => $this->mapTicketRow($ticket))->all();

        $kpis = [
            'open_tickets' => [
                'value' => $summary['open_tickets'],
                'trend' => null,
                'sublabel' => __('Active queue'),
                'tone' => 'sky',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'critical_incidents' => [
                'value' => $summary['urgent_tickets'],
                'trend' => null,
                'sublabel' => __('Urgent priority'),
                'tone' => 'rose',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'sla_breaches' => [
                'value' => $summary['overdue_tickets'],
                'trend' => null,
                'sublabel' => __('Past due date'),
                'tone' => 'amber',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'avg_resolution' => [
                'value' => SupportTicket::query()
                    ->whereNotNull('resolved_at')
                    ->count(),
                'trend' => null,
                'sublabel' => __('Resolved tickets'),
                'tone' => 'emerald',
                'points' => OperationalMetrics::emptySparkline(),
                'animate' => false,
            ],
            'active_agents' => [
                'value' => StaffProfile::query()->where('status', 'active')->count(),
                'trend' => null,
                'sublabel' => __('Active staff'),
                'tone' => 'indigo',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'resolved_today' => [
                'value' => SupportTicket::query()
                    ->whereDate('resolved_at', today())
                    ->count(),
                'trend' => null,
                'sublabel' => __('Resolved today'),
                'tone' => 'violet',
                'points' => OperationalMetrics::emptySparkline(),
            ],
        ];

        $incidents = [];
        $agents = StaffProfile::query()
            ->where('status', 'active')
            ->withCount(['assignedTickets' => fn ($q) => $q->whereIn('status', $openStatuses)])
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(fn (StaffProfile $s) => [
                'name' => $s->full_name,
                'role' => $s->job_title ?? __('Staff'),
                'status' => 'online',
                'tickets' => $s->assigned_tickets_count,
                'sla_pct' => '—',
                'avg_response' => '—',
            ])
            ->all();

        $slaOverview = [
            'response_target' => '—',
            'resolution_target' => '—',
            'compliance_pct' => 0,
            'escalation_threshold' => '—',
            'overdue' => $dbTickets->filter(fn (SupportTicket $t) => $t->isOverdue())->take(5)->map(fn ($t) => $this->mapTicketRow($t))->values()->all(),
            'timers' => [],
        ];

        $analytics = [
            'resolution_trend' => collect(range(6, 0))->map(fn ($d) => [
                'label' => now()->subDays($d)->format('D'),
                'value' => SupportTicket::query()->whereDate('resolved_at', now()->subDays($d))->count(),
            ])->reverse()->values()->all(),
            'response_times' => [],
            'satisfaction' => 0,
            'categories' => SupportTicket::query()
                ->selectRaw('category, count(*) as cnt')
                ->groupBy('category')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'name' => SupportOpsOptions::categories()[$row->category] ?? $row->category,
                    'count' => (int) $row->cnt,
                    'pct' => 0,
                ])
                ->all(),
            'recurring' => [],
        ];

        $automation = [];
        $conversations = [];

        return view('admin.support-tickets.index', compact(
            'kpis',
            'tickets',
            'incidents',
            'agents',
            'slaOverview',
            'analytics',
            'automation',
            'conversations',
            'summary',
        ));
    }

    public function create(): View
    {
        return view('admin.support-tickets.create', [
            'ticket' => new SupportTicket(['status' => 'open', 'priority' => 'medium', 'opened_at' => now()]),
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['opened_at'] = now();
        $data['category'] = $data['category'] ?? 'other';
        $data['source'] = $data['source'] ?? 'internal';

        $ticket = SupportTicket::query()->create($data);

        return redirect()
            ->route('support-tickets.show', $ticket->id)
            ->with('status', __('Ticket created successfully.'));
    }

    public function show(string $reference): View
    {
        $ticket = SupportTicket::query()
            ->with([
                'tenant:id,company_name',
                'project:id,name',
                'assignedStaff',
                'comments.staffProfile',
                'comments.user',
            ])
            ->findOrFail($reference);

        $profile = $this->mapTicketRow($ticket);
        $profile['description'] = $ticket->description;
        $profile['resolution_notes'] = $ticket->resolution_notes;

        $commentTypes = SupportOpsOptions::commentTypes();
        $visibilities = SupportOpsOptions::visibilities();
        $staffAssignments = $this->hrOverview->assignmentsFor(
            $ticket->load('activeStaffAssignments.staffProfile.department')
        );

        $activityLogs = $this->activityQuery->forContext(supportTicketId: $ticket->id);

        return view('admin.support-tickets.show', compact(
            'ticket',
            'profile',
            'commentTypes',
            'visibilities',
            'staffAssignments',
            'activityLogs',
        ));
    }

    public function edit(string $reference): View
    {
        $ticket = SupportTicket::query()->findOrFail($reference);
        $profile = $this->mapTicketRow($ticket);

        return view('admin.support-tickets.edit', [
            'ticket' => $ticket,
            'profile' => $profile,
            'reference' => (string) $ticket->id,
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'isDemo' => false,
        ]);
    }

    public function update(Request $request, string $reference): RedirectResponse
    {
        $ticket = SupportTicket::query()->findOrFail($reference);
        $ticket->update($this->validated($request));

        if (in_array($ticket->status, ['resolved', 'closed'], true) && ! $ticket->closed_at) {
            $ticket->update(['closed_at' => now(), 'resolved_at' => $ticket->resolved_at ?? now()]);
        }

        return redirect()
            ->route('support-tickets.show', $ticket->id)
            ->with('status', __('Ticket updated.'));
    }

    public function destroy(string $reference): RedirectResponse
    {
        $ticket = SupportTicket::query()->findOrFail($reference);
        $ticket->delete();

        return redirect()->route('support-tickets.index')->with('status', __('Ticket deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_staff_id' => ['nullable', 'exists:staff_profiles,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'category' => ['nullable', Rule::in(array_keys(SupportOpsOptions::categories()))],
            'priority' => ['required', Rule::in(array_keys(SupportOpsOptions::priorities()))],
            'status' => ['required', Rule::in(array_keys(SupportOpsOptions::ticketStatuses()))],
            'source' => ['nullable', Rule::in(array_keys(SupportOpsOptions::sources()))],
            'due_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTicketRow(SupportTicket $ticket): array
    {
        return [
            'id' => 'TKT-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT),
            'db_id' => $ticket->id,
            'tenant' => $ticket->tenant?->company_name ?? __('Unassigned tenant'),
            'project' => $ticket->project?->name,
            'subject' => $ticket->subject,
            'category' => SupportOpsOptions::categories()[$ticket->category ?? 'other'] ?? ($ticket->category ?? 'other'),
            'priority' => $ticket->priority ?? 'medium',
            'assigned_to' => $ticket->assignedStaff?->full_name ?? __('Unassigned'),
            'sla_status' => $ticket->isOverdue() ? 'breached' : 'on_track',
            'sla_progress' => $ticket->isOverdue() ? 100 : 0,
            'last_response' => $ticket->updated_at?->diffForHumans() ?? __('Just now'),
            'status' => $ticket->status,
            'resolved_today' => $ticket->resolved_at?->isToday() ?? false,
            'opened_at' => $ticket->opened_at?->format('M j, Y g:i A') ?? '—',
        ];
    }
}
