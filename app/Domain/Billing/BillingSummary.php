<?php

namespace App\Domain\Billing;

use App\Domain\Rbac\RbacScopeFilter;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Models\TenantProjectSubscription;
use Illuminate\Database\Eloquent\Builder;

class BillingSummary
{
    public function __construct(
        private readonly BillingSettings $settings,
        private readonly DraftInvoiceGenerator $draftGenerator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function platform(?RbacScopeFilter $scopeFilter = null): array
    {
        $mrr = (float) $this->scoped($scopeFilter, TenantProjectSubscription::query(), 'tenant_id')
            ->where('product_status', 'active')
            ->whereNotIn('license_status', ['suspended', 'expired', 'disabled'])
            ->sum('monthly_fee');

        $openInvoices = $this->scoped($scopeFilter, TenantInvoice::query())
            ->whereNotIn('status', ['cancelled', 'void', 'paid'])
            ->get();

        $invoicesDue = $openInvoices->filter(fn (TenantInvoice $i) => $i->due_date && $i->due_date->gte(now()->startOfDay()))->count();

        $overdueAmount = (float) $openInvoices
            ->filter(fn (TenantInvoice $i) => $i->status === 'overdue' || ($i->due_date && $i->due_date->isPast()))
            ->sum(fn (TenantInvoice $i) => $i->balanceDue());

        $paidThisMonth = (float) $this->scoped($scopeFilter, TenantPayment::query())
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        if ($paidThisMonth <= 0) {
            $paidThisMonth = (float) $this->scoped($scopeFilter, TenantInvoice::query())
                ->where('status', 'paid')
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_paid');
        }

        $unpaidBalance = (float) $openInvoices->sum(fn (TenantInvoice $i) => $i->balanceDue());

        $billingRiskTenants = $this->scopedTenants($scopeFilter)
            ->where(function ($query): void {
                $query->whereIn('status', ['overdue', 'warning'])
                    ->orWhereHas('invoices', function ($q): void {
                        $q->where('status', 'overdue');
                    });
            })
            ->count();

        return [
            'mrr' => $mrr,
            'currency' => $this->settings->defaultCurrency(),
            'invoices_due' => $invoicesDue,
            'overdue_amount' => $overdueAmount,
            'paid_this_month' => $paidThisMonth,
            'unpaid_balance' => $unpaidBalance,
            'billing_risk_tenants' => $billingRiskTenants,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $tenant->loadMissing(['projectSubscriptions.moduleSubscriptions.projectModule', 'projectSubscriptions.serviceIntegrations', 'invoices']);

        $mrr = (float) $tenant->projectSubscriptions
            ->filter(fn ($s) => $this->draftGenerator->isBillableSubscription($s))
            ->sum(fn ($s) => (float) ($s->monthly_fee ?? 0));

        $openInvoices = $tenant->invoices->whereNotIn('status', ['cancelled', 'void', 'paid']);

        return [
            'mrr' => $mrr,
            'currency' => $tenant->billing_preferred_currency ?? $tenant->tenant_currency ?? $this->settings->defaultCurrency(),
            'outstanding' => (float) $openInvoices->sum(fn (TenantInvoice $i) => $i->balanceDue()),
            'next_renewal' => $tenant->projectSubscriptions->min('renewal_date'),
            'next_due' => $openInvoices->min('due_date'),
        ];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    private function scoped(?RbacScopeFilter $scopeFilter, Builder $query, string $tenantColumn = 'tenant_id'): Builder
    {
        if (! $scopeFilter || $scopeFilter->isGlobalScope()) {
            return $query;
        }

        return $scopeFilter->applyTenantForeignScope($query, $tenantColumn);
    }

    /** @return Builder<Tenant> */
    private function scopedTenants(?RbacScopeFilter $scopeFilter): Builder
    {
        if (! $scopeFilter || $scopeFilter->isGlobalScope()) {
            return Tenant::query();
        }

        return $scopeFilter->applyTenantScope(Tenant::query());
    }
}
