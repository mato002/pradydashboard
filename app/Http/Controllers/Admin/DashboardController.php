<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $serversCount = Server::query()->count();
        $projectsCount = Project::query()->count();
        $activeProjects = Project::query()->where('status', 'active')->count();
        $tenantsCount = Tenant::query()->count();
        $activeTenants = Tenant::query()->where('status', 'active')->count();
        $onlineServers = Server::query()->where('status', 'online')->count();
        $overdueTenants = Tenant::query()->where('status', 'overdue')->count();
        $openTickets = SupportTicket::query()->where('status', 'open')->count();
        $highPriorityTickets = SupportTicket::query()
            ->where('status', 'open')
            ->whereIn('priority', ['high', 'urgent'])
            ->count();

        $tenantMrr = (float) Tenant::query()->whereIn('status', ['active', 'trial'])->sum('subscription_amount');
        $projectMrr = (float) Project::query()->sum('monthly_revenue');
        $monthlyRevenue = $tenantMrr > 0 ? $tenantMrr : $projectMrr;

        $overdueExposure = (float) Tenant::query()->where('status', 'overdue')->sum('subscription_amount');

        $revenueGrowthPct = $this->estimateRevenueGrowthPercent();

        $servers = Server::query()
            ->withCount(['projects', 'tenants'])
            ->with('latestHealthLog')
            ->orderBy('name')
            ->take(8)
            ->get();

        $recentTenants = Tenant::query()
            ->with('project')
            ->latest()
            ->take(6)
            ->get();

        $revenueSeries = $this->buildRevenueSeries($monthlyRevenue);
        $productRevenue = $this->buildProductRevenue();
        $systemAlerts = $this->buildSystemAlerts($servers, $overdueTenants);

        $spark = fn (string $key) => $this->pseudoSparkline($key);

        return view('admin.dashboard', compact(
            'serversCount',
            'projectsCount',
            'activeProjects',
            'tenantsCount',
            'activeTenants',
            'onlineServers',
            'overdueTenants',
            'openTickets',
            'highPriorityTickets',
            'monthlyRevenue',
            'overdueExposure',
            'revenueGrowthPct',
            'servers',
            'recentTenants',
            'revenueSeries',
            'productRevenue',
            'systemAlerts',
            'spark',
        ));
    }

    private function estimateRevenueGrowthPercent(): ?float
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->startOfMonth();
        $previousStart = $now->copy()->subMonth()->startOfMonth();
        $previousEnd = $now->copy()->subMonth()->endOfMonth();

        $cur = (float) TenantPayment::query()->whereBetween('paid_at', [$currentStart, $now])->sum('amount');
        $prev = (float) TenantPayment::query()->whereBetween('paid_at', [$previousStart, $previousEnd])->sum('amount');

        if ($cur <= 0 && $prev <= 0) {
            return null;
        }

        if ($prev <= 0) {
            return $cur > 0 ? 100.0 : null;
        }

        return round((($cur - $prev) / $prev) * 100, 1);
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function buildRevenueSeries(float $fallbackMrr): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $sum = (float) TenantPayment::query()
                ->whereBetween('paid_at', [$month->copy(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $series[] = [
                'label' => $month->format('M'),
                'value' => $sum,
            ];
        }

        if (collect($series)->sum('value') <= 0 && $fallbackMrr > 0) {
            $base = $fallbackMrr / max(1, count($series));
            foreach ($series as $k => $row) {
                $wave = 0.82 + ($k * 0.03);
                $series[$k]['value'] = round($base * $wave, 2);
            }
        }

        return $series;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{name: string, amount: float, pct: float}>
     */
    private function buildProductRevenue(): Collection
    {
        $rows = Project::query()
            ->selectRaw('COALESCE(NULLIF(product_slug, ""), name) as bucket')
            ->selectRaw('SUM(COALESCE(monthly_revenue, 0)) as total')
            ->groupBy('bucket')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $sum = (float) $rows->sum('total');
        if ($sum <= 0) {
            return collect();
        }

        return $rows->map(function ($row) use ($sum) {
            $amount = (float) $row->total;

            return [
                'name' => (string) $row->bucket,
                'amount' => $amount,
                'pct' => $sum > 0 ? round(($amount / $sum) * 100, 1) : 0,
            ];
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildSystemAlerts(\Illuminate\Support\Collection $servers, int $overdueTenants): Collection
    {
        $alerts = collect();

        foreach ($servers as $server) {
            if ($server->renewal_expires_at && $server->renewal_expires_at->lte(Carbon::now()->addDays(14))) {
                $alerts->push([
                    'type' => 'critical',
                    'title' => __('SSL / renewal window'),
                    'body' => __(':server renews on :date', ['server' => $server->name, 'date' => $server->renewal_expires_at->toFormattedDateString()]),
                    'time' => __('Soon'),
                ]);
            }

            $disk = $server->disk_usage_percent ?? $server->latestHealthLog?->disk_percent;
            if ($disk !== null && (float) $disk >= 85) {
                $alerts->push([
                    'type' => 'warning',
                    'title' => __('High disk usage'),
                    'body' => __(':server is at :pct% disk.', ['server' => $server->name, 'pct' => (int) round((float) $disk)]),
                    'time' => __('Live'),
                ]);
            }

            if (in_array(strtolower((string) $server->backup_status), ['failed', 'error'], true)) {
                $alerts->push([
                    'type' => 'info',
                    'title' => __('Backup attention'),
                    'body' => __('Last backup reported issues on :server.', ['server' => $server->name]),
                    'time' => __('Ops'),
                ]);
            }
        }

        if ($overdueTenants > 0) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Overdue tenants'),
                'body' => __('Tenants overdue on billing: :count', ['count' => $overdueTenants]),
                'time' => __('Billing'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('All systems nominal'),
                'body' => __('No critical infrastructure alerts right now.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(8);
    }

    /**
     * @return array<int, float>
     */
    private function pseudoSparkline(string $seed): array
    {
        $h = crc32($seed);
        $pts = [];
        for ($i = 0; $i < 8; $i++) {
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3f) % 48;
        }

        return $pts;
    }
}
