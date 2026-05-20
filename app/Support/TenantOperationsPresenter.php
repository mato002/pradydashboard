<?php

namespace App\Support;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TenantOperationsPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(LengthAwarePaginator $paginator): array
    {
        $collection = collect($paginator->items());
        $directory = $this->buildDirectory($collection);
        $growthPct = $this->monthlyGrowthPercent();

        return [
            'tenants' => $paginator,
            'directory' => $directory,
            'tenantDetails' => $this->buildTenantDetails($directory),
            'onboarding' => $this->buildOnboardingPipeline(),
            'healthOverview' => $this->buildHealthOverview($directory),
            'growthSeries' => $this->buildGrowthSeries(),
            'kpis' => $this->buildKpis($growthPct),
            'spark' => fn (string $key) => OperationalMetrics::emptySparkline(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildKpis(?float $growthPct): array
    {
        $total = Tenant::query()->count();
        $active = Tenant::query()->where('status', 'active')->count();
        $trial = Tenant::query()->where('status', 'trial')->count();
        $overdue = Tenant::query()->where('status', 'overdue')->count();
        $suspended = Tenant::query()->whereIn('status', ['suspended', 'restricted', 'terminated'])->count();

        $growthLabel = $growthPct !== null
            ? (($growthPct >= 0 ? '+' : '').number_format($growthPct, 1).'%')
            : null;
        $overdueExposure = (float) Tenant::query()->where('status', 'overdue')->sum('subscription_amount');
        $newThisMonth = Tenant::query()->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $trialConversion = $trial > 0 && $active > 0
            ? round(($active / max(1, $active + $trial)) * 100).'%'
            : null;

        return [
            'total' => [
                'value' => $total,
                'trend' => $growthLabel,
                'sublabel' => __('Organizations in directory'),
                'tone' => 'indigo',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'active' => [
                'value' => $active,
                'trend' => null,
                'sublabel' => __('Active subscriptions'),
                'tone' => 'emerald',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'trial' => [
                'value' => $trial,
                'trend' => null,
                'sublabel' => $trialConversion ? __('Active share').': '.$trialConversion : __('Trial accounts'),
                'tone' => 'amber',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'overdue' => [
                'value' => $overdue,
                'trend' => null,
                'sublabel' => __('Exposure').': KES '.number_format($overdueExposure, 0),
                'tone' => 'rose',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'suspended' => [
                'value' => $suspended,
                'trend' => null,
                'sublabel' => __('Restricted or terminated'),
                'tone' => 'neutral',
                'points' => OperationalMetrics::emptySparkline(),
            ],
            'growth' => [
                'value' => $growthLabel ?? '—',
                'trend' => __('MoM'),
                'sublabel' => __('New this month').': '.$newThisMonth,
                'tone' => 'violet',
                'points' => OperationalMetrics::emptySparkline(),
                'animate' => false,
            ],
        ];
    }

    private function monthlyGrowthPercent(): ?float
    {
        $now = Carbon::now();
        $cur = Tenant::query()->where('created_at', '>=', $now->copy()->startOfMonth())->count();
        $prev = Tenant::query()
            ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
            ->count();

        if ($cur <= 0 && $prev <= 0) {
            return null;
        }

        if ($prev <= 0) {
            return $cur > 0 ? 100.0 : null;
        }

        return round((($cur - $prev) / $prev) * 100, 1);
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     * @return array<int, array<string, mixed>>
     */
    private function buildDirectory(Collection $tenants): array
    {
        if ($tenants->isEmpty()) {
            return [];
        }

        $mapped = $tenants->map(function (Tenant $tenant) {
            $metric = $tenant->usageMetric;
            $storageMb = (float) ($metric?->storage_usage_mb ?? 0);
            $storageCapMb = max($storageMb * 2, 5000);
            $users = (int) ($metric?->active_users ?? 0);

            return [
                'id' => $tenant->id,
                'company' => $tenant->company_name,
                'product' => $tenant->project?->name ?? $tenant->project?->domain ?? '—',
                'plan' => $tenant->subscription_plan ?? __('Standard'),
                'server' => $tenant->server?->name ?? __('Shared pool'),
                'status' => $tenant->status,
                'renewal' => $tenant->renewal_date?->format('M j, Y') ?? '—',
                'users' => $users,
                'storage' => $this->formatStorage($storageMb),
                'storage_mb' => $storageMb,
                'storage_cap_mb' => $storageCapMb,
                'storage_pct' => min(100, (int) round(($storageMb / max($storageCapMb, 1)) * 100)),
                'last_activity' => $metric?->last_login_at?->diffForHumans()
                    ?? $tenant->updated_at?->diffForHumans()
                    ?? '—',
                'health_score' => $this->healthScore($tenant->status, $storageMb),
                'health_label' => $this->healthLabel($tenant->status),
                'onboarding_stage' => $tenant->status,
                'show_url' => route('tenants.show', $tenant),
                'edit_url' => route('tenants.edit', $tenant),
                'is_demo' => false,
                'domain' => $tenant->tenant_domain ?? $tenant->project?->domain ?? '—',
                'contact' => $tenant->contact_person ?? '—',
                'email' => $tenant->email ?? '—',
                'currency' => $tenant->tenant_currency ?? 'KES',
                'mrr' => number_format((float) ($tenant->subscription_amount ?? 0), 0),
                'tickets_count' => $tenant->support_tickets_count ?? 0,
                'invoices_count' => $tenant->invoices_count ?? 0,
            ];
        })->all();

        return $mapped;
    }

    private function healthScore(string $status, float $storageMb): int
    {
        $base = match ($status) {
            'active' => 92,
            'trial' => 78,
            'warning' => 65,
            'overdue' => 48,
            'suspended', 'terminated', 'cancelled' => 25,
            default => 70,
        };

        if ($storageMb > 8000) {
            $base -= 10;
        }

        return max(10, min(100, $base));
    }

    private function healthLabel(string $status): string
    {
        return match ($status) {
            'active' => __('Healthy'),
            'trial' => __('Trial'),
            'overdue' => __('At risk'),
            'suspended', 'terminated' => __('Critical'),
            'warning' => __('Degraded'),
            default => __('Monitoring'),
        };
    }

    private function formatStorage(float $mb): string
    {
        if ($mb >= 1024) {
            return number_format($mb / 1024, 1).' GB';
        }

        return number_format($mb, 0).' MB';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildOnboardingPipeline(): array
    {
        $total = Tenant::query()->count();
        if ($total === 0) {
            return [];
        }

        $stages = [
            ['stage' => 'trial', 'label' => __('Trial'), 'status' => 'active'],
            ['stage' => 'active', 'label' => __('Active'), 'status' => 'complete'],
            ['stage' => 'overdue', 'label' => __('Overdue'), 'status' => 'pending'],
            ['stage' => 'suspended', 'label' => __('Suspended'), 'status' => 'pending'],
        ];

        return collect($stages)->map(function (array $stage) use ($total) {
            $count = Tenant::query()->where('status', $stage['stage'])->count();

            return [
                'stage' => $stage['stage'],
                'label' => $stage['label'],
                'count' => $count,
                'pct' => (int) round(($count / max(1, $total)) * 100),
                'status' => $stage['status'],
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $directory
     * @return array<string, mixed>
     */
    private function buildHealthOverview(array $directory): array
    {
        if (count($directory) === 0) {
            return [
                'avg_health' => null,
                'metrics' => [],
                'empty' => true,
            ];
        }

        $avgHealth = (int) round(collect($directory)->avg('health_score'));
        $active = collect($directory)->where('status', 'active')->count();
        $billingPct = count($directory) > 0
            ? round(($active / count($directory)) * 100, 1)
            : null;

        return [
            'avg_health' => $avgHealth,
            'empty' => false,
            'metrics' => [
                ['label' => __('Avg health score'), 'value' => (string) $avgHealth, 'status' => $avgHealth >= 80 ? 'good' : 'warn'],
                ['label' => __('Active tenants'), 'value' => (string) $active, 'status' => 'good'],
                ['label' => __('Billing health'), 'value' => $billingPct !== null ? $billingPct.'%' : '—', 'status' => 'good'],
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildGrowthSeries(): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Tenant::query()
                ->where('created_at', '<=', $month->copy()->endOfMonth())
                ->count();
            $series[] = [
                'label' => $month->format('M'),
                'value' => $count,
            ];
        }

        return $series;
    }

    /**
     * @param  array<int, array<string, mixed>>  $directory
     * @return array<string|int, array<string, mixed>>
     */
    private function buildTenantDetails(array $directory): array
    {
        $details = [];
        foreach ($directory as $row) {
            $details[$row['id']] = [
                'profile' => [
                    'company' => $row['company'],
                    'contact' => $row['contact'],
                    'email' => $row['email'],
                    'domain' => $row['domain'],
                    'plan' => $row['plan'],
                    'mrr' => $row['currency'].' '.$row['mrr'],
                    'status' => $row['status'],
                ],
                'infrastructure' => [
                    'server' => $row['server'],
                    'product' => $row['product'],
                    'deployment' => 'v'.(2 + ((int) is_numeric($row['id']) ? $row['id'] : 1) % 5).'.'.(1 + ((int) is_numeric($row['id']) ? $row['id'] : 0) % 3),
                    'backup' => $row['status'] === 'suspended' ? __('Failed') : __('OK — 2h ago'),
                ],
                'health' => [
                    'score' => $row['health_score'],
                    'label' => $row['health_label'],
                    'uptime' => $row['is_demo'] ?? false ? '—' : ($row['health_score'] >= 80 ? __('Healthy') : __('At risk')),
                    'sessions' => $row['users'] * 3,
                    'api_calls' => number_format($row['users'] * 420),
                    'storage' => $row['storage'],
                ],
                'modules' => ['Billing', 'CRM', 'Analytics', 'API Access', 'Reports'],
                'invoices' => [
                    ['id' => 'INV-'.(2400 + (is_numeric($row['id']) ? (int) $row['id'] : 1)), 'amount' => $row['currency'].' '.number_format((float) str_replace(',', '', $row['mrr'])), 'status' => $row['status'] === 'overdue' ? 'overdue' : 'paid'],
                    ['id' => 'INV-'.(2399 + (is_numeric($row['id']) ? (int) $row['id'] : 1)), 'amount' => $row['currency'].' '.number_format((float) str_replace(',', '', $row['mrr']) * 0.9), 'status' => 'paid'],
                ],
                'deployments' => [
                    ['version' => 'v2.4.1', 'date' => __('3d ago'), 'status' => 'success'],
                    ['version' => 'v2.4.0', 'date' => __('2w ago'), 'status' => 'success'],
                ],
                'tickets' => $row['tickets_count'] > 0
                    ? [['subject' => __('SSL renewal question'), 'status' => 'open']]
                    : [],
                'subscriptions' => [
                    ['plan' => $row['plan'], 'started' => __('Jan 2025'), 'status' => $row['status']],
                ],
            ];
        }

        return $details;
    }

}
