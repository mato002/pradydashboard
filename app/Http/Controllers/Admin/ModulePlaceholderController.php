<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModulePlaceholderController extends Controller
{
    /** @var array<string, array{title: string, phase: string, blurb: string}> */
    private const SECTIONS = [
        'subscriptions' => [
            'title' => 'Subscriptions',
            'phase' => 'Phase 2',
            'blurb' => 'Tenant subscription plans, billing cycles, renewals, and plan changes.',
        ],
        'invoices' => [
            'title' => 'Invoices',
            'phase' => 'Phase 2',
            'blurb' => 'Invoice generation, numbering, due dates, and PDF delivery.',
        ],
        'payments' => [
            'title' => 'Payments',
            'phase' => 'Phase 2',
            'blurb' => 'Record payments, receipts, allocations to invoices, and balances.',
        ],
        'access-controls' => [
            'title' => 'Access Controls',
            'phase' => 'Phase 3',
            'blurb' => 'Grace periods, restriction levels, module disables, and automated enforcement.',
        ],
        'server-health' => [
            'title' => 'Server Health',
            'phase' => 'Phase 4',
            'blurb' => 'CPU, RAM, disk, uptime checks, and historical charts.',
        ],
        'backups' => [
            'title' => 'Backups',
            'phase' => 'Phase 4',
            'blurb' => 'Backup schedules, last run status, and restore drill tracking.',
        ],
        'ssl-domains' => [
            'title' => 'SSL & Domains',
            'phase' => 'Phase 4',
            'blurb' => 'Certificate expiry, domain inventory, and renewal alerts.',
        ],
        'deployments' => [
            'title' => 'Deployments',
            'phase' => 'Phase 4',
            'blurb' => 'Version history, deployment notes, and rollback references.',
        ],
        'monitoring' => [
            'title' => 'Monitoring',
            'phase' => 'Phase 4',
            'blurb' => 'Synthetic checks, error budgets, and escalation policies.',
        ],
        'activity-logs' => [
            'title' => 'Activity Logs',
            'phase' => 'Phase 5',
            'blurb' => 'Cross-tenant audit trail, exports, and retention policies.',
        ],
        'support-tickets' => [
            'title' => 'Support Tickets',
            'phase' => 'Phase 5',
            'blurb' => 'Tenant issues, SLAs, assignments, and resolution history.',
        ],
        'users-roles' => [
            'title' => 'Users & Roles',
            'phase' => 'Phase 5',
            'blurb' => 'Dashboard staff accounts, RBAC, and API token governance.',
        ],
        'system-settings' => [
            'title' => 'System Settings',
            'phase' => 'Phase 5',
            'blurb' => 'Mailers, SMS gateways, tax defaults, and regionalisation.',
        ],
        'api-credentials' => [
            'title' => 'API Credentials',
            'phase' => 'Phase 5',
            'blurb' => 'Rotate project tokens, webhook secrets, and IP allow lists.',
        ],
        'reports' => [
            'title' => 'Reports',
            'phase' => 'Phase 5',
            'blurb' => 'Profitability, expiring subscriptions, overdue tenants, and product revenue.',
        ],
        'settings' => [
            'title' => 'Settings',
            'phase' => 'Phase 5',
            'blurb' => 'Organization defaults, notification channels, tax, and integrations.',
        ],
    ];

    public function __invoke(string $section): View
    {
        $meta = self::SECTIONS[$section] ?? null;

        if ($meta === null) {
            abort(404);
        }

        return view('admin.placeholder', [
            'title' => $meta['title'],
            'phase' => $meta['phase'],
            'blurb' => $meta['blurb'],
        ]);
    }
}
