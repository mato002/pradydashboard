<?php

namespace App\Domain\Tenancy\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findForCommandCenter(int $id): Tenant
    {
        return Tenant::query()
            ->whereKey($id)
            ->with([
                'project' => function ($query) {
                    $query->with([
                        'server',
                        'deployments' => fn ($q) => $q->latest('deployed_at')->limit(20),
                    ]);
                },
                'server',
                'subscriptions' => fn ($q) => $q->latest()->limit(5),
                'invoices' => fn ($q) => $q->latest()->limit(10),
                'payments' => fn ($q) => $q->latest()->limit(10),
                'accessControls' => fn ($q) => $q->latest()->limit(10),
                'latestAccessControl',
                'licenseModules',
                'usageMetric',
                'activityLogs' => fn ($q) => $q->limit(30),
                'reportedUsers' => fn ($q) => $q->latest('last_seen_at')->limit(50),
                'alerts' => fn ($q) => $q->whereNull('dismissed_at')->latest()->limit(10),
                'supportTickets' => fn ($q) => $q->with(['assignedStaff', 'project'])->latest('opened_at')->limit(30),
                'communications' => fn ($q) => $q->with(['staffProfile', 'relatedTicket'])->latest('communication_date')->limit(30),
                'notices' => fn ($q) => $q->latest()->limit(20),
                'projectSubscriptions' => fn ($q) => $q->with([
                    'project.server',
                    'project.modules',
                    'infrastructure.server',
                    'versionTracking',
                    'serviceIntegrations',
                    'moduleSubscriptions.projectModule',
                ]),
                'operationalDocuments' => fn ($q) => $q->with(['uploader', 'subscription.project', 'project'])->latest(),
            ])
            ->firstOrFail();
    }

    public function recentForDashboard(int $limit = 6): Collection
    {
        return Tenant::query()
            ->with('project')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
