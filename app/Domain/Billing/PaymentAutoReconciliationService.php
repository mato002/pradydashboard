<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use Illuminate\Support\Str;

class PaymentAutoReconciliationService
{
    public function __construct(
        private readonly PaymentReconciliationService $reconciliation,
    ) {}

    public function tryReconcile(TenantPayment $payment): bool
    {
        $payment = $payment->fresh();

        if ($payment->status !== 'successful') {
            return false;
        }

        if (in_array($payment->reconciliation_status, [
            PaymentReconciliationStatus::MATCHED,
            PaymentReconciliationStatus::PARTIALLY_MATCHED,
            PaymentReconciliationStatus::IGNORED,
            PaymentReconciliationStatus::DUPLICATE,
        ], true)) {
            return false;
        }

        $invoice = $this->findInvoiceForPayment($payment);
        if ($invoice === null) {
            return false;
        }

        try {
            $this->reconciliation->matchToInvoice($payment, $invoice);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function findInvoiceForPayment(TenantPayment $payment): ?TenantInvoice
    {
        if ($payment->tenant_id) {
            $byReference = $this->findByInvoiceReference($payment, (int) $payment->tenant_id);
            if ($byReference !== null) {
                return $byReference;
            }
        }

        if (! $payment->tenant_id) {
            return null;
        }

        $open = TenantInvoice::query()
            ->where('tenant_id', $payment->tenant_id)
            ->where('document_type', 'invoice')
            ->whereIn('status', TenantInvoice::OPEN_STATUSES)
            ->orderBy('due_date')
            ->get()
            ->filter(fn (TenantInvoice $invoice) => $invoice->balanceDue() > 0.009);

        $amount = (float) $payment->amount;

        $exact = $open->first(fn (TenantInvoice $invoice) => abs($invoice->balanceDue() - $amount) <= 0.02);
        if ($exact !== null) {
            return $exact;
        }

        return $open->sortBy(fn (TenantInvoice $invoice) => abs($invoice->balanceDue() - $amount))->first();
    }

    private function findByInvoiceReference(TenantPayment $payment, int $tenantId): ?TenantInvoice
    {
        $haystack = Str::upper(implode(' ', array_filter([
            $payment->reference,
            $payment->narration,
            $payment->notes,
        ])));

        if ($haystack === '') {
            return null;
        }

        $candidates = TenantInvoice::query()
            ->where('tenant_id', $tenantId)
            ->where('document_type', 'invoice')
            ->whereIn('status', TenantInvoice::OPEN_STATUSES)
            ->orderByDesc('issue_date')
            ->limit(25)
            ->get();

        foreach ($candidates as $invoice) {
            if (Str::contains($haystack, Str::upper($invoice->invoice_number))) {
                return $invoice;
            }
        }

        return null;
    }
}
