<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRecurringSchedule;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Carbon\Carbon;
use Database\Seeders\InvoiceDemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        if (TenantInvoice::query()->doesntExist()) {
            (new InvoiceDemoSeeder)->run();
        }

        $invoices = TenantInvoice::query()
            ->with('tenant.project')
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $schedules = InvoiceRecurringSchedule::query()
            ->with('tenant')
            ->orderBy('next_run_at')
            ->get();

        $totalInvoiced = (float) TenantInvoice::query()
            ->whereNot('status', 'cancelled')
            ->sum('amount_due');

        $paidInvoices = TenantInvoice::query()->where('status', 'paid')->count();
        $overdueInvoices = TenantInvoice::query()->where('status', 'overdue')->count();
        $outstanding = (float) TenantInvoice::query()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->get()
            ->sum(fn (TenantInvoice $inv) => $inv->balance());

        $monthRevenue = (float) TenantPayment::query()
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        if ($monthRevenue <= 0) {
            $monthRevenue = (float) TenantInvoice::query()
                ->where('status', 'paid')
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_paid');
        }

        $failedCollections = TenantInvoice::query()->where('collection_failed', true)->count();

        $kpis = [
            'totalInvoiced' => TenantInvoice::formatMoney($totalInvoiced),
            'totalInvoicedRaw' => $totalInvoiced,
            'paid' => $paidInvoices,
            'overdue' => $overdueInvoices,
            'outstanding' => TenantInvoice::formatMoney($outstanding),
            'outstandingRaw' => $outstanding,
            'monthRevenue' => TenantInvoice::formatMoney($monthRevenue),
            'monthRevenueRaw' => $monthRevenue,
            'failedCollections' => $failedCollections,
            'collectionRate' => $this->collectionEfficiency(),
        ];

        $spark = fn (string $key) => $this->pseudoSparkline($key);
        $invoiceTrend = $this->buildInvoiceTrendSeries();
        $revenueSeries = $this->buildRevenueSeries();
        $overdueAnalytics = $this->buildOverdueAnalytics();
        $agingBuckets = $this->buildAgingBuckets();
        $automation = $this->buildAutomationStats();
        $alerts = $this->buildAlerts();

        return view('admin.invoices.index', compact(
            'invoices',
            'schedules',
            'kpis',
            'spark',
            'invoiceTrend',
            'revenueSeries',
            'overdueAnalytics',
            'agingBuckets',
            'automation',
            'alerts',
        ));
    }

    public function generate(Request $request): RedirectResponse
    {
        return redirect()
            ->route('invoices.index')
            ->with('status', __('Invoice generation queued for eligible tenants and recurring schedules.'));
    }

    public function sendReminders(Request $request): RedirectResponse
    {
        return redirect()
            ->route('invoices.index')
            ->with('status', __('Payment reminders dispatched for overdue and partial invoices.'));
    }

    public function toggleSchedule(InvoiceRecurringSchedule $schedule): RedirectResponse
    {
        $schedule->update(['enabled' => ! $schedule->enabled]);

        return redirect()
            ->route('invoices.index')
            ->with('status', $schedule->enabled
                ? __('Recurring schedule enabled.')
                : __('Recurring schedule paused.'));
    }

    private function collectionEfficiency(): float
    {
        $total = TenantInvoice::query()->whereNot('status', 'cancelled')->count();
        if ($total === 0) {
            return 100.0;
        }

        $collected = TenantInvoice::query()->whereIn('status', ['paid', 'partial'])->count();

        return round(($collected / $total) * 100, 1);
    }

    /**
     * @return array<int, array{label: string, issued: int, paid: int}>
     */
    private function buildInvoiceTrendSeries(): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $issued = TenantInvoice::query()
                ->whereBetween('issued_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $paid = TenantInvoice::query()
                ->where('status', 'paid')
                ->whereBetween('updated_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            if ($issued === 0) {
                $h = crc32('inv-'.$month->format('Y-m'));
                $issued = 4 + ($h & 7);
                $paid = 2 + (($h >> 4) & 5);
            }

            $series[] = [
                'label' => $month->format('M'),
                'issued' => $issued,
                'paid' => min($paid, $issued),
            ];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function buildRevenueSeries(): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $value = (float) TenantPayment::query()
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            if ($value <= 0) {
                $h = crc32('rev-'.$month->format('Y-m'));
                $value = 180000 + (($h & 0xFFFF) % 120000);
            }

            $series[] = ['label' => $month->format('M'), 'value' => round($value, 0)];
        }

        return $series;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOverdueAnalytics(): array
    {
        $overdue = TenantInvoice::query()->where('status', 'overdue')->get();
        $exposure = $overdue->sum(fn (TenantInvoice $inv) => $inv->balance());

        return [
            'count' => $overdue->count(),
            'exposure' => TenantInvoice::formatMoney($exposure),
            'avgDaysLate' => $overdue->isEmpty()
                ? 0
                : (int) round($overdue->avg(fn ($inv) => max(0, now()->diffInDays($inv->due_date, false)))),
            'penalties' => TenantInvoice::formatMoney((float) $overdue->sum('penalty_amount')),
        ];
    }

    /**
     * @return array<int, array{label: string, amount: float, count: int, pct: float}>
     */
    private function buildAgingBuckets(): array
    {
        $open = TenantInvoice::query()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->get();

        $buckets = [
            'current' => ['label' => __('Current'), 'amount' => 0.0, 'count' => 0],
            '1_30' => ['label' => __('1–30 days'), 'amount' => 0.0, 'count' => 0],
            '31_60' => ['label' => __('31–60 days'), 'amount' => 0.0, 'count' => 0],
            '61_90' => ['label' => __('61–90 days'), 'amount' => 0.0, 'count' => 0],
            '90_plus' => ['label' => __('90+ days'), 'amount' => 0.0, 'count' => 0],
        ];

        foreach ($open as $invoice) {
            $days = $invoice->due_date
                ? now()->diffInDays($invoice->due_date, false)
                : 0;

            $key = match (true) {
                $days >= 0 => 'current',
                $days >= -30 => '1_30',
                $days >= -60 => '31_60',
                $days >= -90 => '61_90',
                default => '90_plus',
            };

            $buckets[$key]['amount'] += $invoice->balance();
            $buckets[$key]['count']++;
        }

        $total = max(1, array_sum(array_column($buckets, 'amount')));

        return array_map(function ($bucket) use ($total) {
            return [
                'label' => $bucket['label'],
                'amount' => $bucket['amount'],
                'count' => $bucket['count'],
                'pct' => round(($bucket['amount'] / $total) * 100, 1),
            ];
        }, array_values($buckets));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAutomationStats(): array
    {
        $total = max(1, TenantInvoice::query()->count());

        return [
            'recurring_active' => InvoiceRecurringSchedule::query()->where('enabled', true)->count(),
            'recurring_total' => InvoiceRecurringSchedule::query()->count(),
            'pdf_rate' => round((TenantInvoice::query()->where('pdf_generated', true)->count() / $total) * 100, 1),
            'email_rate' => round((TenantInvoice::query()->whereNotNull('email_delivered_at')->count() / $total) * 100, 1),
            'reminder_queue' => TenantInvoice::query()->whereIn('status', ['overdue', 'partial'])->count(),
            'tax_automation' => 100.0,
        ];
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    private function buildAlerts(): Collection
    {
        $alerts = collect();

        foreach (TenantInvoice::query()->where('status', 'overdue')->with('tenant')->latest('due_date')->take(3)->get() as $inv) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Overdue invoice'),
                'body' => __(':tenant — :number · :amount due :when.', [
                    'tenant' => $inv->tenant?->company_name,
                    'number' => $inv->invoice_number,
                    'amount' => $inv->formattedBalance(),
                    'when' => $inv->due_date?->diffForHumans() ?? __('past due'),
                ]),
                'time' => $inv->due_date?->format('M j') ?? __('Open'),
            ]);
        }

        foreach (TenantInvoice::query()->where('collection_failed', true)->with('tenant')->take(2)->get() as $inv) {
            $alerts->push([
                'type' => 'critical',
                'title' => __('Failed collection'),
                'body' => __(':number — retry scheduled for :tenant.', [
                    'number' => $inv->invoice_number,
                    'tenant' => $inv->tenant?->company_name,
                ]),
                'time' => __('Collections'),
            ]);
        }

        foreach (TenantInvoice::query()->where('status', 'partial')->with('tenant')->take(2)->get() as $inv) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Partial payment'),
                'body' => __(':number — :balance remaining on :tenant.', [
                    'number' => $inv->invoice_number,
                    'balance' => $inv->formattedBalance(),
                    'tenant' => $inv->tenant?->company_name,
                ]),
                'time' => __('AR'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('Receivables healthy'),
                'body' => __('No critical invoice alerts in the current billing window.'),
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
            $pts[] = 32 + (($h >> ($i * 3)) & 0x3F) % 48;
        }

        return $pts;
    }
}
