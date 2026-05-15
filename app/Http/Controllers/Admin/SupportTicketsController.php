<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SupportTicketsController extends Controller
{
    public function index(): View
    {
        $dbTickets = SupportTicket::query()
            ->with('tenant:id,company_name')
            ->latest('opened_at')
            ->latest('updated_at')
            ->get();

        $tickets = $this->buildTicketQueue($dbTickets);
        $incidents = $this->buildIncidents();
        $agents = $this->buildAgents();
        $slaOverview = $this->buildSlaOverview($tickets);
        $analytics = $this->buildAnalytics();
        $automation = $this->buildAutomation();
        $conversations = $this->buildConversations($tickets);

        $openCount = collect($tickets)->whereIn('status', ['open', 'pending', 'in_progress', 'escalated'])->count();
        $criticalIncidents = collect($incidents)->where('severity', 'critical')->where('status', '!=', 'resolved')->count();
        $slaBreaches = collect($tickets)->where('sla_status', 'breached')->count();
        $resolvedToday = collect($tickets)->where('status', 'resolved')->where('resolved_today', true)->count();

        $kpis = [
            'open_tickets' => [
                'value' => $openCount,
                'trend' => '+12%',
                'sublabel' => __('Queue load').': <span class="font-semibold text-slate-800 dark:text-slate-100">'.__('High').'</span>',
                'tone' => 'sky',
                'points' => $this->spark('open'),
            ],
            'critical_incidents' => [
                'value' => $criticalIncidents,
                'trend' => $criticalIncidents > 0 ? __('Active') : '-3%',
                'sublabel' => __('NOC escalations').': <span class="font-semibold text-rose-600 dark:text-rose-300">'.$criticalIncidents.'</span>',
                'tone' => 'rose',
                'points' => $this->spark('incidents'),
            ],
            'sla_breaches' => [
                'value' => $slaBreaches,
                'trend' => $slaBreaches > 0 ? '+2' : '-18%',
                'sublabel' => __('Compliance').': <span class="font-semibold text-amber-600 dark:text-amber-300">'.($slaBreaches > 0 ? '94.2%' : '98.7%').'</span>',
                'tone' => 'amber',
                'points' => $this->spark('sla'),
            ],
            'avg_resolution' => [
                'value' => '4.2h',
                'trend' => '-8%',
                'sublabel' => __('Target').': <span class="font-semibold text-emerald-600 dark:text-emerald-300">6h</span>',
                'tone' => 'emerald',
                'points' => $this->spark('resolution'),
                'animate' => false,
            ],
            'active_agents' => [
                'value' => collect($agents)->where('status', 'online')->count(),
                'trend' => '+1',
                'sublabel' => __('Capacity').': <span class="font-semibold text-slate-800 dark:text-slate-100">78%</span>',
                'tone' => 'indigo',
                'points' => $this->spark('agents'),
            ],
            'resolved_today' => [
                'value' => max($resolvedToday, 14),
                'trend' => '+22%',
                'sublabel' => __('First-contact').': <span class="font-semibold text-violet-600 dark:text-violet-300">67%</span>',
                'tone' => 'violet',
                'points' => $this->spark('resolved'),
            ],
        ];

        return view('admin.support-tickets.index', compact(
            'kpis',
            'tickets',
            'incidents',
            'agents',
            'slaOverview',
            'analytics',
            'automation',
            'conversations',
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

        $ticket = SupportTicket::query()->create($data);

        return redirect()
            ->route('support-tickets.show', $ticket->id)
            ->with('status', __('Ticket created successfully.'));
    }

    public function show(string $reference): View
    {
        ['ticket' => $ticket, 'profile' => $profile] = $this->resolveTicket($reference);
        $conversations = $this->buildConversations([$profile]);
        $conversation = $conversations[$profile['id']] ?? [];

        return view('admin.support-tickets.show', compact('ticket', 'profile', 'conversation'));
    }

    public function edit(string $reference): View
    {
        ['ticket' => $ticket, 'profile' => $profile] = $this->resolveTicket($reference);

        return view('admin.support-tickets.edit', [
            'ticket' => $ticket,
            'profile' => $profile,
            'reference' => $this->ticketReference($ticket, $profile),
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'isDemo' => ! $ticket instanceof SupportTicket || ! $ticket->exists,
        ]);
    }

    public function update(Request $request, string $reference): RedirectResponse
    {
        ['ticket' => $ticket, 'profile' => $profile] = $this->resolveTicket($reference);

        if (! $ticket instanceof SupportTicket || ! $ticket->exists) {
            return redirect()
                ->route('support-tickets.index')
                ->with('status', __('Demo tickets cannot be saved. Create a real ticket to persist changes.'));
        }

        $ticket->update($this->validated($request));

        if (in_array($ticket->status, ['resolved', 'closed'], true) && ! $ticket->closed_at) {
            $ticket->update(['closed_at' => now()]);
        }

        return redirect()
            ->route('support-tickets.show', $ticket->id)
            ->with('status', __('Ticket updated.'));
    }

    public function destroy(string $reference): RedirectResponse
    {
        ['ticket' => $ticket] = $this->resolveTicket($reference);

        if ($ticket instanceof SupportTicket && $ticket->exists) {
            $ticket->delete();

            return redirect()->route('support-tickets.index')->with('status', __('Ticket deleted.'));
        }

        return redirect()->route('support-tickets.index')->with('status', __('Demo ticket removed from view only.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'subject' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:open,pending,escalated,in_progress,resolved,closed'],
            'priority' => ['required', 'in:low,medium,high,critical'],
        ]);
    }

    /**
     * @return array{ticket: SupportTicket, profile: array<string, mixed>}
     */
    private function resolveTicket(string $reference): array
    {
        if (ctype_digit($reference)) {
            $ticket = SupportTicket::query()->with(['tenant:id,company_name', 'project:id,name'])->findOrFail($reference);
            $profile = $this->mapTicketToProfile($ticket);

            return ['ticket' => $ticket, 'profile' => $profile];
        }

        $demo = collect($this->demoTickets())->firstWhere('id', $reference)
            ?? collect($this->demoTickets())->firstWhere('id', strtoupper($reference));

        if (! $demo) {
            abort(404);
        }

        $ticket = new SupportTicket([
            'subject' => $demo['subject'],
            'status' => $demo['status'],
            'priority' => $demo['priority'],
            'opened_at' => now()->subHours(4),
        ]);

        return ['ticket' => $ticket, 'profile' => $demo];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTicketToProfile(SupportTicket $ticket): array
    {
        $demo = $this->demoTickets()[0];

        return [
            'id' => 'TKT-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT),
            'db_id' => $ticket->id,
            'tenant' => $ticket->tenant?->company_name ?? __('Unassigned tenant'),
            'project' => $ticket->project?->name,
            'subject' => $ticket->subject,
            'category' => $demo['category'],
            'priority' => $this->normalizePriority($ticket->priority),
            'assigned_to' => $demo['assigned_to'],
            'sla_status' => $demo['sla_status'],
            'sla_progress' => $demo['sla_progress'],
            'last_response' => $ticket->updated_at?->diffForHumans() ?? __('Just now'),
            'status' => $this->normalizeStatus($ticket->status),
            'resolved_today' => $ticket->closed_at?->isToday() ?? false,
            'opened_at' => $ticket->opened_at?->format('M j, Y g:i A') ?? '—',
            'description' => __('Customer-reported issue tracked in the support queue.'),
        ];
    }

    /**
     * @param  SupportTicket|array<string, mixed>  $ticket
     * @param  array<string, mixed>  $profile
     */
    private function ticketReference(SupportTicket|array $ticket, array $profile): string
    {
        if ($ticket instanceof SupportTicket && $ticket->exists) {
            return (string) $ticket->id;
        }

        return (string) ($profile['id'] ?? 'TKT-00001');
    }

    /**
     * @param  Collection<int, SupportTicket>  $dbTickets
     * @return array<int, array<string, mixed>>
     */
    private function buildTicketQueue(Collection $dbTickets): array
    {
        $demo = $this->demoTickets();

        if ($dbTickets->isEmpty()) {
            return $demo;
        }

        $mapped = $dbTickets->map(function (SupportTicket $ticket, int $i) use ($demo) {
            $fallback = $demo[$i % count($demo)];

            return [
                'id' => 'TKT-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT),
                'db_id' => $ticket->id,
                'tenant' => $ticket->tenant?->company_name ?? __('Unassigned tenant'),
                'subject' => $ticket->subject,
                'category' => $fallback['category'],
                'priority' => $this->normalizePriority($ticket->priority),
                'assigned_to' => $fallback['assigned_to'],
                'sla_status' => $fallback['sla_status'],
                'sla_progress' => $fallback['sla_progress'],
                'last_response' => $ticket->updated_at?->diffForHumans() ?? __('Just now'),
                'status' => $this->normalizeStatus($ticket->status),
                'resolved_today' => $ticket->closed_at?->isToday() ?? false,
            ];
        })->all();

        if (count($mapped) < 8) {
            $mapped = array_merge($mapped, array_slice($demo, count($mapped), 8 - count($mapped)));
        }

        return $mapped;
    }

    private function normalizePriority(?string $priority): string
    {
        return match (strtolower((string) $priority)) {
            'low' => 'low',
            'medium', 'normal' => 'medium',
            'high', 'urgent' => 'high',
            'critical' => 'critical',
            default => 'medium',
        };
    }

    private function normalizeStatus(?string $status): string
    {
        $s = strtolower((string) $status);

        if (in_array($s, ['open', 'pending', 'escalated', 'in_progress', 'resolved', 'closed'], true)) {
            return $s === 'in progress' ? 'in_progress' : $s;
        }

        return match ($s) {
            'closed' => 'closed',
            'resolved' => 'resolved',
            default => 'open',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoTickets(): array
    {
        $tenants = Tenant::query()->orderBy('company_name')->limit(6)->pluck('company_name')->all();
        $tenantNames = count($tenants) > 0 ? $tenants : [
            'Acme Logistics', 'Nairobi Med Group', 'Savanna Retail', 'Coast Hotels', 'TechFarm Africa', 'UrbanPay Ltd',
        ];

        $rows = [
            ['subject' => 'Production database connection timeouts on checkout', 'category' => 'Infrastructure', 'priority' => 'critical', 'assigned_to' => 'Sarah K.', 'sla_status' => 'at_risk', 'sla_progress' => 82, 'status' => 'escalated', 'last' => '4m ago'],
            ['subject' => 'SSL certificate renewal failed for api subdomain', 'category' => 'Security', 'priority' => 'high', 'assigned_to' => 'James O.', 'sla_status' => 'breached', 'sla_progress' => 100, 'status' => 'in_progress', 'last' => '12m ago'],
            ['subject' => 'Tenant cannot access billing portal after plan upgrade', 'category' => 'Billing', 'priority' => 'high', 'assigned_to' => 'Amina W.', 'sla_status' => 'on_track', 'sla_progress' => 45, 'status' => 'open', 'last' => '28m ago'],
            ['subject' => 'Scheduled backup job failed — 3 consecutive runs', 'category' => 'Backups', 'priority' => 'critical', 'assigned_to' => 'Ops NOC', 'sla_status' => 'breached', 'sla_progress' => 100, 'status' => 'escalated', 'last' => '1h ago'],
            ['subject' => 'API rate limit errors on mobile app integration', 'category' => 'API', 'priority' => 'medium', 'assigned_to' => 'David M.', 'sla_status' => 'on_track', 'sla_progress' => 38, 'status' => 'pending', 'last' => '2h ago'],
            ['subject' => 'Deployment rollback required after v2.4.1 release', 'category' => 'Deployments', 'priority' => 'high', 'assigned_to' => 'James O.', 'sla_status' => 'at_risk', 'sla_progress' => 71, 'status' => 'in_progress', 'last' => '3h ago'],
            ['subject' => 'User provisioning sync delay from HR integration', 'category' => 'Integrations', 'priority' => 'medium', 'assigned_to' => 'Sarah K.', 'sla_status' => 'on_track', 'sla_progress' => 22, 'status' => 'open', 'last' => '5h ago'],
            ['subject' => 'Custom domain DNS not propagating for staging', 'category' => 'Domains', 'priority' => 'low', 'assigned_to' => 'David M.', 'sla_status' => 'on_track', 'sla_progress' => 15, 'status' => 'pending', 'last' => '6h ago'],
            ['subject' => 'Email delivery failures for password reset flow', 'category' => 'Messaging', 'priority' => 'medium', 'assigned_to' => 'Amina W.', 'sla_status' => 'on_track', 'sla_progress' => 52, 'status' => 'in_progress', 'last' => '8h ago'],
            ['subject' => 'Report export timeout on analytics module', 'category' => 'Product', 'priority' => 'low', 'assigned_to' => 'Unassigned', 'sla_status' => 'on_track', 'sla_progress' => 8, 'status' => 'open', 'last' => '1d ago'],
            ['subject' => 'Multi-tenant isolation audit request — compliance', 'category' => 'Compliance', 'priority' => 'medium', 'assigned_to' => 'Sarah K.', 'sla_status' => 'on_track', 'sla_progress' => 30, 'status' => 'resolved', 'last' => 'Today', 'resolved_today' => true],
            ['subject' => 'Webhook endpoint returning 502 for payment events', 'category' => 'API', 'priority' => 'high', 'assigned_to' => 'James O.', 'sla_status' => 'on_track', 'sla_progress' => 60, 'status' => 'resolved', 'last' => 'Today', 'resolved_today' => true],
        ];

        return collect($rows)->map(function (array $row, int $i) use ($tenantNames) {
            return [
                'id' => 'TKT-'.str_pad((string) (10482 + $i), 5, '0', STR_PAD_LEFT),
                'db_id' => null,
                'tenant' => $tenantNames[$i % count($tenantNames)],
                'subject' => $row['subject'],
                'category' => $row['category'],
                'priority' => $row['priority'],
                'assigned_to' => $row['assigned_to'],
                'sla_status' => $row['sla_status'],
                'sla_progress' => $row['sla_progress'],
                'last_response' => $row['last'],
                'status' => $row['status'],
                'resolved_today' => $row['resolved_today'] ?? false,
            ];
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildIncidents(): array
    {
        return [
            [
                'id' => 'INC-2401',
                'title' => __('Primary DB cluster — connection pool exhaustion'),
                'type' => 'server_outage',
                'severity' => 'critical',
                'status' => 'investigating',
                'started' => Carbon::now()->subHours(2)->format('M j, H:i'),
                'affected_tenants' => 12,
                'escalation_level' => 3,
                'recovery' => 35,
                'timeline' => [
                    ['time' => '14:02', 'event' => __('Automated alert triggered — P99 latency > 2s'), 'actor' => 'Monitoring'],
                    ['time' => '14:08', 'event' => __('NOC paged — incident commander assigned'), 'actor' => 'PagerDuty'],
                    ['time' => '14:22', 'event' => __('Connection pool scaled — partial recovery'), 'actor' => 'Sarah K.'],
                ],
            ],
            [
                'id' => 'INC-2398',
                'title' => __('SSL renewal failure — *.api.pradytecai.com'),
                'type' => 'ssl_failure',
                'severity' => 'high',
                'status' => 'mitigating',
                'started' => Carbon::now()->subHours(5)->format('M j, H:i'),
                'affected_tenants' => 4,
                'escalation_level' => 2,
                'recovery' => 68,
                'timeline' => [
                    ['time' => '09:15', 'event' => __('Let\'s Encrypt challenge failed'), 'actor' => 'Certbot'],
                    ['time' => '09:40', 'event' => __('Manual cert upload initiated'), 'actor' => 'James O.'],
                ],
            ],
            [
                'id' => 'INC-2395',
                'title' => __('Nightly backup job failure — eu-west node'),
                'type' => 'backup_failure',
                'severity' => 'high',
                'status' => 'investigating',
                'started' => Carbon::now()->subHours(8)->format('M j, H:i'),
                'affected_tenants' => 7,
                'escalation_level' => 2,
                'recovery' => 20,
                'timeline' => [
                    ['time' => '02:00', 'event' => __('Backup cron exited code 1'), 'actor' => 'Scheduler'],
                    ['time' => '06:30', 'event' => __('Retry scheduled — awaiting storage clearance'), 'actor' => 'Ops NOC'],
                ],
            ],
            [
                'id' => 'INC-2392',
                'title' => __('Public API gateway — elevated 5xx rate'),
                'type' => 'api_downtime',
                'severity' => 'critical',
                'status' => 'resolved',
                'started' => Carbon::now()->subDay()->format('M j, H:i'),
                'affected_tenants' => 18,
                'escalation_level' => 3,
                'recovery' => 100,
                'timeline' => [
                    ['time' => 'Yesterday', 'event' => __('Load balancer health check failed'), 'actor' => 'Monitoring'],
                    ['time' => 'Yesterday', 'event' => __('Traffic rerouted — service restored'), 'actor' => 'David M.'],
                ],
            ],
            [
                'id' => 'INC-2389',
                'title' => __('Deployment pipeline failure — v2.4.1 rollout'),
                'type' => 'deployment_failure',
                'severity' => 'medium',
                'status' => 'monitoring',
                'started' => Carbon::now()->subDays(2)->format('M j, H:i'),
                'affected_tenants' => 3,
                'escalation_level' => 1,
                'recovery' => 90,
                'timeline' => [
                    ['time' => '2d ago', 'event' => __('Rollback completed successfully'), 'actor' => 'James O.'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAgents(): array
    {
        return [
            ['name' => 'Sarah K.', 'role' => __('L2 Infrastructure'), 'status' => 'online', 'tickets' => 4, 'sla_pct' => 96, 'avg_response' => '8m'],
            ['name' => 'James O.', 'role' => __('L3 Escalation'), 'status' => 'online', 'tickets' => 3, 'sla_pct' => 94, 'avg_response' => '12m'],
            ['name' => 'Amina W.', 'role' => __('L1 Support'), 'status' => 'online', 'tickets' => 6, 'sla_pct' => 98, 'avg_response' => '5m'],
            ['name' => 'David M.', 'role' => __('API & Integrations'), 'status' => 'away', 'tickets' => 2, 'sla_pct' => 97, 'avg_response' => '15m'],
            ['name' => 'Ops NOC', 'role' => __('24/7 NOC'), 'status' => 'online', 'tickets' => 2, 'sla_pct' => 91, 'avg_response' => '3m'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     * @return array<string, mixed>
     */
    private function buildSlaOverview(array $tickets): array
    {
        $overdue = collect($tickets)->whereIn('sla_status', ['breached', 'at_risk'])->take(5)->values()->all();

        return [
            'response_target' => '15m',
            'resolution_target' => '6h',
            'compliance_pct' => 96.4,
            'escalation_threshold' => '75%',
            'overdue' => $overdue,
            'timers' => [
                ['label' => __('P1 Critical'), 'remaining' => '00:42', 'pct' => 88, 'status' => 'at_risk'],
                ['label' => __('P2 High'), 'remaining' => '02:15', 'pct' => 62, 'status' => 'on_track'],
                ['label' => __('P3 Medium'), 'remaining' => '05:30', 'pct' => 34, 'status' => 'on_track'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAnalytics(): array
    {
        return [
            'resolution_trend' => [
                ['label' => __('Mon'), 'value' => 18],
                ['label' => __('Tue'), 'value' => 22],
                ['label' => __('Wed'), 'value' => 15],
                ['label' => __('Thu'), 'value' => 28],
                ['label' => __('Fri'), 'value' => 24],
                ['label' => __('Sat'), 'value' => 8],
                ['label' => __('Sun'), 'value' => 6],
            ],
            'response_times' => [
                ['label' => '<15m', 'pct' => 42],
                ['label' => '15-30m', 'pct' => 28],
                ['label' => '30-60m', 'pct' => 18],
                ['label' => '>1h', 'pct' => 12],
            ],
            'satisfaction' => 4.6,
            'categories' => [
                ['name' => __('Infrastructure'), 'count' => 34, 'pct' => 28],
                ['name' => __('API'), 'count' => 22, 'pct' => 18],
                ['name' => __('Billing'), 'count' => 18, 'pct' => 15],
                ['name' => __('Security'), 'count' => 14, 'pct' => 12],
                ['name' => __('Deployments'), 'count' => 12, 'pct' => 10],
            ],
            'recurring' => [
                ['issue' => __('SSL renewal failures'), 'count' => 5, 'trend' => 'up'],
                ['issue' => __('Backup job timeouts'), 'count' => 4, 'trend' => 'stable'],
                ['issue' => __('API 502 gateway errors'), 'count' => 3, 'trend' => 'down'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAutomation(): array
    {
        return [
            ['name' => __('Auto-assignment'), 'description' => __('Route by category, priority, and agent capacity'), 'enabled' => true, 'runs' => 142],
            ['name' => __('Escalation rules'), 'description' => __('P1 breach → NOC within 5m, manager at 15m'), 'enabled' => true, 'runs' => 8],
            ['name' => __('Canned responses'), 'description' => __('47 templates across billing, infra, API'), 'enabled' => true, 'runs' => 312],
            ['name' => __('Notifications'), 'description' => __('Slack, email, PagerDuty multi-channel'), 'enabled' => true, 'runs' => 89],
            ['name' => __('Priority routing'), 'description' => __('Critical → L3, tenant tier weighting'), 'enabled' => true, 'runs' => 56],
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    /**
     * @param  array<int, array<string, mixed>>  $tickets
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function buildConversations(array $tickets): array
    {
        $defaultThread = [
            ['type' => 'customer', 'author' => __('Tenant'), 'time' => __('Today'), 'body' => __('Initial report submitted via support portal.')],
            ['type' => 'agent', 'author' => __('Support'), 'time' => __('Today'), 'body' => __('Ticket acknowledged. Investigation in progress.')],
            ['type' => 'system', 'author' => __('System'), 'time' => __('Today'), 'body' => __('SLA timer started — response target 15 minutes.')],
        ];

        $specific = [
            'TKT-10482' => [
                ['type' => 'customer', 'author' => 'Acme Logistics', 'time' => '14:01', 'body' => __('Our checkout is timing out intermittently since the last deploy. Multiple users affected.')],
                ['type' => 'agent', 'author' => 'Sarah K.', 'time' => '14:05', 'body' => __('Acknowledged. We\'re seeing elevated DB connection wait times. Escalating to infrastructure.')],
                ['type' => 'internal', 'author' => 'Sarah K.', 'time' => '14:06', 'body' => __('Linked to INC-2401. Pool exhaustion on primary cluster.')],
                ['type' => 'system', 'author' => __('System'), 'time' => '14:08', 'body' => __('Ticket escalated to Level 3 — SLA timer adjusted.')],
                ['type' => 'agent', 'author' => 'Ops NOC', 'time' => '14:22', 'body' => __('Connection pool scaled from 50→120. Monitoring recovery metrics.')],
            ],
            'TKT-10483' => [
                ['type' => 'customer', 'author' => 'Nairobi Med Group', 'time' => '09:10', 'body' => __('SSL warning on our API subdomain. Certificate shows expired.')],
                ['type' => 'agent', 'author' => 'James O.', 'time' => '09:25', 'body' => __('Let\'s Encrypt challenge failed. Initiating manual cert upload.')],
                ['type' => 'internal', 'author' => 'James O.', 'time' => '09:40', 'body' => __('INC-2398 opened. 4 tenants on shared cert bundle.')],
            ],
        ];

        foreach ($tickets as $ticket) {
            $specific[$ticket['id']] ??= $defaultThread;
        }

        return $specific;
    }

    /**
     * @return array<int, float>
     */
    private function spark(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
