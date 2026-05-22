<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Billing\PaymentMatchingSuggester;
use App\Domain\Billing\PaymentRecorderService;
use App\Domain\Billing\PaymentReconciliationQuery;
use App\Domain\Billing\PaymentReconciliationService;
use App\Domain\Rbac\RbacScopeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPaymentRequest;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReconciliationController extends Controller
{
    public function suggestions(TenantPayment $payment, PaymentMatchingSuggester $suggester): JsonResponse
    {
        $rows = $suggester->suggest($payment)->map(fn (array $row) => [
            'invoice_id' => $row['invoice']->id,
            'invoice_number' => $row['invoice']->invoice_number,
            'tenant' => $row['invoice']->tenant?->company_name ?? $row['invoice']->manual_client_name,
            'balance' => $row['invoice']->formattedBalance(),
            'score' => $row['score'],
            'reasons' => $row['reasons'],
        ]);

        return response()->json(['suggestions' => $rows]);
    }

    public function store(RecordPaymentRequest $request, PaymentRecorderService $recorder): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['tenant_invoice_id'])) {
            $invoice = TenantInvoice::query()->findOrFail((int) $data['tenant_invoice_id']);
            $recorder->recordForInvoice($invoice, $data);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('status', __('Payment recorded and matched to invoice.'));
        }

        $payment = $recorder->recordUnreconciled($data);
        $duplicate = app(PaymentReconciliationService::class)->findDuplicate($payment);
        if ($duplicate) {
            return redirect()
                ->route('invoices.index', ['tab' => 'payments', 'reconciliation_status' => 'unreconciled'])
                ->with('error', __('Possible duplicate of payment :ref. Review before matching.', ['ref' => $duplicate->displayId()]));
        }

        return redirect()
            ->route('invoices.index', ['tab' => 'payments'])
            ->with('status', __('Payment recorded to inbox as unreconciled.'));
    }

    public function match(
        Request $request,
        TenantPayment $payment,
        PaymentReconciliationService $reconciliation,
    ): RedirectResponse {
        $data = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:tenant_invoices,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $invoice = TenantInvoice::query()->findOrFail((int) $data['invoice_id']);
        $result = $reconciliation->matchToInvoice(
            $payment,
            $invoice,
            isset($data['amount']) ? (float) $data['amount'] : null,
        );

        $message = __('Payment matched to :number.', ['number' => $invoice->invoice_number]);
        if ($result['receipt']) {
            $message .= ' '.__('Receipt :number generated.', ['number' => $result['receipt']->invoice_number]);
        }

        return back()->with('status', $message);
    }

    public function split(Request $request, TenantPayment $payment, PaymentReconciliationService $reconciliation): RedirectResponse
    {
        $data = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required', 'integer', 'exists:tenant_invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $rows = collect($data['allocations'])
            ->filter(fn (array $row) => ! empty($row['invoice_id']) && (float) ($row['amount'] ?? 0) > 0)
            ->values()
            ->all();

        if ($rows === []) {
            return back()->withErrors(['allocations' => __('Add at least one invoice allocation.')]);
        }

        $lines = array_map(fn (array $row) => [
            'invoice_id' => (int) $row['invoice_id'],
            'amount' => (float) $row['amount'],
        ], $rows);

        $reconciliation->splitAcrossInvoices($payment, $lines);

        return back()->with('status', __('Payment split across invoices.'));
    }

    public function duplicate(TenantPayment $payment, PaymentReconciliationService $reconciliation): RedirectResponse
    {
        $reconciliation->markDuplicate($payment, request('notes'));

        return back()->with('status', __('Payment marked as duplicate.'));
    }

    public function ignore(TenantPayment $payment, PaymentReconciliationService $reconciliation): RedirectResponse
    {
        $reconciliation->markIgnored($payment, request('notes'));

        return back()->with('status', __('Payment ignored.'));
    }

    public function reverse(TenantPayment $payment, PaymentReconciliationService $reconciliation): RedirectResponse
    {
        $reconciliation->reverse($payment);

        return back()->with('status', __('Payment reconciliation reversed.'));
    }
}
