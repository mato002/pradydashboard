<?php

namespace App\Domain\Billing;

use App\Domain\Rbac\RbacScopeFilter;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentReconciliationQuery
{
    /**
     * @return array<string, mixed>
     */
    public function inboxKpis(RbacScopeFilter $scopeFilter): array
    {
        $base = $this->scoped($scopeFilter, TenantPayment::query());

        $unreconciled = (clone $base)
            ->where('reconciliation_status', PaymentReconciliationStatus::UNRECONCILED)
            ->where('status', 'successful');

        $matchedToday = (clone $base)
            ->whereIn('reconciliation_status', [
                PaymentReconciliationStatus::MATCHED,
                PaymentReconciliationStatus::PARTIALLY_MATCHED,
            ])
            ->whereDate('matched_at', today());

        $month = (clone $base)->where('status', 'successful')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $recentMatched = (clone $base)
            ->whereNotNull('matched_at')
            ->where('created_at', '>=', now()->subDays(90))
            ->latest('matched_at')
            ->limit(50)
            ->get(['created_at', 'matched_at']);

        $avgHours = $recentMatched->isEmpty()
            ? 0
            : $recentMatched->avg(fn (TenantPayment $p) => $p->created_at->diffInHours($p->matched_at));

        return [
            'unreconciled_count' => (clone $unreconciled)->count(),
            'unreconciled_amount' => (float) (clone $unreconciled)->sum('amount'),
            'matched_today' => $matchedToday->count(),
            'duplicates' => (clone $base)->where('reconciliation_status', PaymentReconciliationStatus::DUPLICATE)->count(),
            'ignored' => (clone $base)->where('reconciliation_status', PaymentReconciliationStatus::IGNORED)->count(),
            'payments_this_month' => $month->count(),
            'month_collected' => (float) $month->sum('amount'),
            'avg_reconciliation_hours' => round((float) ($avgHours ?? 0), 1),
        ];
    }

    /**
     * @return LengthAwarePaginator<TenantPayment>
     */
    public function inbox(Request $request, RbacScopeFilter $scopeFilter): LengthAwarePaginator
    {
        $query = $this->scoped($scopeFilter, TenantPayment::query())
            ->with(['tenant', 'invoice', 'allocations.invoice', 'matchedByUser'])
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at');

        if ($request->filled('reconciliation_status')) {
            $query->where('reconciliation_status', $request->string('reconciliation_status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source'));
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->boolean('unreconciled_only')) {
            $query->where('reconciliation_status', PaymentReconciliationStatus::UNRECONCILED);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date('date_to'));
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @param  Builder<TenantPayment>  $query */
    private function scoped(RbacScopeFilter $scopeFilter, Builder $query): Builder
    {
        if ($scopeFilter->isGlobalScope()) {
            return $query;
        }

        return $scopeFilter->applyTenantForeignScope($query, 'tenant_id');
    }
}
