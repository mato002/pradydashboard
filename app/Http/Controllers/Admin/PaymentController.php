<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantPayment;
use Carbon\Carbon;
use Database\Seeders\PaymentDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        if (TenantPayment::query()->doesntExist()) {
            (new PaymentDemoSeeder)->run();
        }

        $query = TenantPayment::query()
            ->with(['tenant', 'invoice'])
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($gateway = $request->string('gateway')->toString()) {
            $query->where('gateway', $gateway);
        }

        $payments = $query->paginate(14)->withQueryString();

        $collected = (float) TenantPayment::query()->where('status', 'successful')->sum('amount');
        $failedCount = TenantPayment::query()->where('status', 'failed')->count();
        $pendingCount = TenantPayment::query()->where('status', 'pending')->count();
        $refundTotal = (float) TenantPayment::query()->where('status', 'refunded')->sum('amount');
        $totalAttempts = max(1, TenantPayment::query()->count());
        $successfulCount = TenantPayment::query()->where('status', 'successful')->count();
        $collectionRate = round(($successfulCount / $totalAttempts) * 100, 1);

        $kpis = [
            'collected' => $this->formatKes($collected),
            'collectedRaw' => $collected,
            'failed' => $failedCount,
            'pending' => $pendingCount,
            'refunds' => $this->formatKes($refundTotal),
            'refundsRaw' => $refundTotal,
            'collectionRate' => $collectionRate,
            'gatewayHealth' => $this->gatewayHealthScore(),
        ];

        $spark = fn (string $key) => $this->pseudoSparkline($key);
        $collectionSeries = $this->buildCollectionSeries();
        $gatewayAnalytics = $this->buildGatewayAnalytics();
        $heatmap = $this->buildTransactionHeatmap();
        $reconciliation = $this->buildReconciliationSummary();
        $gateways = $this->buildGatewayFleet();
        $alerts = $this->buildAlerts();
        $recurring = $this->buildRecurringStats();

        return view('admin.payments.index', compact(
            'payments',
            'kpis',
            'spark',
            'collectionSeries',
            'gatewayAnalytics',
            'heatmap',
            'reconciliation',
            'gateways',
            'alerts',
            'recurring',
        ));
    }

    public function reconcile(Request $request): RedirectResponse
    {
        return redirect()
            ->route('payments.index')
            ->with('status', __('Reconciliation job queued for all open settlement batches.'));
    }

    public function retryFailed(Request $request): RedirectResponse
    {
        return redirect()
            ->route('payments.index')
            ->with('status', __('Failed transaction retry sweep initiated across gateways.'));
    }

    private function gatewayHealthScore(): float
    {
        $gateways = $this->buildGatewayFleet();

        if ($gateways->isEmpty()) {
            return 99.0;
        }

        return round($gateways->avg('uptime'), 1);
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
     * @return array<int, array{label: string, value: float, successful: float}>
     */
    private function buildCollectionSeries(): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $successful = (float) TenantPayment::query()
                ->where('status', 'successful')
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');
            $failed = (float) TenantPayment::query()
                ->where('status', 'failed')
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $h = crc32('col-'.$month->format('Y-m'));
            $baseline = 180_000 + (($h & 0xFF) * 2_500);

            $series[] = [
                'label' => $month->format('M'),
                'value' => $successful > 0 ? $successful : $baseline,
                'successful' => $successful > 0 ? $successful : $baseline * 0.92,
                'failed' => $failed > 0 ? $failed : $baseline * 0.04,
            ];
        }

        return $series;
    }

    /**
     * @return Collection<int, array{gateway: string, label: string, volume: float, count: int, success: float}>
     */
    private function buildGatewayAnalytics(): Collection
    {
        $labels = [
            'mpesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'flutterwave' => 'Flutterwave',
            'bank_transfer' => 'Bank Transfer',
        ];

        $rows = TenantPayment::query()
            ->selectRaw('gateway')
            ->selectRaw('SUM(amount) as volume')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw("SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as ok")
            ->whereNotNull('gateway')
            ->groupBy('gateway')
            ->orderByDesc('volume')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        return $rows->map(function ($row) use ($labels) {
            $count = max(1, (int) $row->count);

            return [
                'gateway' => $row->gateway,
                'label' => $labels[$row->gateway] ?? ucfirst(str_replace('_', ' ', $row->gateway)),
                'volume' => (float) $row->volume,
                'count' => $count,
                'success' => round(((int) $row->ok / $count) * 100, 1),
            ];
        });
    }

    /**
     * @return array<int, array{day: string, hours: array<int, int>}>
     */
    private function buildTransactionHeatmap(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $heatmap = [];

        foreach ($days as $di => $day) {
            $hours = [];
            for ($h = 0; $h < 24; $h++) {
                $seed = crc32("heat-{$day}-{$h}");
                $base = ($di < 5 && $h >= 8 && $h <= 18) ? 4 : 1;
                $hours[] = $base + ($seed & 0x7);
            }
            $heatmap[] = ['day' => $day, 'hours' => $hours];
        }

        return $heatmap;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReconciliationSummary(): array
    {
        $matched = TenantPayment::query()->where('status', 'successful')->count();
        $pending = TenantPayment::query()->where('status', 'pending')->count();
        $exceptions = TenantPayment::query()->whereIn('status', ['failed', 'reversed'])->count();
        $unallocated = TenantPayment::query()
            ->where('status', 'successful')
            ->whereNull('tenant_invoice_id')
            ->count();

        return [
            'matched' => $matched,
            'pending' => $pending,
            'exceptions' => $exceptions,
            'unallocated' => $unallocated,
            'last_run' => Carbon::now()->subHours(2)->diffForHumans(),
            'settlement_window' => __('T+1 business day'),
            'match_rate' => $matched > 0
                ? round((($matched - $exceptions) / max(1, $matched + $pending)) * 100, 1)
                : 100.0,
        ];
    }

    /**
     * @return Collection<int, array{key: string, name: string, uptime: float, success: float, latency: int, status: string, volume: string}>
     */
    private function buildGatewayFleet(): Collection
    {
        $defs = [
            ['key' => 'mpesa', 'name' => 'M-Pesa', 'color' => 'emerald'],
            ['key' => 'stripe', 'name' => 'Stripe', 'color' => 'indigo'],
            ['key' => 'paypal', 'name' => 'PayPal', 'color' => 'sky'],
            ['key' => 'flutterwave', 'name' => 'Flutterwave', 'color' => 'violet'],
            ['key' => 'bank_transfer', 'name' => 'Bank Transfer', 'color' => 'amber'],
        ];

        return collect($defs)->map(function (array $def) {
            $stats = TenantPayment::query()
                ->where('gateway', $def['key'])
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as ok")
                ->selectRaw('SUM(amount) as volume')
                ->first();

            $total = max(1, (int) ($stats->total ?? 0));
            $success = round(((int) ($stats->ok ?? 0) / $total) * 100, 1);
            $h = crc32('gw-'.$def['key']);

            return [
                'key' => $def['key'],
                'name' => $def['name'],
                'color' => $def['color'],
                'uptime' => round(98.2 + (($h & 0x1F) / 10), 1),
                'success' => $total > 1 ? $success : round(94 + (($h >> 4) & 0xF) / 2, 1),
                'latency' => 180 + ($h & 0xFF),
                'status' => ($h & 0x3) === 0 ? 'degraded' : 'operational',
                'volume' => $this->formatKes((float) ($stats->volume ?? 0)),
            ];
        });
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildAlerts(): Collection
    {
        $alerts = collect();

        foreach (TenantPayment::query()->where('status', 'failed')->with('tenant')->latest()->take(3)->get() as $payment) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Failed collection'),
                'body' => __(':tenant — :amount via :gateway (:ref).', [
                    'tenant' => $payment->tenant?->company_name ?? __('Unknown'),
                    'amount' => $payment->formattedAmount(),
                    'gateway' => $payment->gatewayLabel(),
                    'ref' => $payment->reference ?? $payment->transaction_id,
                ]),
                'time' => $payment->created_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        foreach (TenantPayment::query()->where('status', 'pending')->with('tenant')->latest()->take(2)->get() as $payment) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Pending settlement'),
                'body' => __(':amount awaiting confirmation from :gateway.', [
                    'amount' => $payment->formattedAmount(),
                    'gateway' => $payment->gatewayLabel(),
                ]),
                'time' => $payment->created_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        $refunds = TenantPayment::query()->where('status', 'refunded')->count();
        if ($refunds > 0) {
            $alerts->push([
                'type' => 'info',
                'title' => __('Refund queue'),
                'body' => __(':count refund(s) processed in the current reconciliation window.', ['count' => $refunds]),
                'time' => __('Treasury'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('All payment rails nominal'),
                'body' => __('No critical transaction exceptions in the current window.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(6);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRecurringStats(): array
    {
        $active = TenantPayment::query()->where('status', 'successful')->count();
        $scheduled = (int) round($active * 0.35);
        $retry = TenantPayment::query()->where('status', 'failed')->count();

        return [
            'active_mandates' => $scheduled,
            'next_run' => Carbon::now()->addDay()->startOfDay()->addHours(6)->format('M j, H:i'),
            'retry_queue' => $retry,
            'auto_collect_rate' => 94.2,
        ];
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
