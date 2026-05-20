<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Hr\HrOverview;
use App\Domain\Support\SupportOperationsSummary;
use App\Domain\Projects\ProjectOperationsService;
use App\Domain\Tenancy\ProjectOperationalInsights;
use App\Domain\Tenancy\ProjectVersionRolloutSummary;
use App\Support\IntegrationServiceOptions;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Server;
use App\Support\ProjectFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectOperationsService $operations,
        private readonly ProjectVersionRolloutSummary $rolloutSummary,
        private readonly ProjectOperationalInsights $operationalInsights,
        private readonly HrOverview $hrOverview,
        private readonly SupportOperationsSummary $supportSummary,
        private readonly ActivityLogQuery $activityQuery,
    ) {}

    public function index(Request $request): View
    {
        $scopeFilter = app(\App\Domain\Rbac\RbacScopeFilter::class);

        $query = $scopeFilter
            ->applyProjectScope(Project::query())
            ->with('server')
            ->withCount('tenants')
            ->orderBy('name');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        if ($env = $request->query('environment')) {
            $query->where('status', $env === 'production' ? 'active' : 'maintenance');
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $allProjects = $scopeFilter->applyProjectScope(Project::query())->with('server')->withCount('tenants')->get();
        $projects = $query->paginate(12)->withQueryString();

        $enrichedRows = $projects->getCollection()->map(function (Project $project): array {
            return array_merge(
                ['project' => $project],
                $this->operations->enrich($project)
            );
        });

        return view('admin.projects.index', [
            'projects' => $projects,
            'enrichedRows' => $enrichedRows,
            'kpis' => $this->operations->kpis($allProjects),
            'recentDeployments' => $this->operations->recentDeployments($allProjects),
            'infrastructure' => $this->operations->infrastructureMap($allProjects),
            'filters' => [
                'q' => $request->query('q'),
                'environment' => $request->query('environment'),
                'status' => $request->query('status'),
            ],
            'spark' => fn (string $key) => $this->operations->sparkline($key),
        ]);
    }

    public function create(): View
    {
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.projects.create', [
            'project' => new Project,
            'servers' => $servers,
            'formOptions' => ProjectFormOptions::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Project::query()->create($data);

        return redirect()->route('projects.index')->with('status', 'Hosted project created.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'server.latestHealthLog',
            'tenants',
            'modules',
            'versions' => fn ($q) => $q->orderByDesc('release_date'),
            'tenantProjectSubscriptions.tenant',
            'tenantProjectSubscriptions.versionTracking',
            'deployments' => fn ($q) => $q->orderByDesc('deployed_at')->limit(10),
        ]);

        $meta = $this->operations->enrich($project);
        $mrr = (float) $project->tenantProjectSubscriptions->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));
        $rolloutSummary = $this->rolloutSummary->forProject($project);
        $missingContracts = $this->operationalInsights->tenantsMissingRequiredContracts($project);
        $integrationSummary = $this->operationalInsights->integrationsByServiceType($project);
        $integrationServiceLabels = IntegrationServiceOptions::serviceTypes();

        return view('admin.projects.show', [
            'project' => $project,
            'meta' => $meta,
            'mrr' => $mrr,
            'rolloutSummary' => $rolloutSummary,
            'missingContracts' => $missingContracts,
            'integrationSummary' => $integrationSummary,
            'integrationServiceLabels' => $integrationServiceLabels,
            'pipeline' => $this->operations->pipelineStages($project),
            'buildLogs' => $this->operations->buildLogs($project),
            'envVars' => $this->operations->environmentVariables($project),
            'deploymentHistory' => $this->operations->recentDeployments(collect([$project]), 10),
            'staffAssignments' => $this->hrOverview->assignmentsFor($project),
            'supportSummary' => $this->supportSummary->forProject($project->id),
            'activityLogs' => $this->activityQuery->forContext(projectId: $project->id),
            'operationalRisks' => app(\App\Domain\Operations\OperationalRiskScanner::class)->forProject($project->id),
        ]);
    }

    public function edit(Project $project): View
    {
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.projects.edit', [
            'project' => $project,
            'servers' => $servers,
            'formOptions' => ProjectFormOptions::all(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request);
        $project->update($data);

        return redirect()->route('projects.show', $project)->with('status', 'Hosted project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Hosted project removed.');
    }

    public function regenerateToken(Project $project): RedirectResponse
    {
        $project->update(['api_token' => Str::random(64)]);

        return back()->with('status', 'API token regenerated. Update all tenant systems with the new token.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $request->merge([
            'product_slug' => filled(trim((string) $request->input('product_slug', '')))
                ? strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace('-', '_', trim((string) $request->input('product_slug')))))
                : null,
            'system_code' => filled(trim((string) $request->input('system_code', '')))
                ? strtolower(preg_replace('/[^a-z0-9\-]/', '', str_replace('_', '-', trim((string) $request->input('system_code')))))
                : null,
        ]);

        foreach ([
            'kill_switch_allowed',
            'offline_mode_allowed',
            'contract_document_required',
            'requires_server',
            'requires_domain',
            'requires_ssl',
            'requires_whm',
            'default_database_required',
            'backup_required',
        ] as $booleanField) {
            $request->merge([$booleanField => $request->boolean($booleanField)]);
        }

        $data = $request->validate([
            'server_id' => ['nullable', 'exists:servers,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'product_slug' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('projects', 'product_slug')->ignore($request->route('project')?->getKey()),
            ],
            'system_code' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('projects', 'system_code')->ignore($request->route('project')?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'owner_department' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
            'technology_stack' => ['nullable', 'string'],
            'git_repository' => ['nullable', 'string', 'max:500'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(ProjectFormOptions::status()))],
            'version' => ['nullable', 'string', 'max:100'],
            'min_supported_version' => ['nullable', 'string', 'max:100'],
            'latest_release_date' => ['nullable', 'date'],
            'business_model' => ['nullable', Rule::in(array_keys(ProjectFormOptions::businessModel()))],
            'deployment_type' => ['nullable', Rule::in(array_keys(ProjectFormOptions::deploymentType()))],
            'billing_model' => ['nullable', Rule::in(array_keys(ProjectFormOptions::billingModel()))],
            'default_setup_fee' => ['nullable', 'numeric', 'min:0'],
            'default_monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(array_keys(ProjectFormOptions::currency()))],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'minimum_contract_term' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'license_validation_mode' => ['required', Rule::in(array_keys(ProjectFormOptions::licenseValidationMode()))],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:365'],
            'kill_switch_allowed' => ['boolean'],
            'offline_mode_allowed' => ['boolean'],
            'contract_document_required' => ['boolean'],
            'requires_server' => ['boolean'],
            'requires_domain' => ['boolean'],
            'requires_ssl' => ['boolean'],
            'requires_whm' => ['boolean'],
            'default_disk_quota_mb' => ['nullable', 'integer', 'min:0'],
            'default_database_required' => ['boolean'],
            'backup_required' => ['boolean'],
            'monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'monthly_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        return $data;
    }
}
