<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\LicenseModule;
use App\Models\Project;
use App\Models\Server;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    private const TABS = [
        'overview',
        'billing',
        'infrastructure',
        'modules',
        'users',
        'activity',
        'support',
        'deployments',
        'monitoring',
        'settings',
    ];

    public function __construct(
        private readonly TenantRepositoryInterface $tenants
    ) {
        $this->authorizeResource(Tenant::class, 'tenant', [
            'except' => ['index', 'create', 'store'],
        ]);
    }

    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::query()
            ->with(['project', 'server'])
            ->orderBy('company_name')
            ->paginate(15);

        $kpis = [
            'total' => Tenant::query()->count(),
            'active' => Tenant::query()->where('status', 'active')->count(),
            'trial' => Tenant::query()->where('status', 'trial')->count(),
            'overdue' => Tenant::query()->where('status', 'overdue')->count(),
            'suspended' => Tenant::query()->whereIn('status', ['suspended', 'restricted', 'terminated'])->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'kpis'));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        $projects = Project::query()->orderBy('name')->get();
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.tenants.create', [
            'tenant' => new Tenant,
            'projects' => $projects,
            'servers' => $servers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $data = $this->validated($request);
        Tenant::query()->create($data);

        return redirect()->route('tenants.index')->with('status', __('Tenant created.'));
    }

    public function show(Request $request, Tenant $tenant): View
    {
        $tab = $this->normalizeTab((string) $request->query('tab', 'overview'));

        $tenant = $this->tenants->findForCommandCenter($tenant->id);

        $moduleCatalog = LicenseModule::query()->orderBy('sort_order')->get();

        $billingOutstanding = (float) $tenant->invoices
            ->whereNotIn('status', ['void', 'paid'])
            ->sum(fn ($invoice): float => max(0,
                (float) $invoice->amount_due - (float) $invoice->amount_paid + (float) $invoice->penalty_amount
            ));

        $lastPayment = $tenant->payments->sortByDesc('paid_at')->first();

        $billingKpi = [
            'outstanding' => $billingOutstanding,
            'currency' => $tenant->tenant_currency ?? 'KES',
            'last_payment' => $lastPayment,
            'subscription_amount' => $tenant->subscription_amount,
        ];

        return view('admin.tenants.show', compact(
            'tenant',
            'tab',
            'moduleCatalog',
            'billingKpi'
        ));
    }

    public function edit(Tenant $tenant): View
    {
        $projects = Project::query()->orderBy('name')->get();
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.tenants.edit', compact('tenant', 'projects', 'servers'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $this->validated($request);
        $tenant->update($data);

        $returnTab = $this->normalizeTab((string) $request->input('return_tab', 'overview'));

        return redirect()->to(route('tenants.show', $tenant).'?tab='.urlencode($returnTab))
            ->with('status', __('Tenant updated.'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('tenants.index')->with('status', __('Tenant removed.'));
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'overview';
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:64'],
            'physical_address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'size:2'],
            'logo_path' => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'subscription_plan' => ['nullable', 'string', 'max:255'],
            'subscription_amount' => ['nullable', 'numeric', 'min:0'],
            'tenant_currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'start_date' => ['nullable', 'date'],
            'renewal_date' => ['nullable', 'date'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'status' => ['required', 'in:active,trial,warning,restricted,suspended,overdue,cancelled,terminated'],
            'cpanel_account_ref' => ['nullable', 'string', 'max:255'],
            'database_ref' => ['nullable', 'string', 'max:255'],
            'login_url' => ['nullable', 'string', 'max:500'],
            'tenant_domain' => ['nullable', 'string', 'max:255'],
            'deployment_version' => ['nullable', 'string', 'max:100'],
            'penalties_total' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
