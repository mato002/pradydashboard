<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditEventAggregator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogsController extends Controller
{
    public function __construct(
        private readonly AuditEventAggregator $aggregator
    ) {}

    public function index(Request $request): View
    {
        $all = $this->aggregator->collect();
        $filters = $this->filtersFromRequest($request);
        $filtered = $this->aggregator->filter($all, $filters);
        $kpis = $this->aggregator->kpis($all);

        $perPage = 25;
        $page = max(1, (int) $request->query('page', 1));
        $paginated = $filtered->forPage($page, $perPage);
        $totalPages = max(1, (int) ceil($filtered->count() / $perPage));

        $filterOptions = [
            'tenants' => $all->pluck('tenant')->filter()->unique()->sort()->values(),
            'users' => $all->pluck('user')->unique()->sort()->values(),
            'modules' => $all->pluck('module')->unique()->sort()->values(),
            'servers' => $all->pluck('server')->filter()->unique()->sort()->values(),
        ];

        return view('admin.activity-logs.index', [
            'events' => $paginated,
            'liveStream' => $all->take(12),
            'kpis' => $kpis,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'eventTypes' => AuditEventAggregator::EVENT_TYPES,
            'severities' => AuditEventAggregator::SEVERITIES,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalFiltered' => $filtered->count(),
            'heatmap' => $this->aggregator->heatmap($all),
            'timeline' => $this->aggregator->timeline($all),
            'loginBreakdown' => $this->aggregator->breakdown(
                $all->where('event_type', 'Login'),
                'status'
            ),
            'deploymentBreakdown' => $this->aggregator->breakdown(
                $all->where('event_type', 'Deployment'),
                'status'
            ),
            'apiBreakdown' => $this->aggregator->breakdown(
                $all->where('event_type', 'API'),
                'module'
            ),
            'spark' => fn (string $key) => $this->aggregator->sparkline($all, $key),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $all = $this->aggregator->collect();
        $filters = $this->filtersFromRequest($request);
        $filtered = $this->aggregator->filter($all, $filters);

        $filename = 'audit-log-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filtered): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Timestamp',
                'Event Type',
                'User',
                'Tenant',
                'IP Address',
                'Module',
                'Server',
                'Severity',
                'Description',
                'Status',
            ]);

            foreach ($filtered as $event) {
                fputcsv($out, [
                    $event['timestamp']->toIso8601String(),
                    $event['event_type'],
                    $event['user'],
                    $event['tenant'] ?? '',
                    $event['ip'],
                    $event['module'],
                    $event['server'] ?? '',
                    $event['severity'],
                    $event['description'],
                    $event['status'],
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => $request->query('q'),
            'event_type' => $request->query('event_type'),
            'severity' => $request->query('severity'),
            'tenant' => $request->query('tenant'),
            'user' => $request->query('user'),
            'module' => $request->query('module'),
            'server' => $request->query('server'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
    }
}
