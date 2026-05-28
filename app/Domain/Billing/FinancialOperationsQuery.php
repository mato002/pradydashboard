<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogQuery;
use App\Domain\Rbac\RbacScopeFilter;
use App\Models\BillingAutomationRule;
use App\Models\CollectionNote;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\InvoiceRecurringSchedule;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use App\Support\Billing\BillingDocumentType;
use App\Support\Billing\CollectionNoteOutcome;
use App\Support\Billing\CollectionNoteStatus;
use App\Support\Cache\OperationalCache;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FinancialOperationsQuery
{
    public function __construct(
        private readonly BillingSummary $billingSummary,
        private readonly OverdueBillingProcessor $overdueProcessor,
        private readonly ActivityLogQuery $activityQuery,
        private readonly OperationalCache $operationalCache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function overviewKpis(RbacScopeFilter $scopeFilter): array
    {
        $scopeKey = $this->operationalCache->scopeFingerprint($scopeFilter);

        return $this->operationalCache->remember(
            'financial',
            'overview-kpis',
            config('redis_cache.ttl.financial_overview', 180),
            fn () => $this->computeOverviewKpis($scopeFilter),
            $scopeKey,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function computeOverviewKpis(RbacScopeFilter $scopeFilter): array
    {
        $billing = $this->billingSummary->platform($scopeFilter);
        $currency = $billing['currency'];
        $invoiceScope = $this->scopedInvoices($scopeFilter);

        $totalInvoiced = (float) (clone $invoiceScope)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->get()
            ->sum(fn (TenantInvoice $inv) => $inv->invoiceTotal());

        $paidInvoices = (clone $invoiceScope)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->where('status', 'paid')
            ->count();

        $overdueInvoices = (clone $invoiceScope)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->where('status', 'overdue')
            ->count();

        $failedCollections = (clone $invoiceScope)->where('collection_failed', true)->count();
        $collectionRate = $this->collectionEfficiency($scopeFilter);
        $arr = round($billing['mrr'] * 12, 2);

        $rules = BillingAutomationRule::platform();
        $graceExposure = (float) (clone $invoiceScope)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereIn('status', ['overdue', 'partial', 'partially_paid', 'sent', 'pending'])
            ->where('due_date', '>=', now()->subDays($rules->grace_period_days))
            ->where('due_date', '<', now())
            ->get()
            ->sum(fn (TenantInvoice $i) => $i->balanceDue());

        $suspensionRisk = $this->scopedTenants($scopeFilter)
            ->whereIn('status', ['overdue', 'warning'])
            ->orWhereHas('invoices', fn ($q) => $q->where('status', 'overdue'))
            ->count();

        $forecast = $this->revenueForecast($scopeFilter);

        return [
            'totalInvoiced' => TenantInvoice::formatMoney($totalInvoiced, $currency),
            'totalInvoicedRaw' => $totalInvoiced,
            'paid' => $paidInvoices,
            'overdue' => $overdueInvoices,
            'outstanding' => TenantInvoice::formatMoney($billing['unpaid_balance'], $currency),
            'outstandingRaw' => $billing['unpaid_balance'],
            'monthRevenue' => TenantInvoice::formatMoney($billing['paid_this_month'], $currency),
            'monthRevenueRaw' => $billing['paid_this_month'],
            'failedCollections' => $failedCollections,
            'collectionRate' => $collectionRate,
            'mrr' => TenantInvoice::formatMoney($billing['mrr'], $currency),
            'mrrRaw' => $billing['mrr'],
            'arr' => TenantInvoice::formatMoney($arr, $currency),
            'arrRaw' => $arr,
            'revenueForecast' => TenantInvoice::formatMoney($forecast, $currency),
            'revenueForecastRaw' => $forecast,
            'graceExposure' => TenantInvoice::formatMoney($graceExposure, $currency),
            'graceExposureRaw' => $graceExposure,
            'suspensionRisk' => $suspensionRisk,
            'currency' => $currency,
            'invoicesDue' => $billing['invoices_due'],
            'overdueAmount' => TenantInvoice::formatMoney($billing['overdue_amount'], $currency),
            'billingRiskTenants' => $billing['billing_risk_tenants'],
        ];
    }

    /**
     * @return LengthAwarePaginator<TenantInvoice>
     */
    public function invoiceRegister(Request $request, RbacScopeFilter $scopeFilter, ?string $documentType = null): LengthAwarePaginator
    {
        $query = $this->scopedInvoices($scopeFilter)
            ->with(['tenant.project', 'projectSubscription.project', 'generatedDocuments']);

        if ($documentType) {
            $query->where('document_type', $documentType);
        } elseif ($request->filled('type')) {
            $query->where('document_type', $request->string('type'));
        } else {
            $query->whereIn('document_type', BillingDocumentType::registerTypes());
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->filled('project_id')) {
            $query->whereHas('projectSubscription', fn ($q) => $q->where('product_id', $request->integer('project_id')));
        }

        if ($request->boolean('overdue')) {
            $query->where('status', 'overdue');
        }

        if ($request->boolean('unpaid')) {
            $query->whereNotIn('status', ['paid', 'cancelled', 'void']);
        }

        if ($request->boolean('paid')) {
            $query->where('status', 'paid');
        }

        if ($request->boolean('recurring')) {
            $query->where('is_recurring', true);
        }

        if ($request->boolean('draft')) {
            $query->where('status', 'draft');
        }

        if ($request->boolean('suspended')) {
            $query->whereHas('tenant', fn ($q) => $q->where('status', 'suspended'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date('date_to'));
        }

        return $query
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{tenant: string, balance: string, balance_raw: float, overdue_count: int}>
     */
    public function topDebtors(RbacScopeFilter $scopeFilter, int $limit = 5): Collection
    {
        return (clone $this->scopedInvoices($scopeFilter))
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->with('tenant')
            ->get()
            ->groupBy('tenant_id')
            ->map(function (Collection $invoices) {
                $tenant = $invoices->first()?->tenant;
                $balance = $invoices->sum(fn (TenantInvoice $i) => $i->balanceDue());

                return [
                    'tenant' => $tenant?->company_name ?? __('Unknown'),
                    'balance' => TenantInvoice::formatMoney($balance, $invoices->first()?->currency),
                    'balance_raw' => $balance,
                    'overdue_count' => $invoices->where('status', 'overdue')->count(),
                ];
            })
            ->sortByDesc('balance_raw')
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function upcomingRenewals(RbacScopeFilter $scopeFilter, int $days = 30): Collection
    {
        $until = now()->addDays($days);

        return $this->scoped($scopeFilter, TenantProjectSubscription::query())
            ->where('product_status', 'active')
            ->whereNotNull('renewal_date')
            ->whereBetween('renewal_date', [now()->startOfDay(), $until])
            ->with(['tenant', 'project'])
            ->orderBy('renewal_date')
            ->limit(8)
            ->get()
            ->map(fn ($sub) => [
                'tenant' => $sub->tenant?->company_name,
                'project' => $sub->project?->name,
                'renewal_date' => $sub->renewal_date?->format('M j, Y'),
                'monthly_fee' => TenantInvoice::formatMoney((float) $sub->monthly_fee, $sub->tenant?->tenant_currency),
            ]);
    }

    /**
     * @return Collection<int, TenantInvoice>
     */
    public function failedDeliveries(RbacScopeFilter $scopeFilter): Collection
    {
        return (clone $this->scopedInvoices($scopeFilter))
            ->where(function ($q): void {
                $q->where('delivery_status', 'failed')
                    ->orWhere(fn ($q2) => $q2->where('status', 'sent')->whereNull('email_delivered_at')->whereNotNull('finalized_at'));
            })
            ->with('tenant')
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, TenantProjectSubscription>
     */
    public function expiringSubscriptions(RbacScopeFilter $scopeFilter): Collection
    {
        return $this->scoped($scopeFilter, TenantProjectSubscription::query())
            ->where(function ($q): void {
                $q->whereIn('license_status', ['expiring', 'trial'])
                    ->orWhere(function ($q2): void {
                        $q2->whereNotNull('renewal_date')
                            ->where('renewal_date', '<=', now()->addDays(14));
                    });
            })
            ->with(['tenant', 'project'])
            ->orderBy('renewal_date')
            ->limit(8)
            ->get();
    }

    public function schedules(RbacScopeFilter $scopeFilter): Collection
    {
        return $this->scoped($scopeFilter, InvoiceRecurringSchedule::query())
            ->with('tenant')
            ->orderBy('next_run_at')
            ->get();
    }

    public function templates(): Collection
    {
        return DocumentTemplate::query()->orderBy('type')->orderByDesc('is_default')->get();
    }

    public function automationRules(): BillingAutomationRule
    {
        return BillingAutomationRule::platform();
    }

    /**
     * @return Collection<int, TenantInvoice>
     */
    public function collectionsData(RbacScopeFilter $scopeFilter): Collection
    {
        return $this->collectionsOverview($scopeFilter)['overdue'];
    }

    /**
     * @return array<string, mixed>
     */
    public function collectionsOverview(RbacScopeFilter $scopeFilter): array
    {
        $open = $this->scopedOpenInvoices($scopeFilter);
        $rules = BillingAutomationRule::platform();
        $suspensionThreshold = (int) $rules->suspension_after_days + (int) $rules->grace_period_days;

        $withBalance = $open->filter(fn (TenantInvoice $i) => $i->balanceDue() > 0.009);

        $overdue = $withBalance->filter(fn (TenantInvoice $i) => $i->due_date && $i->due_date->isPast())->values();

        $dueSoon = $withBalance->filter(function (TenantInvoice $i): bool {
            if (! $i->due_date || $i->due_date->isPast()) {
                return false;
            }

            return $i->due_date->lte(now()->addDays(7)->endOfDay());
        })->values();

        $unpaidSent = $withBalance->where('status', 'sent')->values();

        $partiallyPaid = $withBalance->whereIn('status', ['partial', 'partially_paid'])->values();

        $suspensionCandidates = $overdue
            ->filter(fn (TenantInvoice $i) => $i->due_date && $i->due_date->diffInDays(now()->startOfDay()) >= $suspensionThreshold)
            ->groupBy('tenant_id')
            ->map(fn (Collection $group) => [
                'tenant' => $group->first()?->tenant?->company_name ?? __('Unknown'),
                'tenant_id' => $group->first()?->tenant_id,
                'balance' => $group->sum(fn (TenantInvoice $inv) => $inv->balanceDue()),
                'invoice_count' => $group->count(),
                'max_days_overdue' => $group->max(fn (TenantInvoice $inv) => $inv->due_date?->diffInDays(now()->startOfDay()) ?? 0),
            ])
            ->sortByDesc('balance')
            ->values();

        $tenantIds = $withBalance->pluck('tenant_id')->filter()->unique()->values();
        $noteQuery = CollectionNote::query()
            ->with(['invoice.tenant', 'user'])
            ->when(
                ! $scopeFilter->isGlobalScope(),
                fn ($q) => $q->where(function ($inner) use ($tenantIds): void {
                    $inner->whereIn('tenant_id', $tenantIds)
                        ->orWhereHas('invoice', fn ($iq) => $iq->whereIn('tenant_id', $tenantIds));
                }),
            );

        $promisedPayments = (clone $noteQuery)
            ->open()
            ->where('outcome', CollectionNoteOutcome::PROMISED_PAYMENT)
            ->orderBy('promise_to_pay_date')
            ->limit(25)
            ->get();

        $overdueFollowUps = (clone $noteQuery)
            ->open()
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', now()->toDateString())
            ->orderBy('follow_up_date')
            ->limit(25)
            ->get();

        return [
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'unpaid_sent' => $unpaidSent,
            'partially_paid' => $partiallyPaid,
            'top_debtors' => $this->topDebtors($scopeFilter, 8),
            'aging_buckets' => $this->collectionsAgingBuckets($scopeFilter),
            'promised_payments' => $promisedPayments,
            'overdue_follow_ups' => $overdueFollowUps,
            'suspension_candidates' => $suspensionCandidates,
        ];
    }

    /**
     * Collections aging: days overdue.
     *
     * @return array<int, array{label: string, key: string, amount: float, count: int, pct: float}>
     */
    public function collectionsAgingBuckets(RbacScopeFilter $scopeFilter): array
    {
        $open = $this->scopedOpenInvoices($scopeFilter)
            ->filter(fn (TenantInvoice $i) => $i->due_date && $i->due_date->isPast() && $i->balanceDue() > 0.009);

        $buckets = [
            '0_7' => ['label' => __('0–7 days'), 'key' => '0_7', 'amount' => 0.0, 'count' => 0],
            '8_14' => ['label' => __('8–14 days'), 'key' => '8_14', 'amount' => 0.0, 'count' => 0],
            '15_30' => ['label' => __('15–30 days'), 'key' => '15_30', 'amount' => 0.0, 'count' => 0],
            '31_60' => ['label' => __('31–60 days'), 'key' => '31_60', 'amount' => 0.0, 'count' => 0],
            '60_plus' => ['label' => __('60+ days'), 'key' => '60_plus', 'amount' => 0.0, 'count' => 0],
        ];

        foreach ($open as $invoice) {
            $days = (int) $invoice->due_date->diffInDays(now()->startOfDay());
            $key = match (true) {
                $days <= 7 => '0_7',
                $days <= 14 => '8_14',
                $days <= 30 => '15_30',
                $days <= 60 => '31_60',
                default => '60_plus',
            };
            $buckets[$key]['amount'] += $invoice->balanceDue();
            $buckets[$key]['count']++;
        }

        $total = max(1, array_sum(array_column($buckets, 'amount')));

        return array_map(function (array $bucket) use ($total): array {
            return [
                'label' => $bucket['label'],
                'key' => $bucket['key'],
                'amount' => round($bucket['amount'], 2),
                'count' => $bucket['count'],
                'pct' => round(($bucket['amount'] / $total) * 100, 1),
            ];
        }, array_values($buckets));
    }

    /** @return Collection<int, TenantInvoice> */
    private function scopedOpenInvoices(RbacScopeFilter $scopeFilter): Collection
    {
        $query = (clone $this->scopedInvoices($scopeFilter))
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'draft'])
            ->with(['tenant', 'projectSubscription.project']);

        if (! $scopeFilter->isGlobalScope()) {
            $query->whereIn('tenant_id', $this->scopedTenants($scopeFilter)->pluck('id'));
        }

        return $query->get();
    }

    /**
     * @return Collection<int, CollectionNote>
     */
    public function recentCollectionNotes(RbacScopeFilter $scopeFilter, int $limit = 15): Collection
    {
        $tenantIds = $this->scopedTenants($scopeFilter)->pluck('id');

        return CollectionNote::query()
            ->with(['invoice.tenant', 'user'])
            ->when(
                ! $scopeFilter->isGlobalScope(),
                fn ($q) => $q->where(function ($inner) use ($tenantIds): void {
                    $inner->whereIn('tenant_id', $tenantIds)
                        ->orWhereHas('invoice', fn ($iq) => $iq->whereIn('tenant_id', $tenantIds));
                }),
            )
            ->whereIn('status', [CollectionNoteStatus::OPEN, CollectionNoteStatus::COMPLETED])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function activityLogs(RbacScopeFilter $scopeFilter, int $limit = 30): Collection
    {
        return $this->activityQuery->recent($limit, ['category' => 'billing']);
    }

    /**
     * @return array<int, array{label: string, issued: int, paid: int}>
     */
    public function invoiceTrendSeries(RbacScopeFilter $scopeFilter): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $issued = (clone $this->scopedInvoices($scopeFilter))
                ->where('document_type', BillingDocumentType::INVOICE)
                ->whereBetween('issued_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $paid = (clone $this->scopedInvoices($scopeFilter))
                ->where('document_type', BillingDocumentType::INVOICE)
                ->where('status', 'paid')
                ->whereBetween('updated_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $series[] = [
                'label' => $month->format('M'),
                'issued' => $issued,
                'paid' => min($paid, max(1, $issued)),
            ];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    public function revenueSeries(RbacScopeFilter $scopeFilter): array
    {
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $value = (float) $this->scoped($scopeFilter, TenantPayment::query())
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $series[] = ['label' => $month->format('M'), 'value' => round($value, 0)];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, amount: float, count: int, pct: float}>
     */
    public function agingBuckets(RbacScopeFilter $scopeFilter): array
    {
        $open = (clone $this->scopedInvoices($scopeFilter))
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereIn('status', ['pending', 'overdue', 'partial', 'partially_paid', 'sent'])
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

            $buckets[$key]['amount'] += $invoice->balanceDue();
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
    public function automationStats(RbacScopeFilter $scopeFilter): array
    {
        $invoiceScope = $this->scopedInvoices($scopeFilter);
        $total = max(1, (clone $invoiceScope)->where('document_type', BillingDocumentType::INVOICE)->count());
        $scheduleScope = $this->scoped($scopeFilter, InvoiceRecurringSchedule::query());

        return [
            'recurring_active' => (clone $scheduleScope)->where('enabled', true)->count(),
            'recurring_total' => (clone $scheduleScope)->count(),
            'pdf_rate' => round(((clone $invoiceScope)->where('pdf_generated', true)->count() / $total) * 100, 1),
            'email_rate' => round(((clone $invoiceScope)->whereNotNull('email_delivered_at')->count() / $total) * 100, 1),
            'reminder_queue' => (clone $invoiceScope)->whereIn('status', ['overdue', 'partial', 'partially_paid'])->count(),
            'documents_generated' => GeneratedDocument::query()->count(),
        ];
    }

    /**
     * @return Collection<int, array{type: string, title: string, body: string, time: string}>
     */
    public function alerts(RbacScopeFilter $scopeFilter): Collection
    {
        $alerts = collect();

        foreach ((clone $this->scopedInvoices($scopeFilter))->where('status', 'overdue')->with('tenant')->latest('due_date')->take(3)->get() as $inv) {
            $alerts->push([
                'type' => 'danger',
                'title' => __('Overdue invoice'),
                'body' => __(':tenant — :number · :amount', [
                    'tenant' => $inv->tenant?->company_name,
                    'number' => $inv->invoice_number,
                    'amount' => $inv->formattedBalance(),
                ]),
                'time' => $inv->due_date?->format('M j') ?? __('Open'),
            ]);
        }

        foreach ($this->failedDeliveries($scopeFilter)->take(2) as $inv) {
            $alerts->push([
                'type' => 'warning',
                'title' => __('Delivery failed'),
                'body' => __(':number — :tenant', [
                    'number' => $inv->invoice_number,
                    'tenant' => $inv->tenant?->company_name,
                ]),
                'time' => __('Email'),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => __('Receivables healthy'),
                'body' => __('No critical financial alerts in the current window.'),
                'time' => __('Now'),
            ]);
        }

        return $alerts->take(8);
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function filterTenants(RbacScopeFilter $scopeFilter): Collection
    {
        return $this->scopedTenants($scopeFilter)->orderBy('company_name')->get(['id', 'company_name']);
    }

    private function revenueForecast(RbacScopeFilter $scopeFilter): float
    {
        $mrr = $this->billingSummary->platform($scopeFilter)['mrr'];
        $openPipeline = (clone $this->scopedInvoices($scopeFilter))
            ->where('document_type', BillingDocumentType::QUOTATION)
            ->where('approval_status', 'approved')
            ->whereNull('converted_invoice_id')
            ->sum('total');

        return round($mrr + (float) $openPipeline, 2);
    }

    private function collectionEfficiency(RbacScopeFilter $scopeFilter): float
    {
        $query = $this->scopedInvoices($scopeFilter)->where('document_type', BillingDocumentType::INVOICE);
        $total = (clone $query)->whereNot('status', 'cancelled')->count();
        if ($total === 0) {
            return 100.0;
        }

        $collected = (clone $query)->whereIn('status', ['paid', 'partial', 'partially_paid'])->count();

        return round(($collected / $total) * 100, 1);
    }

    /** @return Builder<TenantInvoice> */
    private function scopedInvoices(RbacScopeFilter $scopeFilter): Builder
    {
        return $scopeFilter->applyTenantForeignScope(TenantInvoice::query());
    }

    /** @return Builder<Tenant> */
    private function scopedTenants(RbacScopeFilter $scopeFilter): Builder
    {
        if ($scopeFilter->isGlobalScope()) {
            return Tenant::query();
        }

        return $scopeFilter->applyTenantScope(Tenant::query());
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scoped(RbacScopeFilter $scopeFilter, Builder $query, string $tenantColumn = 'tenant_id'): Builder
    {
        if ($scopeFilter->isGlobalScope()) {
            return $query;
        }

        return $scopeFilter->applyTenantForeignScope($query, $tenantColumn);
    }
}
