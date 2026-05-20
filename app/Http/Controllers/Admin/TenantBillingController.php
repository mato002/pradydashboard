<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Domain\Billing\BillingSummary;
use App\Domain\Billing\DraftInvoiceGenerator;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantBillingController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function updateProfile(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $data = $request->validate([
            'billing_contact_name' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'billing_phone' => ['nullable', 'string', 'max:80'],
            'billing_address' => ['nullable', 'string', 'max:2000'],
            'billing_tax_pin' => ['nullable', 'string', 'max:80'],
            'billing_preferred_currency' => ['nullable', 'string', 'size:3'],
            'billing_payment_terms' => ['nullable', 'string', 'max:80'],
            'billing_tax_exempt' => ['sometimes', 'boolean'],
            'billing_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['billing_tax_exempt'] = $request->boolean('billing_tax_exempt');

        $tenant->update($data);

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing'])
            ->with('status', __('Billing profile updated.'));
    }

    public function generateDraft(Request $request, Tenant $tenant, DraftInvoiceGenerator $generator): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $subscriptionId = $request->filled('subscription')
            ? (int) $request->input('subscription')
            : null;

        $result = $generator->generate($tenant, $subscriptionId);

        if (! $result) {
            return redirect()
                ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing'])
                ->with('error', __('No billable items found for this tenant.'));
        }

        $invoice = $result['invoice'];

        $this->activityLogger->log(
            'invoice.draft_generated',
            ActivityLogCategory::BILLING,
            __('Draft invoice :number generated for :tenant', [
                'number' => $invoice->invoice_number,
                'tenant' => $tenant->company_name,
            ]),
            $invoice,
            null,
            ['status' => 'draft', 'invoice_number' => $invoice->invoice_number],
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', __('Draft invoice :number created.', ['number' => $invoice->invoice_number]));
    }
}
