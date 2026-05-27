<?php

namespace App\Http\Controllers;

use App\Domain\Billing\BillingSettings;
use App\Domain\Licensing\TenantLicenseBillingContext;
use App\Domain\Settings\PlatformSettingsService;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTenantBillingController extends Controller
{
    public function show(Request $request, Tenant $tenant): View
    {
        $billing = app(TenantLicenseBillingContext::class)->forTenant($tenant);
        $openInvoices = TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', TenantInvoice::OPEN_STATUSES)
            ->where('document_type', 'invoice')
            ->orderBy('due_date')
            ->get();

        $platformBilling = (new BillingSettings)->all();
        $company = (new PlatformSettingsService)->all()['company'] ?? [];

        return view('public.billing.pay', [
            'tenant' => $tenant,
            'billing' => $billing,
            'invoices' => $openInvoices,
            'platformBilling' => $platformBilling,
            'company' => $company,
        ]);
    }
}
