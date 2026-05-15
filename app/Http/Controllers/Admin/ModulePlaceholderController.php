<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModulePlaceholderController extends Controller
{
    /** @var array<string, array{title: string, phase: string, blurb: string}> */
    private const SECTIONS = [
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
