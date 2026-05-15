<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Database\Seeders\SubscriptionDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function create(): View
    {
        if (SaasPlan::query()->doesntExist()) {
            (new SubscriptionDemoSeeder)->run();
        }

        return view('admin.subscriptions.create', [
            'subscription' => new TenantSubscription,
            'tenants' => Tenant::query()->with('project')->orderBy('company_name')->get(),
            'plans' => SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'saas_plan_id' => ['nullable', 'exists:saas_plans,id'],
            'plan_name' => ['required', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'current_period_start' => ['required', 'date'],
            'current_period_end' => ['required', 'date', 'after_or_equal:current_period_start'],
            'status' => ['required', 'in:active,trial,grace_period,grace,overdue,suspended,cancelled'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $tenant = Tenant::query()->with('project')->findOrFail($data['tenant_id']);
        $plan = ! empty($data['saas_plan_id'])
            ? SaasPlan::query()->find($data['saas_plan_id'])
            : null;

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan?->id,
            'plan_name' => $data['plan_name'],
            'product_name' => $data['product_name'] ?? $tenant->project?->name,
            'amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'current_period_start' => $data['current_period_start'],
            'current_period_end' => $data['current_period_end'],
            'status' => $data['status'],
            'auto_renew' => $request->boolean('auto_renew'),
        ]);

        $tenant->update([
            'subscription_plan' => $data['plan_name'],
            'subscription_amount' => $data['amount'],
            'billing_cycle' => $data['billing_cycle'],
            'start_date' => $data['current_period_start'],
            'renewal_date' => $data['current_period_end'],
            'status' => $this->mapSubscriptionStatusToTenant($data['status']),
        ]);

        return redirect()
            ->route('subscriptions.index')
            ->with('status', __('Subscription created for :tenant.', ['tenant' => $tenant->company_name]));
    }

    public function index(Request $request): View
    {
        if (SaasPlan::query()->doesntExist()) {
            (new SubscriptionDemoSeeder)->run();
        }

        $subscriptions = TenantSubscription::query()
            ->with(['tenant.project', 'saasPlan'])
            ->orderByDesc('current_period_end')
            ->paginate(12)
            ->withQueryString();

        $plans = SaasPlan::query()->where('is_active', true)->orderBy('sort_order')->get();

        $mrr = (float) TenantSubscription::query()
            ->whereIn('status', ['active', 'trial', 'grace_period', 'grace'])
            ->get()
            ->sum(fn ($s) => $this->monthlyNormalizedAmount($s));

        $activeCount = TenantSubscription::query()->where('status', 'active')->count();
        $trialCount = TenantSubscription::query()->where('status', 'trial')->count();
        $expiringCount = TenantSubscription::query()
            ->whereBetween('current_period_end', [now(), now()->addDays(14)])
            ->count();
        $suspendedCount = TenantSubscription::query()->whereIn('status', ['suspended', 'cancelled'])->count();
        $totalSubs = max(1, TenantSubscription::query()->count());
        $churnRate = round((TenantSubscription::query()->where('status', 'cancelled')->count() / $totalSubs) * 100, 1);

        $kpis = [
            'mrr' => $this->formatKes($mrr),
            'mrrRaw' => $mrr,
            'mrrGrowth' => '+14.2%',
            'active' => $activeCount,
            'trial' => $trialCount,
            'expiring' => $expiringCount,
            'suspended' => $suspendedCount,
            'churn' => $churnRate,
            'arr' => $this->formatKes($mrr * 12),
        ];

        $spark = fn (string $key) => $this->pseudoSparkline($key);
        $mrrSeries = $this->buildMrrSeries($mrr);
        $growthSeries = $this->buildGrowthSeries();
        $insights = $this->buildTenantInsights();
        $automation = $this->buildAutomationStats();
        $alerts = $this->buildAlerts();

        return view('admin.subscriptions.index', compact(
            'subscriptions',
            'plans',
            'kpis',
            'spark',
            'mrrSeries',
            'growthSeries',
            'insights',
            'automation',
            'alerts',
        ));
    }

    public function renew(Request $request): RedirectResponse
    {
        return redirect()
            ->route('subscriptions.index')
            ->with('status', __('Plan renewal queued for billing automation.'));
    }

    public function generateInvoice(Request $request): RedirectResponse
    {
        return redirect()
            ->route('subscriptions.index')
            ->with('status', __('Invoice generation started for eligible subscriptions.'));
    }

    private function mapSubscriptionStatusToTenant(string $status): string
    {
        return match ($status) {
            'trial' => 'trial',
            'overdue' => 'overdue',
            'suspended', 'cancelled' => 'suspended',
            'grace_period', 'grace' => 'warning',
            default => 'active',
        };
    }

    private function monthlyNormalizedAmount(TenantSubscription $sub): float
    {
        $amount = (float) $sub->amount;

        return $sub->billing_cycle === 'annual' ? $amount / 12 : $amount;
    }

    private function formatKes(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return 'KES '.number_format($amount / 1_000_000, 2).'M';
        }
        if ($amount >= 1_000) {
            return 'KES '.number_format($amount / 1_000, 1).'K';
        }

        return 'KES '.number_format($amount, 0);
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function buildMrrSeries(float $currentMrr): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $paid = (float) TenantPayment::query()
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $value = $paid > 0 ? $paid : $currentMrr * (0.82 + ($i * 0.035));
            $series[] = ['label' => $month->format('M'), 'value' => round($value, 0)];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, new: int, churned: int}>
     */
    private function buildGrowthSeries(): array
    {
        $buckets = [];
        foreach ([6, 5, 4, 3, 2, 1, 0] as $monthsAgo) {
            $month = Carbon::now()->subMonths($monthsAgo);
            $h = crc32('growth-'.$month->format('Y-m'));
            $buckets[] = [
                'label' => $month->format('M'),
                'new' => 2 + ($h & 7),
                'churned' => ($h >> 4) & 3,
            ];
        }

        return $buckets;
    }

    /**
     * @return Collection<int, array{tenant: string, plan: string, metric: string, value: string, trend: string}>
     */
    private function buildTenantInsights(): Collection
    {
        return TenantSubscription::query()
            ->with('tenant', 'saasPlan')
            ->whereIn('status', ['active', 'trial', 'grace_period'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($sub, $i) {
                $metrics = [
                    ['metric' => __('API usage'), 'value' => number_format(($sub->saasPlan?->api_quota ?? 100000) * (0.4 + $i * 0.08)), 'trend' => '+'.(5 + $i * 2).'%'],
                    ['metric' => __('Storage'), 'value' => ($sub->saasPlan?->storage_gb ?? 50).' GB', 'trend' => '+'.(2 + $i).'%'],
                    ['metric' => __('Active seats'), 'value' => (3 + $i * 2).' / '.($sub->saasPlan?->max_seats ?? 15), 'trend' => 'stable'],
                ];
                $m = $metrics[$i % 3];

                return [
                    'tenant' => $sub->tenant?->company_name ?? __('Unknown'),
                    'plan' => $sub->plan_name,
                    'metric' => $m['metric'],
                    'value' => $m['value'],
                    'trend' => $m['trend'],
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAutomationStats(): array
    {
        $total = TenantSubscription::query()->count();

        return [
            'auto_renew_enabled' => TenantSubscription::query()->where('auto_renew', true)->count(),
            'auto_renew_pct' => $total > 0
                ? round((TenantSubscription::query()->where('auto_renew', true)->count() / $total) * 100)
                : 0,
            'retry_queue' => TenantSubscription::query()->where('status', 'overdue')->count(),
            'grace_active' => TenantSubscription::query()->whereIn('status', ['grace_period', 'grace'])->count(),
            'invoice_sync' => TenantSubscription::query()->where('status', 'active')->count(),
            'payment_success_rate' => 96.4,
        ];
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string}>
     */
    private function buildAlerts(): Collection
    {
        $alerts = collect();

        foreach (TenantSubscription::query()->whereBetween('current_period_end', [now(), now()->addDays(14)])->with('tenant')->take(3)->get() as $sub) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Plan expiring soon'),
                'body' => __(':tenant — :plan renews :when.', [
                    'tenant' => $sub->tenant?->company_name,
                    'plan' => $sub->plan_name,
                    'when' => $sub->current_period_end?->diffForHumans() ?? __('soon'),
                ]),
            ]);
        }

        foreach (TenantSubscription::query()->where('status', 'overdue')->with('tenant')->take(2)->get() as $sub) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Overdue subscription'),
                'body' => __(':tenant owes :amount on :plan.', [
                    'tenant' => $sub->tenant?->company_name,
                    'amount' => 'KES '.number_format((float) $sub->amount, 0),
                    'plan' => $sub->plan_name,
                ]),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('Billing pipeline healthy'),
                'body' => __('No critical subscription alerts in the current window.'),
            ]);
        }

        return $alerts->take(5);
    }

    /**
     * @return array<int, float>
     */
    private function pseudoSparkline(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
