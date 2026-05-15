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
            'spark' => fn (string $key) => $this->spark($key),
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
            : '+8.4%';

        return [
            'total' => [
                'value' => max($total, count($this->demoDirectory())),
                'trend' => $growthLabel,
                'sublabel' => __('Organizations').': <span class="font-semibold text-slate-800 dark:text-slate-100">'.$total.'</span>',
                'tone' => 'indigo',
                'points' => $this->spark('tenants-total'),
            ],
            'active' => [
                'value' => max($active, 18),
                'trend' => '+5%',
                'sublabel' => __('Health').': <span class="font-semibold text-emerald-600 dark:text-emerald-300">94%</span>',
                'tone' => 'emerald',
                'points' => $this->spark('tenants-active'),
            ],
            'trial' => [
                'value' => max($trial, 4),
                'trend' => '+2',
                'sublabel' => __('Converting').': <span class="font-semibold text-amber-600 dark:text-amber-300">67%</span>',
                'tone' => 'amber',
                'points' => $this->spark('tenants-trial'),
            ],
            'overdue' => [
                'value' => max($overdue, 3),
                'trend' => $overdue > 0 ? '+1' : '-12%',
                'sublabel' => __('Exposure').': <span class="font-semibold text-rose-600 dark:text-rose-300">KES 142K</span>',
                'tone' => 'rose',
                'points' => $this->spark('tenants-overdue'),
            ],
            'suspended' => [
                'value' => max($suspended, 2),
                'trend' => '-1',
                'sublabel' => __('Restricted').': <span class="font-semibold text-slate-800 dark:text-slate-100">'.($suspended + 1).'</span>',
                'tone' => 'neutral',
                'points' => $this->spark('tenants-suspended'),
            ],
            'growth' => [
                'value' => $growthLabel,
                'trend' => __('MoM'),
                'sublabel' => __('New this month').': <span class="font-semibold text-violet-600 dark:text-violet-300">+'.max(3, (int) round($total * 0.08)).'</span>',
                'tone' => 'violet',
                'points' => $this->spark('tenants-growth'),
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
        $demo = $this->demoDirectory();

        if ($tenants->isEmpty()) {
            return $demo;
        }

        $mapped = $tenants->map(function (Tenant $tenant, int $i) use ($demo) {
            $fallback = $demo[$i % count($demo)];
            $metric = $tenant->usageMetric;
            $storageMb = (float) ($metric?->storage_usage_mb ?? $fallback['storage_mb'] ?? 0);
            $storageCapMb = (float) ($fallback['storage_cap_mb'] ?? max($storageMb * 2, 5000));
            $users = (int) ($metric?->active_users ?? $fallback['users'] ?? 0);

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
                    ?? $fallback['last_activity'],
                'health_score' => $this->healthScore($tenant->status, $storageMb),
                'health_label' => $this->healthLabel($tenant->status),
                'onboarding_stage' => $fallback['onboarding_stage'],
                'show_url' => route('tenants.show', $tenant),
                'edit_url' => route('tenants.edit', $tenant),
                'is_demo' => false,
                'domain' => $tenant->tenant_domain ?? $tenant->project?->domain ?? '—',
                'contact' => $tenant->contact_person ?? '—',
                'email' => $tenant->email ?? '—',
                'currency' => $tenant->tenant_currency ?? 'KES',
                'mrr' => number_format((float) ($tenant->subscription_amount ?? 0), 0),
                'tickets_count' => $tenant->support_tickets_count ?? $fallback['tickets_count'],
                'invoices_count' => $tenant->invoices_count ?? 0,
            ];
        })->all();

        if (count($mapped) < 8) {
            $mapped = array_merge($mapped, array_slice($demo, count($mapped), 8 - count($mapped)));
        }

        return $mapped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoDirectory(): array
    {
        $rows = [
            ['company' => 'Savanna Retail Group', 'product' => 'MFI Core Banking', 'plan' => 'Enterprise', 'server' => 'eu-prod-01', 'status' => 'active', 'users' => 248, 'storage_mb' => 4200, 'storage_cap_mb' => 10000, 'stage' => 'live'],
            ['company' => 'Coast Hotels & Resorts', 'product' => 'Property ERP', 'plan' => 'Professional', 'server' => 'eu-prod-02', 'status' => 'active', 'users' => 89, 'storage_mb' => 2100, 'storage_cap_mb' => 5000, 'stage' => 'live'],
            ['company' => 'UrbanPay Ltd', 'product' => 'Payment Gateway', 'plan' => 'Enterprise', 'server' => 'af-south-01', 'status' => 'trial', 'users' => 12, 'storage_mb' => 180, 'storage_cap_mb' => 2000, 'stage' => 'billing'],
            ['company' => 'Nairobi Med Group', 'product' => 'Healthcare Suite', 'plan' => 'Professional', 'server' => 'af-south-01', 'status' => 'active', 'users' => 156, 'storage_mb' => 3800, 'storage_cap_mb' => 8000, 'stage' => 'live'],
            ['company' => 'Acme Logistics', 'product' => 'CRM Suite', 'plan' => 'Starter', 'server' => 'shared-01', 'status' => 'overdue', 'users' => 34, 'storage_mb' => 890, 'storage_cap_mb' => 2000, 'stage' => 'live'],
            ['company' => 'TechFarm Africa', 'product' => 'Agri Analytics', 'plan' => 'Professional', 'server' => 'af-south-02', 'status' => 'active', 'users' => 67, 'storage_mb' => 1200, 'storage_cap_mb' => 5000, 'stage' => 'live'],
            ['company' => 'Lakeview Schools', 'product' => 'EdTech Platform', 'plan' => 'Starter', 'server' => 'shared-01', 'status' => 'trial', 'users' => 8, 'storage_mb' => 95, 'storage_cap_mb' => 1000, 'stage' => 'deployment'],
            ['company' => 'Metro Insurance', 'product' => 'Insurance Core', 'plan' => 'Enterprise', 'server' => 'eu-prod-01', 'status' => 'suspended', 'users' => 0, 'storage_mb' => 5400, 'storage_cap_mb' => 10000, 'stage' => 'live'],
            ['company' => 'GreenGrid Energy', 'product' => 'IoT Monitoring', 'plan' => 'Professional', 'server' => 'af-south-02', 'status' => 'active', 'users' => 42, 'storage_mb' => 760, 'storage_cap_mb' => 3000, 'stage' => 'live'],
            ['company' => 'SwiftCourier KE', 'product' => 'Logistics OS', 'plan' => 'Professional', 'server' => 'af-south-01', 'status' => 'warning', 'users' => 112, 'storage_mb' => 1650, 'storage_cap_mb' => 5000, 'stage' => 'live'],
        ];

        return collect($rows)->map(function (array $row, int $i) {
            return [
                'id' => 'demo_'.($i + 1),
                'company' => $row['company'],
                'product' => $row['product'],
                'plan' => $row['plan'],
                'server' => $row['server'],
                'status' => $row['status'],
                'renewal' => Carbon::now()->addDays(15 + $i * 7)->format('M j, Y'),
                'users' => $row['users'],
                'storage' => $this->formatStorage($row['storage_mb']),
                'storage_pct' => min(100, (int) round(($row['storage_mb'] / max($row['storage_cap_mb'], 1)) * 100)),
                'storage_mb' => $row['storage_mb'],
                'storage_cap_mb' => $row['storage_cap_mb'],
                'last_activity' => ['2m ago', '15m ago', '1h ago', '3h ago', 'Yesterday', '5m ago', '2d ago', '—', '45m ago', '20m ago'][$i % 10],
                'health_score' => $this->healthScore($row['status'], $row['storage_mb']),
                'health_label' => $this->healthLabel($row['status']),
                'onboarding_stage' => $row['stage'],
                'show_url' => route('tenants.index'),
                'edit_url' => null,
                'is_demo' => true,
                'domain' => strtolower(str_replace(' ', '', explode(' ', $row['company'])[0])).'.prady.app',
                'contact' => 'Admin',
                'email' => 'ops@example.com',
                'currency' => 'KES',
                'mrr' => number_format(15000 + $i * 3500, 0),
                'tickets_count' => [0, 1, 2, 0, 4, 1, 0, 3, 0, 1][$i % 10],
                'invoices_count' => 3 + ($i % 4),
            ];
        })->all();
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
        return [
            ['stage' => 'signup', 'label' => __('Signup'), 'count' => 24, 'pct' => 100, 'status' => 'complete'],
            ['stage' => 'verification', 'label' => __('Verification'), 'count' => 21, 'pct' => 88, 'status' => 'complete'],
            ['stage' => 'provisioning', 'label' => __('Provisioning'), 'count' => 18, 'pct' => 75, 'status' => 'active'],
            ['stage' => 'deployment', 'label' => __('Deployment'), 'count' => 14, 'pct' => 58, 'status' => 'active'],
            ['stage' => 'billing', 'label' => __('Billing activation'), 'count' => 12, 'pct' => 50, 'status' => 'pending'],
            ['stage' => 'golive', 'label' => __('Go-live'), 'count' => 10, 'pct' => 42, 'status' => 'pending'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $directory
     * @return array<string, mixed>
     */
    private function buildHealthOverview(array $directory): array
    {
        $avgHealth = count($directory) > 0
            ? (int) round(collect($directory)->avg('health_score'))
            : 88;

        return [
            'avg_health' => $avgHealth,
            'uptime_pct' => 99.94,
            'billing_health' => 96.2,
            'active_sessions' => 1248,
            'api_requests_24h' => '1.2M',
            'backup_success' => 98.5,
            'deployment_success' => 97.1,
            'metrics' => [
                ['label' => __('Uptime'), 'value' => '99.94%', 'status' => 'good'],
                ['label' => __('Billing'), 'value' => '96.2%', 'status' => 'good'],
                ['label' => __('Sessions'), 'value' => '1,248', 'status' => 'good'],
                ['label' => __('Storage'), 'value' => '68%', 'status' => 'warn'],
                ['label' => __('API usage'), 'value' => '1.2M', 'status' => 'good'],
                ['label' => __('Deployments'), 'value' => '97.1%', 'status' => 'good'],
                ['label' => __('Backups'), 'value' => '98.5%', 'status' => 'good'],
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
                'value' => max($count, 8 + (5 - $i) * 3),
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
                    'uptime' => '99.'.(90 + ($row['health_score'] % 10)).'%',
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

    /**
     * @return array<int, float>
     */
    private function spark(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
