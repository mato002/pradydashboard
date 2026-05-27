<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Projects\ProjectOperationsService;
use App\Http\Controllers\Concerns\ExportsAdminListing;
use App\Http\Controllers\Controller;
use App\Models\HostedProject;
use App\Models\Product;
use App\Models\Server;
use App\Support\Admin\HostedProjectIntegrationKit;
use App\Support\Admin\ListingFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HostedProjectController extends Controller
{
    use ExportsAdminListing;

    public function __construct(
        private readonly ProjectOperationsService $operations
    ) {}

    public function index(Request $request): View
    {
        $query = $this->listingQuery($request);
        $allHosted = HostedProject::query()->with(['server', 'product'])->withCount('tenants')->get();
        $hostedProjects = $query->paginate(12)->withQueryString();

        $enrichedRows = $hostedProjects->getCollection()->map(fn (HostedProject $hp) => array_merge(
            [
                'project' => $hp,
                'hostedProject' => $hp,
            ],
            $this->operations->enrich($hp),
        ));

        return view('admin.hosted-projects.index', [
            'hostedProjects' => $hostedProjects,
            'enrichedRows' => $enrichedRows,
            'kpis' => $this->operations->kpis($allHosted),
            'recentDeployments' => $this->operations->recentDeployments($allHosted),
            'infrastructure' => $this->operations->infrastructureMap($allHosted),
            'filters' => ListingFilters::fromRequest($request, ['q', 'environment', 'status', 'product_id']),
            'exportQuery' => ListingFilters::queryExceptPage($request),
            'productOptions' => Product::query()->orderBy('name')->pluck('name', 'id'),
            'spark' => fn (string $key) => $this->operations->sparkline($key),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->listingQuery($request)->get()->map(fn (HostedProject $hp) => [
            $hp->name,
            $hp->product?->name,
            $hp->domain,
            $hp->environment,
            $hp->status,
            $hp->server?->name,
            $hp->tenants_count,
        ]);

        return $this->exportCsv(
            'hosted-projects-'.now()->format('Y-m-d-His').'.csv',
            ['Name', 'Product', 'Domain', 'Environment', 'Status', 'Server', 'Tenants'],
            $rows,
        );
    }

    public function create(Request $request): View
    {
        return view('admin.hosted-projects.create', [
            'hostedProject' => new HostedProject([
                'product_id' => $request->query('product_id'),
            ]),
            'products' => Product::query()->where('status', 'active')->orderBy('name')->get(),
            'servers' => Server::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HostedProject::query()->create($this->validated($request));

        return redirect()->route('hosted-projects.index')->with('status', __('Hosted project created.'));
    }

    public function show(HostedProject $hostedProject): View
    {
        $hostedProject->load([
            'product',
            'server.latestHealthLog',
            'tenants',
            'deployments' => fn ($q) => $q->orderByDesc('deployed_at')->limit(10),
        ]);

        return view('admin.hosted-projects.show', [
            'hostedProject' => $hostedProject,
            'project' => $hostedProject,
            'meta' => $this->operations->enrich($hostedProject),
            'pipeline' => $this->operations->pipelineStages($hostedProject),
            'buildLogs' => $this->operations->buildLogs($hostedProject),
            'envVars' => $this->operations->environmentVariables($hostedProject),
            'deploymentHistory' => $this->operations->recentDeployments(collect([$hostedProject]), 10),
            'integrationKit' => app(HostedProjectIntegrationKit::class)->forShow($hostedProject),
        ]);
    }

    public function edit(HostedProject $hostedProject): View
    {
        return view('admin.hosted-projects.edit', [
            'hostedProject' => $hostedProject,
            'products' => Product::query()->orderBy('name')->get(),
            'servers' => Server::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, HostedProject $hostedProject): RedirectResponse
    {
        $hostedProject->update($this->validated($request, $hostedProject));

        return redirect()->route('hosted-projects.show', $hostedProject)->with('status', __('Hosted project updated.'));
    }

    public function destroy(HostedProject $hostedProject): RedirectResponse
    {
        $hostedProject->delete();

        return redirect()->route('hosted-projects.index')->with('status', __('Hosted project removed.'));
    }

    public function regenerateToken(HostedProject $hostedProject): RedirectResponse
    {
        $hostedProject->update(['api_token' => Str::random(64)]);

        return back()->with('status', __('API token regenerated. Update all tenant systems with the new token.'));
    }

    private function listingQuery(Request $request)
    {
        $query = HostedProject::query()
            ->with(['server', 'product'])
            ->withCount('tenants')
            ->orderBy('domain');

        ListingFilters::applySearch($query, $request->query('q'), [
            'name', 'domain', 'product_key', 'database_name', 'cpanel_username',
        ]);

        if ($env = $request->query('environment')) {
            $query->where('environment', $env);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?HostedProject $hostedProject = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'base_url' => ['nullable', 'url', 'max:500'],
            'environment' => ['required', 'in:production,staging,demo,development'],
            'product_key' => ['nullable', 'string', 'max:80'],
            'stack' => ['nullable', 'string'],
            'git_repository' => ['nullable', 'string', 'max:500'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'cpanel_username' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,maintenance,suspended'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
