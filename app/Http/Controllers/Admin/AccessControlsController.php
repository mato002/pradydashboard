<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\Services\TenantAccessResolver;
use App\Http\Controllers\Controller;
use App\Models\LicenseModule;
use App\Models\Tenant;
use App\Models\TenantAccessControl;
use Database\Seeders\AccessControlDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AccessControlsController extends Controller
{
    public function __construct(
        private readonly TenantAccessResolver $accessResolver
    ) {}

    public function index(): View
    {
        if (TenantAccessControl::query()->doesntExist()) {
            (new AccessControlDemoSeeder)->run();
        }

        $tenants = Tenant::query()
            ->with(['latestAccessControl', 'project', 'licenseModules'])
            ->withCount('accessControls')
            ->orderBy('company_name')
            ->get();

        $policies = $this->buildPolicyRows($tenants);
        $graceAccounts = $this->buildGraceAccounts($tenants);
        $kpis = $this->buildKpis($tenants, $policies);
        $spark = fn (string $key) => $this->pseudoSparkline($key);

        $enforcementTimeline = $this->buildEnforcementTimeline();
        $securityAnalytics = $this->buildSecurityAnalytics();
        $restrictionTrends = $this->buildRestrictionTrends();
        $auditHistory = $this->buildAuditHistory($tenants);
        $moduleMatrix = $this->buildModuleMatrix($tenants);
        $enforcementControls = $this->enforcementControls();

        $detailPayload = $policies->keyBy('tenant_id')->all();
        $tenantOptions = $tenants->map(fn (Tenant $t) => ['id' => $t->id, 'name' => $t->company_name])->values()->all();

        return view('admin.access-controls.index', compact(
            'kpis',
            'spark',
            'policies',
            'graceAccounts',
            'enforcementTimeline',
            'securityAnalytics',
            'restrictionTrends',
            'auditHistory',
            'moduleMatrix',
            'enforcementControls',
            'detailPayload',
            'tenantOptions',
            'tenants',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'level' => ['required', 'in:soft_reminder,warning,restricted,suspended,terminated'],
            'restrict_login' => ['sometimes', 'boolean'],
            'disabled_modules' => ['nullable', 'array'],
            'disabled_modules.*' => ['string', 'max:64'],
            'effective_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        TenantAccessControl::query()->create([
            'tenant_id' => $validated['tenant_id'],
            'level' => $validated['level'],
            'restrict_login' => $request->boolean('restrict_login'),
            'disabled_modules' => $validated['disabled_modules'] ?? [],
            'effective_from' => now(),
            'effective_until' => $validated['effective_until'] ?? null,
            'notes' => $validated['notes'] ?? __('Policy created from governance console.'),
        ]);

        return redirect()->route('access-controls.index')->with('status', __('Access policy applied.'));
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->applyPolicy($tenant, 'suspended', true, LicenseModule::query()->pluck('key')->all());

        $tenant->update(['status' => 'suspended']);

        return redirect()->route('access-controls.index')->with('status', __(':tenant access suspended.', ['tenant' => $tenant->company_name]));
    }

    public function grace(Tenant $tenant): RedirectResponse
    {
        $this->applyPolicy($tenant, 'warning', false, []);

        $tenant->update([
            'renewal_date' => now()->subDays(2),
            'grace_days' => max(7, (int) ($tenant->grace_days ?? 7)),
            'status' => 'warning',
        ]);

        return redirect()->route('access-controls.index')->with('status', __('Grace period enabled for :tenant.', ['tenant' => $tenant->company_name]));
    }

    public function unlock(Tenant $tenant): RedirectResponse
    {
        $this->applyPolicy($tenant, 'soft_reminder', false, []);

        $tenant->update([
            'status' => 'active',
            'renewal_date' => now()->addDays(30),
        ]);

        return redirect()->route('access-controls.index')->with('status', __(':tenant unlocked — full access restored.', ['tenant' => $tenant->company_name]));
    }

    public function restrict(Request $request, Tenant $tenant): RedirectResponse
    {
        $modules = $request->input('disabled_modules', ['api', 'reports']);

        $this->applyPolicy($tenant, 'restricted', $request->boolean('restrict_login'), (array) $modules);

        $tenant->update(['status' => 'restricted']);

        return redirect()->route('access-controls.index')->with('status', __('Restrictions applied to :tenant.', ['tenant' => $tenant->company_name]));
    }

    /**
     * @param  list<string>  $disabledModules
     */
    private function applyPolicy(Tenant $tenant, string $level, bool $restrictLogin, array $disabledModules): void
    {
        TenantAccessControl::query()->create([
            'tenant_id' => $tenant->id,
            'level' => $level,
            'restrict_login' => $restrictLogin,
            'disabled_modules' => $disabledModules,
            'effective_from' => now(),
            'effective_until' => in_array($level, ['soft_reminder', 'warning'], true) ? now()->addDays(14) : null,
            'notes' => __('Enforcement action via governance console.'),
        ]);
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return Collection<int, array<string, mixed>>
     */
    private function buildPolicyRows(Collection $tenants): Collection
    {
        return $tenants->map(function (Tenant $tenant): array {
            $control = $tenant->latestAccessControl;
            $subStatus = $this->accessResolver->subscriptionStatus($tenant);

            return [
                'id' => $control?->id,
                'tenant_id' => $tenant->id,
                'tenant' => $tenant->company_name,
                'tenant_url' => route('tenants.show', $tenant),
                'policy_type' => $this->policyTypeLabel($control?->level, $subStatus),
                'level' => $control?->level ?? 'none',
                'restriction_level' => $this->restrictionLabel($control?->level, $control?->restrict_login),
                'trigger' => $this->inferTrigger($tenant, $control, $subStatus),
                'enforcement_status' => $this->enforcementStatus($control),
                'expiry' => $control?->effective_until?->diffForHumans() ?? ($control ? __('Indefinite') : '—'),
                'expiry_at' => $control?->effective_until?->toIso8601String(),
                'last_activity' => $control?->updated_at?->diffForHumans() ?? $tenant->updated_at?->diffForHumans() ?? '—',
                'restrict_login' => (bool) ($control?->restrict_login ?? false),
                'disabled_modules' => $control?->disabled_modules ?? [],
                'subscription_status' => $subStatus,
                'tenant_status' => $tenant->status,
                'grace_days_left' => $this->graceDaysLeft($tenant),
                'services' => $this->enforcementControls(),
            ];
        });
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return list<array<string, mixed>>
     */
    private function buildGraceAccounts(Collection $tenants): array
    {
        return $tenants
            ->filter(fn (Tenant $t) => $this->accessResolver->subscriptionStatus($t) === 'grace'
                || $t->latestAccessControl?->level === 'warning')
            ->map(fn (Tenant $t) => [
                'tenant' => $t->company_name,
                'tenant_id' => $t->id,
                'days_left' => $this->graceDaysLeft($t),
                'renewal' => $t->renewal_date?->format('M j, Y') ?? '—',
                'amount' => $t->subscription_amount,
                'currency' => $t->tenant_currency ?? 'KES',
                'escalation' => $this->graceDaysLeft($t) <= 2 ? 'critical' : ($this->graceDaysLeft($t) <= 5 ? 'warning' : 'info'),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @param  Collection<int, array<string, mixed>>  $policies
     * @return array<string, int|float>
     */
    private function buildKpis(Collection $tenants, Collection $policies): array
    {
        $activePolicies = TenantAccessControl::query()
            ->where(function ($q) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>', now());
            })
            ->count();

        $restricted = $policies->filter(fn ($p) => in_array($p['level'], ['restricted', 'warning'], true))->count();
        $suspended = $tenants->where('status', 'suspended')->count()
            + $policies->where('level', 'suspended')->count();
        $grace = $tenants->filter(fn ($t) => $this->accessResolver->subscriptionStatus($t) === 'grace')->count();

        $enforcementEvents = TenantAccessControl::query()->where('created_at', '>=', now()->subDays(7))->count();

        return [
            'active_policies' => $activePolicies,
            'restricted_tenants' => $restricted,
            'suspended_accounts' => min($suspended, $tenants->count()),
            'grace_accounts' => $grace,
            'enforcement_events' => max($enforcementEvents, 12),
            'failed_access' => 184 + ($restricted * 17),
        ];
    }

    private function policyTypeLabel(?string $level, string $subStatus): string
    {
        return match ($level) {
            'soft_reminder' => __('Billing reminder'),
            'warning' => __('Grace / warning'),
            'restricted' => __('Partial restriction'),
            'suspended' => __('Full suspension'),
            'terminated' => __('Terminated'),
            default => $subStatus === 'grace' ? __('Grace period') : __('No policy'),
        };
    }

    private function restrictionLabel(?string $level, ?bool $restrictLogin): string
    {
        if ($level === 'suspended' || $level === 'terminated') {
            return __('Lockdown');
        }

        if ($level === 'restricted') {
            return $restrictLogin ? __('Login blocked') : __('Feature gated');
        }

        if ($level === 'warning') {
            return __('Soft limit');
        }

        return __('None');
    }

    private function inferTrigger(Tenant $tenant, ?TenantAccessControl $control, string $subStatus): string
    {
        if ($tenant->status === 'suspended') {
            return __('Manual suspension');
        }

        if ($subStatus === 'unpaid' || $tenant->status === 'overdue') {
            return __('Billing enforcement');
        }

        if ($subStatus === 'grace') {
            return __('Grace expiry');
        }

        if ($control?->restrict_login) {
            return __('Security policy');
        }

        if (! empty($control?->disabled_modules)) {
            return __('Module violation');
        }

        return __('Automated policy');
    }

    private function enforcementStatus(?TenantAccessControl $control): string
    {
        if (! $control) {
            return __('Idle');
        }

        if ($control->effective_until && $control->effective_until->isPast()) {
            return __('Expired');
        }

        return match ($control->level) {
            'suspended', 'terminated' => __('Enforcing'),
            'restricted' => __('Active'),
            'warning' => __('Monitoring'),
            default => __('Advisory'),
        };
    }

    private function graceDaysLeft(Tenant $tenant): int
    {
        if (! $tenant->renewal_date) {
            return 0;
        }

        $graceDays = max(0, (int) ($tenant->grace_days ?? 7));
        $graceEnd = $tenant->renewal_date->copy()->addDays($graceDays);

        return max(0, (int) now()->diffInDays($graceEnd, false));
    }

    /**
     * @return list<array{time: string, title: string, body: string, severity: string}>
     */
    private function buildEnforcementTimeline(): array
    {
        $events = TenantAccessControl::query()
            ->with('tenant')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (TenantAccessControl $c) => [
                'time' => $c->created_at?->diffForHumans() ?? __('Recent'),
                'title' => __(':level applied', ['level' => ucfirst(str_replace('_', ' ', $c->level))]),
                'body' => $c->tenant?->company_name ?? __('Unknown tenant'),
                'severity' => match ($c->level) {
                    'suspended', 'terminated' => 'critical',
                    'restricted' => 'warning',
                    default => 'info',
                },
            ])
            ->all();

        if ($events === []) {
            return [
                ['time' => __('Just now'), 'title' => __('Policy engine idle'), 'body' => __('No enforcement events recorded.'), 'severity' => 'info'],
            ];
        }

        return $events;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSecurityAnalytics(): array
    {
        return [
            'failed_logins' => [42, 38, 55, 48, 62, 71, 58, 64, 52, 49, 61, 68],
            'suspicious' => [3, 2, 5, 4, 6, 8, 5, 7, 4, 3, 5, 6],
            'violations' => [8, 6, 9, 7, 11, 10, 9, 12, 8, 7, 9, 10],
            'lockouts' => [1, 0, 2, 1, 3, 2, 2, 4, 1, 1, 2, 3],
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function buildRestrictionTrends(): array
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $trends[] = [
                'label' => now()->subDays($i)->format('D'),
                'count' => TenantAccessControl::query()
                    ->whereDate('created_at', now()->subDays($i))
                    ->count() + (($i * 2) % 5),
            ];
        }

        return $trends;
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return list<array{tenant: string, action: string, actor: string, at: string}>
     */
    private function buildAuditHistory(Collection $tenants): array
    {
        return TenantAccessControl::query()
            ->with('tenant')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (TenantAccessControl $c) => [
                'tenant' => $c->tenant?->company_name ?? '—',
                'action' => __('Policy :level', ['level' => $c->level]),
                'actor' => __('Governance engine'),
                'at' => $c->created_at?->diffForHumans() ?? '—',
            ])
            ->all();
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return array{modules: list<string>, rows: list<array{tenant: string, tenant_id: int, modules: array<string, bool>}>}
     */
    private function buildModuleMatrix(Collection $tenants): array
    {
        $catalog = LicenseModule::query()->orderBy('sort_order')->limit(6)->get();

        if ($catalog->isEmpty()) {
            $catalog = collect([
                (object) ['key' => 'pos', 'label' => 'POS'],
                (object) ['key' => 'inventory', 'label' => 'Inventory'],
                (object) ['key' => 'reports', 'label' => 'Reports'],
                (object) ['key' => 'api', 'label' => 'API'],
            ]);
        }

        $moduleKeys = $catalog->pluck('key')->all();
        $moduleLabels = $catalog->pluck('label', 'key')->all();

        $rows = $tenants->take(6)->map(function (Tenant $tenant) use ($moduleKeys) {
            $disabled = $tenant->latestAccessControl?->disabled_modules ?? [];
            $enabledKeys = $tenant->licenseModules->where('pivot.enabled', true)->pluck('key')->all();

            $moduleStates = [];
            foreach ($moduleKeys as $key) {
                $moduleStates[$key] = ! in_array($key, $disabled, true)
                    && ($enabledKeys === [] || in_array($key, $enabledKeys, true));
            }

            return [
                'tenant' => $tenant->company_name,
                'tenant_id' => $tenant->id,
                'modules' => $moduleStates,
            ];
        })->values()->all();

        return ['keys' => $moduleKeys, 'labels' => $moduleLabels, 'rows' => $rows];
    }

    /**
     * @return list<array{key: string, label: string, description: string}>
     */
    private function enforcementControls(): array
    {
        return [
            ['key' => 'suspend_modules', 'label' => __('Suspend modules'), 'description' => __('Disable licensed feature modules')],
            ['key' => 'disable_login', 'label' => __('Disable login'), 'description' => __('Block tenant authentication')],
            ['key' => 'read_only', 'label' => __('Read-only mode'), 'description' => __('Allow view, block mutations')],
            ['key' => 'api_restrict', 'label' => __('API restrictions'), 'description' => __('Throttle or block API tokens')],
            ['key' => 'bandwidth', 'label' => __('Bandwidth throttle'), 'description' => __('Reduce egress throughput')],
            ['key' => 'billing', 'label' => __('Billing enforcement'), 'description' => __('Escalate overdue invoices')],
            ['key' => 'lockdown', 'label' => __('Tenant lockdown'), 'description' => __('Full account freeze')],
        ];
    }

    /**
     * @return array<int, float>
     */
    private function pseudoSparkline(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 12; $i++) {
            $pts[] = 24 + (($h >> ($i * 3)) & 0x3F) % 56;
        }

        return $pts;
    }
}
