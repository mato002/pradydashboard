<?php

namespace App\Domain\Support;

use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantCommunication;
use App\Models\TenantNotice;
use App\Support\SupportOpsOptions;
use Illuminate\Support\Collection;

class SupportOperationsSummary
{
    /**
     * @return array<string, mixed>
     */
    public function platform(): array
    {
        $openStatuses = SupportOpsOptions::openTicketStatuses();

        $openTickets = SupportTicket::query()->whereIn('status', $openStatuses)->count();
        $urgentTickets = SupportTicket::query()
            ->whereIn('status', $openStatuses)
            ->whereIn('priority', ['high', 'urgent', 'critical'])
            ->count();
        $overdueTickets = SupportTicket::query()
            ->whereIn('status', $openStatuses)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        $overdueFollowUps = TenantCommunication::query()
            ->where('follow_up_required', true)
            ->where('status', 'pending_follow_up')
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', now()->toDateString())
            ->count();

        $tenantsWithOpenIssues = Tenant::query()
            ->whereHas('supportTickets', fn ($q) => $q->whereIn('status', $openStatuses))
            ->count();

        $recentCommunications = TenantCommunication::query()
            ->with(['tenant:id,company_name', 'staffProfile:id,full_name'])
            ->orderByDesc('communication_date')
            ->limit(8)
            ->get();

        return [
            'open_tickets' => $openTickets,
            'urgent_tickets' => $urgentTickets,
            'overdue_tickets' => $overdueTickets,
            'overdue_follow_ups' => $overdueFollowUps,
            'tenants_with_open_issues' => $tenantsWithOpenIssues,
            'recent_communications' => $recentCommunications,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $openStatuses = SupportOpsOptions::openTicketStatuses();

        $tickets = $tenant->relationLoaded('supportTickets')
            ? $tenant->supportTickets
            : $tenant->supportTickets()->with(['assignedStaff', 'project'])->get();

        $open = $tickets->whereIn('status', $openStatuses);
        $urgent = $open->filter(fn (SupportTicket $t) => $t->isUrgent());
        $overdue = $open->filter(fn (SupportTicket $t) => $t->isOverdue());

        $communications = $tenant->relationLoaded('communications')
            ? $tenant->communications
            : $tenant->communications()->with(['staffProfile', 'relatedTicket'])->latest('communication_date')->get();

        $notices = $tenant->relationLoaded('notices')
            ? $tenant->notices
            : $tenant->notices()->latest()->limit(15)->get();

        $pendingFollowUps = $communications->filter(
            fn (TenantCommunication $c) => $c->follow_up_required && $c->status === 'pending_follow_up'
        );

        return [
            'open_tickets' => $open,
            'urgent_tickets' => $urgent,
            'overdue_tickets' => $overdue,
            'recent_tickets' => $tickets->sortByDesc('opened_at')->take(15),
            'recent_communications' => $communications->take(15),
            'pending_follow_ups' => $pendingFollowUps,
            'recent_notices' => $notices,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forProject(int $projectId): array
    {
        $openStatuses = SupportOpsOptions::openTicketStatuses();

        $openTickets = SupportTicket::query()
            ->with(['tenant:id,company_name', 'assignedStaff'])
            ->where('hosted_project_id', $projectId)
            ->whereIn('status', $openStatuses)
            ->orderByDesc('opened_at')
            ->get();

        $tenantIds = $openTickets->pluck('tenant_id')->filter()->unique();

        return [
            'open_tickets' => $openTickets,
            'tenants_with_issues' => $tenantIds->count(),
            'tenant_ids' => $tenantIds,
        ];
    }

    /**
     * @return Collection<int, SupportTicket>
     */
    public function forStaff(int $staffId): Collection
    {
        return SupportTicket::query()
            ->with(['tenant:id,company_name', 'project:id,name'])
            ->where('assigned_staff_id', $staffId)
            ->whereIn('status', SupportOpsOptions::openTicketStatuses())
            ->orderByDesc('opened_at')
            ->get();
    }

    /**
     * @return Collection<int, TenantCommunication>
     */
    public function followUpsForStaff(int $staffId): Collection
    {
        return TenantCommunication::query()
            ->with('tenant:id,company_name')
            ->where('staff_profile_id', $staffId)
            ->where('follow_up_required', true)
            ->where('status', 'pending_follow_up')
            ->orderBy('follow_up_date')
            ->get();
    }
}
