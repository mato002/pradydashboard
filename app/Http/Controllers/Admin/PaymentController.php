<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Billing\ReconcilePaymentsBatchJob;
use App\Jobs\Billing\RetryFailedPaymentsJob;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\DemoMode;
use App\Support\OperationalMetrics;
use App\Models\TenantPayment;
use Carbon\Carbon;
use Database\Seeders\PaymentDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        if (DemoMode::enabled() && TenantPayment::query()->doesntExist()) {
            (new PaymentDemoSeeder)->run();
        }

        $selectedGateway = $request->string('gateway')->toString();
        $selectedStatus = $request->string('status')->toString();

        $query = $this->paymentsQuery($selectedGateway !== '' ? $selectedGateway : null)
            ->with(['tenant', 'invoice'])
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at');

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        $payments = $query->paginate(14)->withQueryString();

        $gateways = $this->buildGatewayFleet();
        $selectedGatewayLabel = $selectedGateway !== ''
            ? ($gateways->firstWhere('key', $selectedGateway)['name'] ?? ucfirst(str_replace('_', ' ', $selectedGateway)))
            : null;

        $scoped = $this->paymentsQuery($selectedGateway !== '' ? $selectedGateway : null);
        $collected = (float) (clone $scoped)->where('status', 'successful')->sum('amount');
        $failedCount = (clone $scoped)->where('status', 'failed')->count();
        $pendingCount = (clone $scoped)->where('status', 'pending')->count();
        $refundTotal = (float) (clone $scoped)->where('status', 'refunded')->sum('amount');
        $totalAttempts = max(1, (clone $scoped)->count());
        $successfulCount = (clone $scoped)->where('status', 'successful')->count();
        $collectionRate = round(($successfulCount / $totalAttempts) * 100, 1);

        $kpis = [
            'collected' => $this->formatKes($collected),
            'collectedRaw' => $collected,
            'failed' => $failedCount,
            'pending' => $pendingCount,
            'refunds' => $this->formatKes($refundTotal),
            'refundsRaw' => $refundTotal,
            'collectionRate' => $collectionRate,
            'gatewayHealth' => $this->gatewayHealthScore($selectedGateway !== '' ? $selectedGateway : null),
        ];

        $spark = fn (string $key) => OperationalMetrics::emptySparkline();
        $gatewayFilter = $selectedGateway !== '' ? $selectedGateway : null;
        $collectionSeries = $this->buildCollectionSeries($gatewayFilter);
        $gatewayAnalytics = $this->buildGatewayAnalytics($gatewayFilter);
        $heatmap = $this->buildTransactionHeatmap($gatewayFilter);
        $reconciliation = $this->buildReconciliationSummary($gatewayFilter);
        $alerts = $this->buildAlerts($gatewayFilter);
        $recurring = $this->buildRecurringStats($gatewayFilter);

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
            'selectedGateway',
            'selectedGatewayLabel',
        ));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<TenantPayment>
     */
    private function paymentsQuery(?string $gateway = null)
    {
        $query = TenantPayment::query();

        if ($gateway !== null && $gateway !== '') {
            $query->where('gateway', $gateway);
        }

        return $query;
    }

    public function reconcile(Request $request): RedirectResponse
    {
        ReconcilePaymentsBatchJob::dispatch();

        $message = __('Auto-match job dispatched for up to :limit unreconciled successful payments.', [
            'limit' => ReconcilePaymentsBatchJob::BATCH_LIMIT,
        ]);

        if (config('queue.default') !== 'sync') {
            $message .= ' '.__('A queue worker must be running for matches to apply.');
        }

        return redirect()
            ->route('payments.index')
            ->with('status', $message);
    }

    public function retryFailed(Request $request): RedirectResponse
    {
        RetryFailedPaymentsJob::dispatch();

        return redirect()
            ->route('payments.index')
            ->with('status', __('Failed transaction retry sweep initiated across gateways.'));
    }

    private function gatewayHealthScore(?string $gateway = null): float
    {
        $gateways = $this->buildGatewayFleet();

        if ($gateway) {
            $gateways = $gateways->where('key', $gateway)->values();
        }

        $measured = $gateways->filter(fn (array $gw) => $gw['uptime'] !== null);

        if ($measured->isEmpty()) {
            return 0.0;
        }

        return round($measured->avg('uptime'), 1);
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
    private function buildCollectionSeries(?string $gateway = null): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $successful = (float) $this->paymentsQuery($gateway)
                ->where('status', 'successful')
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');
            $failed = (float) $this->paymentsQuery($gateway)
                ->where('status', 'failed')
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $series[] = [
                'label' => $month->format('M'),
                'value' => $successful,
                'successful' => $successful,
                'failed' => $failed,
            ];
        }

        return $series;
    }

    /**
     * @return Collection<int, array{gateway: string, label: string, volume: float, count: int, success: float}>
     */
    private function buildGatewayAnalytics(?string $gateway = null): Collection
    {
        $labels = [
            'mpesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'flutterwave' => 'Flutterwave',
            'bank_transfer' => 'Bank Transfer',
        ];

        $rows = $this->paymentsQuery($gateway)
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
    private function buildTransactionHeatmap(?string $gateway = null): array
    {
        $payments = $this->paymentsQuery($gateway)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(7))
            ->get(['paid_at']);

        if ($payments->isEmpty()) {
            return [];
        }

        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $grid = array_fill(0, 7, array_fill(0, 24, 0));

        foreach ($payments as $payment) {
            $at = $payment->paid_at;
            $grid[$at->dayOfWeekIso - 1][$at->hour]++;
        }

        $heatmap = [];
        foreach ($days as $di => $day) {
            $heatmap[] = ['day' => $day, 'hours' => $grid[$di]];
        }

        return $heatmap;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReconciliationSummary(?string $gateway = null): array
    {
        $matched = $this->paymentsQuery($gateway)
            ->whereIn('reconciliation_status', [
                PaymentReconciliationStatus::MATCHED,
                PaymentReconciliationStatus::PARTIALLY_MATCHED,
            ])
            ->count();

        $awaitingMatch = $this->paymentsQuery($gateway)
            ->where('status', 'successful')
            ->where('reconciliation_status', PaymentReconciliationStatus::UNRECONCILED)
            ->whereNotNull('tenant_id')
            ->count();

        $exceptions = $this->paymentsQuery($gateway)
            ->where(function ($query): void {
                $query->whereIn('status', ['failed', 'reversed'])
                    ->orWhere('reconciliation_status', PaymentReconciliationStatus::DUPLICATE);
            })
            ->count();

        $unallocated = $this->paymentsQuery($gateway)
            ->where('status', 'successful')
            ->where(function ($query): void {
                $query->whereNull('tenant_invoice_id')
                    ->orWhere('unapplied_amount', '>', 0.009);
            })
            ->whereIn('reconciliation_status', [
                PaymentReconciliationStatus::UNRECONCILED,
                PaymentReconciliationStatus::PARTIALLY_MATCHED,
            ])
            ->count();

        $eligibleSuccessful = $this->paymentsQuery($gateway)
            ->where('status', 'successful')
            ->whereNotNull('tenant_id')
            ->count();

        $matchRate = $eligibleSuccessful > 0
            ? round(($matched / $eligibleSuccessful) * 100, 1)
            : 0.0;

        $lastRunMeta = Cache::get(ReconcilePaymentsBatchJob::LAST_RUN_CACHE_KEY);

        return [
            'matched' => $matched,
            'pending' => $awaitingMatch,
            'exceptions' => $exceptions,
            'unallocated' => $unallocated,
            'last_run' => $this->formatReconciliationLastRun($lastRunMeta, $gateway),
            'settlement_window' => __('Auto-match ≤:limit payments / run', [
                'limit' => ReconcilePaymentsBatchJob::BATCH_LIMIT,
            ]),
            'match_rate' => $matchRate,
        ];
    }

    /**
     * @param  array{at?: string, processed?: int, matched?: int}|null  $meta
     */
    private function formatReconciliationLastRun(?array $meta, ?string $gateway = null): string
    {
        if (! is_array($meta) || empty($meta['at'])) {
            $latestMatch = $this->paymentsQuery($gateway)
                ->whereNotNull('matched_at')
                ->orderByDesc('matched_at')
                ->value('matched_at');

            return $latestMatch
                ? Carbon::parse($latestMatch)->diffForHumans()
                : __('Never');
        }

        $label = Carbon::parse($meta['at'])->diffForHumans();
        $processed = (int) ($meta['processed'] ?? 0);
        $matched = (int) ($meta['matched'] ?? 0);

        return __(':time (:matched/:processed matched)', [
            'time' => $label,
            'matched' => $matched,
            'processed' => $processed,
        ]);
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

            $total = (int) ($stats->total ?? 0);
            $success = $total > 0 ? round(((int) ($stats->ok ?? 0) / $total) * 100, 1) : null;

            return [
                'key' => $def['key'],
                'name' => $def['name'],
                'color' => $def['color'],
                'uptime' => $success,
                'success' => $success,
                'latency' => null,
                'status' => $total === 0 ? 'unknown' : ($success >= 95 ? 'operational' : 'degraded'),
                'volume' => $this->formatKes((float) ($stats->volume ?? 0)),
                'transaction_count' => $total,
                'manage_url' => $this->gatewayManageUrl($def['key']),
            ];
        });
    }

    private function gatewayManageUrl(string $key): string
    {
        return match ($key) {
            'mpesa' => route('settings.payments-gateway.transactions.index', ['transaction_type' => 'mpesa']),
            default => route('payments.index', ['gateway' => $key]).'#ledger',
        };
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildAlerts(?string $gateway = null): Collection
    {
        $alerts = collect();

        foreach ($this->paymentsQuery($gateway)->where('status', 'failed')->with('tenant')->latest()->take(3)->get() as $payment) {
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

        foreach ($this->paymentsQuery($gateway)->where('status', 'pending')->with('tenant')->latest()->take(2)->get() as $payment) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Pending confirmation'),
                'body' => __(':amount awaiting confirmation from :gateway.', [
                    'amount' => $payment->formattedAmount(),
                    'gateway' => $payment->gatewayLabel(),
                ]),
                'time' => $payment->created_at?->diffForHumans() ?? __('Recent'),
            ]);
        }

        $refunds = $this->paymentsQuery($gateway)->where('status', 'refunded')->count();
        if ($refunds > 0) {
            $alerts->push([
                'type' => 'info',
                'title' => __('Refunds'),
                'body' => __(':count refunded payment(s) in the ledger.', ['count' => $refunds]),
                'time' => __('Ledger'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('No exceptions'),
                'body' => $gateway
                    ? __('No failed or pending payments for this gateway.')
                    : __('No failed or pending payments in the current ledger.'),
                'time' => __('Just now'),
            ]);
        }

        return $alerts->take(6);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRecurringStats(?string $gateway = null): array
    {
        $retry = $this->paymentsQuery($gateway)->where('status', 'failed')->count();
        $totalAttempts = max(1, $this->paymentsQuery($gateway)->count());
        $successfulCount = $this->paymentsQuery($gateway)->where('status', 'successful')->count();
        $nextSchedule = \App\Models\InvoiceRecurringSchedule::query()
            ->where('enabled', true)
            ->orderBy('next_run_at')
            ->value('next_run_at');

        return [
            'active_mandates' => \App\Models\InvoiceRecurringSchedule::query()->where('enabled', true)->count(),
            'next_run' => $nextSchedule?->format('M j, H:i') ?? '—',
            'retry_queue' => $retry,
            'auto_collect_rate' => round(($successfulCount / $totalAttempts) * 100, 1),
        ];
    }
}
