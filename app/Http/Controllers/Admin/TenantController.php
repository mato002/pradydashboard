<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\LicenseModule;
use App\Models\Project;
use App\Models\SaasPlan;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Support\TenantOperationsPresenter;
use Database\Seeders\SubscriptionDemoSeeder;
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
    ) {}

    public function index(TenantOperationsPresenter $operations): View
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::query()
            ->with(['project', 'server', 'usageMetric'])
            ->withCount(['supportTickets', 'invoices'])
            ->orderBy('company_name')
            ->paginate(15);

        return view('admin.tenants.index', $operations->present($tenants));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        if (SaasPlan::query()->doesntExist()) {
            (new SubscriptionDemoSeeder)->run();
        }

        return view('admin.tenants.create', [
            'tenant' => new Tenant,
            'projects' => Project::query()->orderBy('name')->get(),
            'servers' => Server::query()->orderBy('name')->get(),
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $data = $this->validated($request);
        $saasPlanId = $request->input('saas_plan_id');
        unset($data['saas_plan_id']);

        $tenant = Tenant::query()->create($data);
        $this->syncSubscription($tenant, $saasPlanId, $data);

        return redirect()->route('tenants.show', $tenant)->with('status', __('Tenant provisioned successfully.'));
    }

    public function show(Request $request, Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

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
        $this->authorize('update', $tenant);

        $projects = Project::query()->orderBy('name')->get();
        $servers = Server::query()->orderBy('name')->get();

        if (SaasPlan::query()->doesntExist()) {
            (new SubscriptionDemoSeeder)->run();
        }

        return view('admin.tenants.edit', [
            'tenant' => $tenant,
            'projects' => $projects,
            'servers' => $servers,
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $data = $this->validated($request);
        $tenant->update($data);

        $returnTab = $this->normalizeTab((string) $request->input('return_tab', 'overview'));

        return redirect()->to(route('tenants.show', $tenant).'?tab='.urlencode($returnTab))
            ->with('status', __('Tenant updated.'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

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
            'saas_plan_id' => ['nullable', 'exists:saas_plans,id'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncSubscription(Tenant $tenant, mixed $saasPlanId, array $data): void
    {
        $plan = $saasPlanId ? SaasPlan::query()->find($saasPlanId) : null;
        $cycle = $data['billing_cycle'] ?? 'monthly';
        $amount = (float) ($data['subscription_amount'] ?? ($plan ? (float) $plan->monthly_price : 0));

        if ($plan && empty($data['subscription_plan'])) {
            $tenant->update(['subscription_plan' => $plan->name]);
        }

        $status = match ($tenant->status) {
            'trial' => 'trial',
            'overdue' => 'overdue',
            'suspended', 'restricted', 'terminated', 'cancelled' => 'suspended',
            default => 'active',
        };

        $project = Project::query()->find($tenant->project_id);

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan?->id,
            'plan_name' => $tenant->subscription_plan ?? $plan?->name ?? 'Custom',
            'product_name' => $project?->name,
            'amount' => $cycle === 'annual' && $plan?->annual_price
                ? (float) $plan->annual_price
                : $amount,
            'billing_cycle' => $cycle,
            'current_period_start' => $data['start_date'] ?? now(),
            'current_period_end' => $data['renewal_date'] ?? now()->addMonth(),
            'status' => $status,
            'auto_renew' => ! in_array($status, ['suspended', 'cancelled'], true),
        ]);
    }
}
