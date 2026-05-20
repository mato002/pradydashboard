<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use App\Support\ActivityLogCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogsController extends Controller
{
    public function __construct(
        private readonly ActivityLogQuery $query,
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->query->filtersFromRequest($request);
        $logs = $this->query->paginate($filters);

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'categories' => ActivityLogCategory::labels(),
            'tenants' => Tenant::query()->orderBy('company_name')->limit(200)->pluck('company_name', 'id'),
            'projects' => Project::query()->orderBy('name')->limit(200)->pluck('name', 'id'),
            'servers' => Server::query()->orderBy('name')->limit(200)->pluck('name', 'id'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->query->filtersFromRequest($request);
        $base = \App\Models\SystemActivityLog::query()->orderByDesc('created_at');
        $base = app(\App\Domain\Rbac\RbacScopeFilter::class)->applyActivityLogScope($base);
        $rows = $this->query->apply($base, $filters)->limit(5000)->get();

        $filename = 'activity-log-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Timestamp',
                'Actor',
                'Action',
                'Category',
                'Description',
                'Tenant ID',
                'Project ID',
                'Server ID',
            ]);

            foreach ($rows as $log) {
                fputcsv($out, [
                    $log->created_at?->toIso8601String(),
                    $log->actorDisplayName(),
                    $log->action,
                    $log->category,
                    $log->description,
                    $log->tenant_id,
                    $log->project_id,
                    $log->server_id,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
