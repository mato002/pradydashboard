<?php

namespace App\Http\Controllers;

use App\Domain\Billing\PublicBillingCheckoutService;
use App\Domain\Licensing\TenantLicenseBillingContext;
use App\Domain\Billing\BillingSettings;
use App\Domain\Settings\PlatformSettingsService;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
            'stkAvailable' => filled(config('payment_gateway.admin_token'))
                && filled($tenant->payments_gateway_tenant_uuid),
            'stkAction' => URL::signedRoute('billing.pay.stk', ['tenant' => $tenant->id]),
        ]);
    }

    public function initiateStk(Request $request, Tenant $tenant, PublicBillingCheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'invoice_id' => ['nullable', 'integer', 'exists:tenant_invoices,id'],
        ]);

        $invoice = null;
        if (! empty($data['invoice_id'])) {
            $invoice = TenantInvoice::query()
                ->where('tenant_id', $tenant->id)
                ->whereKey($data['invoice_id'])
                ->firstOrFail();
        }

        $result = $checkout->initiateStkPush($tenant, $data['phone'], $invoice);

        return redirect()
            ->back()
            ->with('status', $result['message']);
    }
}
