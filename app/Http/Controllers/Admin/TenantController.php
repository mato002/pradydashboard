<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Operations\OperationalRiskScanner;
use App\Domain\Billing\BillingSummary;
use App\Domain\Billing\TenantCollectionsQuery;
use App\Domain\Hr\HrOverview;
use App\Domain\Support\SupportOperationsSummary;
use App\Domain\Billing\DraftInvoiceGenerator;
use App\Domain\Tenancy\Repositories\TenantRepositoryInterface;
use App\Domain\Servers\Support\ServerConnectionConfig;
use App\Domain\Tenancy\OperationalDocumentInsights;
use App\Domain\Tenancy\ProjectVersionRolloutSummary;
use App\Domain\Tenancy\TenantCommandCenter;
use App\Domain\Tenancy\TenantProjectModuleMatrix;
use App\Domain\Tenancy\TenantProjectProvisioner;
use App\Domain\Tenancy\TenantSystemApiInsights;
use App\Support\IntegrationServiceOptions;
use App\Support\OperationalDocumentOptions;
use App\Support\ProjectFormOptions;
use App\Support\SupportOpsOptions;
use App\Support\TenantOpsFormOptions;
use App\Models\StaffProfile;
use App\Models\SupportTicket;
use App\Http\Controllers\Controller;
use App\Models\LicenseModule;
use App\Models\Project;
use App\Models\SaasPlan;
use App\Models\Server;
use App\Models\Tenant;
use App\Models\TenantProjectSubscription;
use App\Models\TenantSubscription;
use App\Support\Admin\TenantWorkspaceMetrics;
use App\Support\Admin\PradyWorkspaceRequest;
use App\Support\DemoMode;
use App\Support\TenantOperationsPresenter;
use Database\Seeders\SubscriptionDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    private const TABS = [
        'overview',
        'projects',
        'infrastructure',
        'billing',
        'licensing',
        'modules',
        'integrations',
        'versions',
        'documents',
        'support',
        'communications',
        'notices',
        'users',
        'activity',
        'deployments',
        'monitoring',
        'settings',
    ];

    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly TenantProjectProvisioner $projectProvisioner,
        private readonly TenantCommandCenter $commandCenter,
        private readonly TenantProjectModuleMatrix $moduleMatrix,
        private readonly ProjectVersionRolloutSummary $rolloutSummary,
        private readonly OperationalDocumentInsights $documentInsights,
        private readonly HrOverview $hrOverview,
        private readonly SupportOperationsSummary $supportOps,
        private readonly ActivityLogQuery $activityQuery,
        private readonly TenantSystemApiInsights $tenantSystemApiInsights,
    ) {}

    public function index(TenantOperationsPresenter $operations): View
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = app(\App\Domain\Rbac\RbacScopeFilter::class)
            ->applyTenantScope(Tenant::query())
            ->with(['project', 'server', 'usageMetric', 'projectSubscriptions'])
            ->withCount(['supportTickets', 'invoices'])
            ->orderBy('company_name')
            ->paginate(15);

        return view('admin.tenants.index', $operations->present($tenants));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        if (DemoMode::enabled() && SaasPlan::query()->doesntExist()) {
            (new SubscriptionDemoSeeder)->run();
        }

        $preselectedProjectId = request()->integer('hosted_project_id')
            ?: request()->integer('project_id');

        return view('admin.tenants.create', [
            'tenant' => new Tenant(['hosted_project_id' => $preselectedProjectId ?: null]),
            'preselectedProjectId' => $preselectedProjectId,
            'projects' => Project::query()->orderBy('name')->get(),
            'servers' => Server::query()->orderBy('name')->get(),
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $data = $this->normalizeTenantProjectFields($this->validated($request));
        $saasPlanId = $request->input('saas_plan_id');
        unset($data['saas_plan_id']);

        $tenant = Tenant::query()->create($data);
        $this->syncSubscription($tenant, $saasPlanId, $data);
        $this->projectProvisioner->syncPrimarySubscription($tenant);

        return redirect()->route('tenants.show', $tenant)->with('status', __('Tenant provisioned successfully.'));
    }

    public function show(Request $request, Tenant $tenant): View|Response
    {
        $this->authorize('view', $tenant);

        $tab = $this->normalizeTab((string) $request->query('tab', 'overview'));
        $tenant = $this->tenants->findForCommandCenter($tenant->id);

        $data = array_merge(
            $this->buildShellData($tenant),
            $this->buildWorkspaceData($request, $tenant, $tab),
            ['tab' => $tab],
        );

        if (PradyWorkspaceRequest::isPartial($request)) {
            return response()
                ->view('admin.tenants.partials.workspace.content', $data)
                ->header('X-Tenant-Workspace', 'partial');
        }

        return view('admin.tenants.show', $data);
    }

    /**
     * Persistent shell data (header, KPI groups, risks).
     *
     * @return array<string, mixed>
     */
    private function buildShellData(Tenant $tenant): array
    {
        $billingSummary = app(BillingSummary::class)->forTenant($tenant);
        $lastPayment = $tenant->payments->sortByDesc('paid_at')->first();
        $billingKpi = array_merge($billingSummary, [
            'last_payment' => $lastPayment,
            'subscription_amount' => $tenant->subscription_amount,
        ]);
        $opsSummary = $this->commandCenter->summary($tenant);
        $operationalRisks = app(OperationalRiskScanner::class)->forTenant($tenant->id);
        $metricGroups = TenantWorkspaceMetrics::groups($opsSummary, $billingKpi, $tenant);
        $workspaceTabs = $this->workspaceTabs();

        return compact(
            'tenant',
            'billingKpi',
            'opsSummary',
            'operationalRisks',
            'metricGroups',
            'workspaceTabs',
        );
    }

    /**
     * Tab panel data — loaded only for the active tab.
     *
     * @return array<string, mixed>
     */
    private function buildWorkspaceData(Request $request, Tenant $tenant, string $tab): array
    {
        $subscriptionTabs = ['modules', 'infrastructure', 'versions', 'integrations', 'documents'];
        $selectedSubscription = null;
        $moduleRows = collect();
        $servers = collect();
        $hasRegisteredServers = false;
        $projectVersionContext = ['current' => null, 'latest' => null];
        $inferredVersionStatus = 'unknown';
        $filteredDocuments = $tenant->operationalDocuments;
        $editingIntegration = null;
        $moduleCatalog = collect();
        $projects = collect();
        $billingKpi = [];
        $billableSubscriptions = collect();
        $draftGenerator = null;
        $missingContractWarnings = collect();
        $expiringDocuments = collect();
        $moduleBillingOptions = [];
        $infraFormOptions = [];
        $backupPolicyOptions = [];
        $backupStatusOptions = [];
        $updateStatusOptions = [];
        $documentTypeOptions = [];
        $documentStatusOptions = [];
        $providerServiceTypes = [];
        $integrationStatusOptions = [];
        $integrationCategories = [];
        $tenantSystemPurposes = [];
        $authenticationTypes = [];
        $tenantSystemApiInsights = null;
        $secretPlaceholder = ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER;
        $staffAssignments = collect();
        $supportOps = null;
        $selectedTicket = null;
        $staffList = collect();
        $supportCategories = [];
        $supportPriorities = [];
        $supportStatuses = [];
        $supportSources = [];
        $commentTypes = [];
        $visibilities = [];
        $commChannels = [];
        $commDirections = [];
        $commStatuses = [];
        $noticeTypes = [];
        $noticeSeverities = [];
        $noticeStatuses = [];
        $systemActivityLogs = collect();

        if (in_array($tab, $subscriptionTabs, true) && $tenant->projectSubscriptions->isNotEmpty()) {
            $selectedSubscription = $this->resolveSelectedSubscription($request, $tenant);

            if ($tab === 'modules') {
                $moduleCatalog = LicenseModule::query()->orderBy('sort_order')->get();
                $moduleRows = $this->moduleMatrix->rows($selectedSubscription);
                $moduleBillingOptions = ProjectFormOptions::moduleBillingStatus();
            }

            if ($tab === 'infrastructure') {
                $servers = Server::query()->orderBy('name')->get();
                $hasRegisteredServers = Server::query()->exists();
                $infraFormOptions = TenantOpsFormOptions::sslStatus();
                $backupPolicyOptions = TenantOpsFormOptions::backupPolicy();
                $backupStatusOptions = TenantOpsFormOptions::backupStatus();
            }

            if ($tab === 'versions') {
                $selectedSubscription->loadMissing(['project.versions', 'versionTracking']);
                $project = $selectedSubscription->project;
                $projectVersionContext = [
                    'current' => $this->rolloutSummary->projectCurrentVersion($project),
                    'latest' => $this->rolloutSummary->projectLatestVersion($project),
                ];
                $inferredVersionStatus = $this->rolloutSummary->resolveSubscriptionStatus($selectedSubscription);
                $updateStatusOptions = TenantOpsFormOptions::updateStatus();
            }

            if ($tab === 'integrations') {
                $providerServiceTypes = IntegrationServiceOptions::providerServiceTypes();
                $integrationStatusOptions = IntegrationServiceOptions::statuses();
                $integrationCategories = IntegrationServiceOptions::integrationCategories();
                $tenantSystemPurposes = IntegrationServiceOptions::tenantSystemPurposes();
                $authenticationTypes = IntegrationServiceOptions::authenticationTypes();
                $tenantSystemApiInsights = $this->tenantSystemApiInsights->forSubscription($selectedSubscription);
                if ($request->filled('integration')) {
                    $editingIntegration = $selectedSubscription->serviceIntegrations
                        ->firstWhere('id', (int) $request->query('integration'));
                }
            }

            if ($tab === 'documents') {
                if ($request->filled('subscription')) {
                    $filteredDocuments = $tenant->operationalDocuments
                        ->where('tenant_project_subscription_id', (int) $request->query('subscription'));
                }
                $missingContractWarnings = $this->documentInsights->missingRequiredContracts($tenant);
                $expiringDocuments = $this->documentInsights->expiring($tenant->operationalDocuments);
                $documentTypeOptions = OperationalDocumentOptions::documentTypes();
                $documentStatusOptions = OperationalDocumentOptions::statuses();
            }
        }

        if ($tab === 'overview') {
            $staffAssignments = $this->hrOverview->assignmentsFor($tenant);
        }

        if ($tab === 'projects') {
            $projects = Project::query()->orderBy('name')->get();
        }

        if ($tab === 'billing') {
            $billingSummary = app(BillingSummary::class)->forTenant($tenant);
            $lastPayment = $tenant->payments->sortByDesc('paid_at')->first();
            $billingKpi = array_merge($billingSummary, [
                'last_payment' => $lastPayment,
                'subscription_amount' => $tenant->subscription_amount,
            ]);
            $draftGenerator = app(DraftInvoiceGenerator::class);
            $billableSubscriptions = $tenant->projectSubscriptions->filter(
                fn ($s) => $draftGenerator->isBillableSubscription($s)
            );
        }

        if (in_array($tab, ['support', 'communications', 'notices'], true)) {
            $supportOps = $this->supportOps->forTenant($tenant);
            $staffList = StaffProfile::query()->where('status', 'active')->orderBy('full_name')->pluck('full_name', 'id');
            $supportCategories = SupportOpsOptions::categories();
            $supportPriorities = SupportOpsOptions::priorities();
            $supportStatuses = SupportOpsOptions::ticketStatuses();
            $supportSources = SupportOpsOptions::sources();
            $commentTypes = SupportOpsOptions::commentTypes();
            $visibilities = SupportOpsOptions::visibilities();
            $commChannels = SupportOpsOptions::channels();
            $commDirections = SupportOpsOptions::directions();
            $commStatuses = SupportOpsOptions::communicationStatuses();
            $noticeTypes = SupportOpsOptions::noticeTypes();
            $noticeSeverities = SupportOpsOptions::severities();
            $noticeStatuses = SupportOpsOptions::noticeStatuses();

            if ($tab === 'support' && $request->filled('ticket')) {
                $selectedTicket = SupportTicket::query()
                    ->with(['comments.staffProfile', 'comments.user', 'assignedStaff', 'project'])
                    ->where('tenant_id', $tenant->id)
                    ->find($request->query('ticket'));
            }
        }

        if ($tab === 'activity') {
            $systemActivityLogs = $this->activityQuery->forContext(tenantId: $tenant->id, limit: 50);
        }

        return compact(
            'moduleCatalog',
            'billingKpi',
            'billableSubscriptions',
            'draftGenerator',
            'projects',
            'selectedSubscription',
            'moduleRows',
            'moduleBillingOptions',
            'servers',
            'hasRegisteredServers',
            'projectVersionContext',
            'infraFormOptions',
            'backupPolicyOptions',
            'backupStatusOptions',
            'updateStatusOptions',
            'inferredVersionStatus',
            'filteredDocuments',
            'missingContractWarnings',
            'expiringDocuments',
            'documentTypeOptions',
            'documentStatusOptions',
            'providerServiceTypes',
            'integrationStatusOptions',
            'integrationCategories',
            'tenantSystemPurposes',
            'authenticationTypes',
            'tenantSystemApiInsights',
            'secretPlaceholder',
            'editingIntegration',
            'staffAssignments',
            'supportOps',
            'selectedTicket',
            'staffList',
            'supportCategories',
            'supportPriorities',
            'supportStatuses',
            'supportSources',
            'commentTypes',
            'visibilities',
            'commChannels',
            'commDirections',
            'commStatuses',
            'noticeTypes',
            'noticeSeverities',
            'noticeStatuses',
            'systemActivityLogs',
        );
    }

    /**
     * @return array<string, string>
     */
    private function workspaceTabs(): array
    {
        return [
            'overview' => __('Overview'),
            'projects' => __('Projects'),
            'infrastructure' => __('Infrastructure'),
            'billing' => __('Billing'),
            'licensing' => __('Licensing'),
            'modules' => __('Modules'),
            'integrations' => __('Integrations'),
            'versions' => __('Versions'),
            'documents' => __('Documents'),
            'support' => __('Support'),
            'communications' => __('Communications'),
            'notices' => __('Notices'),
            'users' => __('Users'),
            'activity' => __('Activity'),
            'deployments' => __('Deployments'),
            'monitoring' => __('Monitoring'),
            'settings' => __('Settings'),
        ];
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        $projects = Project::query()->orderBy('name')->get();
        $servers = Server::query()->orderBy('name')->get();

        if (DemoMode::enabled() && SaasPlan::query()->doesntExist()) {
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

        $data = $this->normalizeTenantProjectFields($this->validated($request));
        $tenant->update($data);
        $this->projectProvisioner->syncPrimarySubscription($tenant->fresh());

        $returnTab = $this->normalizeTab((string) $request->input('return_tab', 'overview'));

        return redirect()->to(route('tenants.show', $tenant).'?tab='.urlencode($returnTab))
            ->with('status', __('Tenant updated.'));
    }

    public function updateStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $data = $request->validate([
            'status' => ['required', 'in:prospect,onboarding,active,trial,warning,restricted,suspended,overdue,cancelled,terminated'],
        ]);

        $tenant->update(['status' => $data['status']]);
        $tenant->refresh()->loadMissing('project.product');

        if ($tenant->hosted_project_id && ($tenant->project?->product_id ?? $tenant->product_id)) {
            $this->projectProvisioner->syncPrimarySubscription($tenant);
        }

        return redirect()
            ->back()
            ->with('status', __('Tenant status updated to :status.', ['status' => str_replace('_', ' ', $data['status'])]));
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

    private function resolveSelectedSubscription(Request $request, Tenant $tenant): TenantProjectSubscription
    {
        $subscriptionId = (int) $request->query('subscription', $tenant->projectSubscriptions->first()->id);

        return $tenant->projectSubscriptions->firstWhere('id', $subscriptionId)
            ?? $tenant->projectSubscriptions->first();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeTenantProjectFields(array $data): array
    {
        if (isset($data['project_id'])) {
            $hostedProject = Project::query()->find($data['project_id']);
            $data['hosted_project_id'] = $data['project_id'];
            $data['product_id'] = $hostedProject?->product_id;
            unset($data['project_id']);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:hosted_projects,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'tenant_code' => ['nullable', 'string', 'max:80', Rule::unique('tenants', 'tenant_code')->ignore($request->route('tenant'))],
            'tenant_key' => ['nullable', 'string', 'max:120', Rule::unique('tenants', 'tenant_key')->ignore($request->route('tenant'))],
            'industry' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'county_city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'primary_contact_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
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
            'status' => ['required', 'in:prospect,onboarding,active,trial,warning,restricted,suspended,overdue,cancelled,terminated'],
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

        $hostedProject = Project::query()->with('product')->find($tenant->hosted_project_id);

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan?->id,
            'plan_name' => $tenant->subscription_plan ?? $plan?->name ?? 'Custom',
            'product_name' => $hostedProject?->product?->name ?? $hostedProject?->name,
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
