<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Operations\OperationalRiskScanner;
use App\Http\Controllers\Controller;
use App\Models\OperationalRiskAcknowledgement;
use App\Models\Project;
use App\Models\Server;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Support\OperationalRiskCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskCenterController extends Controller
{
    public function __construct(
        private readonly OperationalRiskScanner $scanner,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->query('q'),
            'category' => $request->query('category'),
            'severity' => $request->query('severity'),
            'tenant_id' => $request->query('tenant_id'),
            'project_id' => $request->query('project_id'),
            'server_id' => $request->query('server_id'),
            'staff_profile_id' => $request->query('staff_profile_id'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'acknowledged' => $request->query('acknowledged', 'no'),
        ];

        $risks = app(\App\Domain\Rbac\RbacScopeFilter::class)
            ->filterRiskCollection($this->scanner->scan($filters));
        $grouped = $this->scanner->grouped($filters, $risks);

        return view('admin.risk-center.index', [
            'risks' => $risks,
            'grouped' => $grouped,
            'filters' => $filters,
            'categories' => OperationalRiskCategory::labels(),
            'tenants' => Tenant::query()->orderBy('company_name')->limit(200)->pluck('company_name', 'id'),
            'projects' => Project::query()->orderBy('name')->limit(200)->pluck('name', 'id'),
            'servers' => Server::query()->orderBy('name')->limit(200)->pluck('name', 'id'),
            'staff' => StaffProfile::query()->where('status', 'active')->orderBy('full_name')->limit(200)->pluck('full_name', 'id'),
            'counts' => [
                'total' => $risks->count(),
                'critical' => $risks->where('severity', 'critical')->where('acknowledged', false)->count(),
                'warning' => $risks->where('severity', 'warning')->where('acknowledged', false)->count(),
                'acknowledged' => $risks->where('acknowledged', true)->count(),
            ],
        ]);
    }

    public function acknowledge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'risk_key' => ['required', 'string', 'max:191'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        OperationalRiskAcknowledgement::query()->updateOrCreate(
            ['risk_key' => $data['risk_key']],
            [
                'user_id' => $request->user()?->id,
                'notes' => $data['notes'] ?? null,
                'acknowledged_at' => now(),
            ]
        );

        return back()->with('status', __('Risk acknowledged.'));
    }

    public function unacknowledge(string $riskKey): RedirectResponse
    {
        OperationalRiskAcknowledgement::query()->where('risk_key', $riskKey)->delete();

        return back()->with('status', __('Acknowledgement cleared.'));
    }
}
