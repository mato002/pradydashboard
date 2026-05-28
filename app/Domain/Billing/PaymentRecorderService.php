<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\ActivityLogCategory;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Billing\PaymentSource;
use App\Support\Cache\OperationalCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentRecorderService
{
    public function __construct(
        private readonly InvoicePaymentRecorder $invoiceRecorder,
        private readonly ActivityLogger $activityLogger,
        private readonly OperationalCache $operationalCache,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordForInvoice(TenantInvoice $invoice, array $data): TenantPayment
    {
        $reference = isset($data['reference']) ? (string) $data['reference'] : null;
        $lockName = $this->operationalCache->paymentReferenceLockKey((int) $invoice->tenant_id, $reference);

        if ($lockName !== null) {
            $result = $this->operationalCache->lock(
                $lockName,
                config('redis_cache.locks.payment_reference', 30),
                fn () => $this->performRecordForInvoice($invoice, $data),
            );

            if ($result === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reference' => [__('A payment with this reference is already being processed.')],
                ]);
            }

            return $result;
        }

        return $this->performRecordForInvoice($invoice, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function performRecordForInvoice(TenantInvoice $invoice, array $data): TenantPayment
    {
        $source = (string) ($data['source'] ?? $data['method'] ?? PaymentSource::MANUAL);
        if (! in_array($source, PaymentSource::all(), true)) {
            $source = PaymentSource::MANUAL;
        }

        $balanceBefore = $invoice->balanceDue();
        $payAmount = (float) $data['amount'];

        $payment = $this->invoiceRecorder->record($invoice, [
            'amount' => $payAmount,
            'payment_date' => $data['payment_date'] ?? $data['paid_at'] ?? now(),
            'method' => $data['method'] ?? $source,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $applied = min($payAmount, $balanceBefore > 0.009 ? $balanceBefore : $payAmount);

        $payment->update([
            'source' => $source,
            'gateway' => $data['gateway'] ?? $source,
            'payer_name' => $data['payer_name'] ?? $invoice->clientDisplayName(),
            'payer_phone' => $data['payer_phone'] ?? $invoice->tenant?->billing_phone ?? $invoice->manual_client_phone,
            'payer_email' => $data['payer_email'] ?? $invoice->tenant?->billing_email ?? $invoice->manual_client_email,
            'bank_source' => $data['bank_source'] ?? null,
            'narration' => $data['narration'] ?? null,
            'reconciliation_status' => PaymentReconciliationStatus::MATCHED,
            'matched_at' => now(),
            'matched_by' => Auth::id(),
            'recorded_by' => Auth::id(),
            'transaction_id' => $data['transaction_id'] ?? $payment->transaction_id ?? ('TXN-'.strtoupper(uniqid())),
        ]);

        $payment->allocations()->create([
            'tenant_invoice_id' => $invoice->id,
            'amount' => $applied,
        ]);

        $overpayment = max(0, round($payAmount - $applied, 2));
        if ($overpayment > 0.009) {
            $payment->update(['unapplied_amount' => $overpayment]);
        }

        $this->activityLogger->log(
            'payment.recorded',
            ActivityLogCategory::BILLING,
            __('Payment :amount recorded on invoice :number', [
                'amount' => $payment->formattedAmount(),
                'number' => $invoice->invoice_number,
            ]),
            $invoice,
            null,
            ['payment_id' => $payment->id],
        );

        return $payment->fresh(['allocations', 'invoice', 'tenant']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordUnreconciled(array $data): TenantPayment
    {
        $source = (string) ($data['source'] ?? PaymentSource::MANUAL);
        if (! in_array($source, PaymentSource::all(), true)) {
            $source = PaymentSource::MANUAL;
        }

        $tenant = ! empty($data['tenant_id'])
            ? Tenant::query()->find((int) $data['tenant_id'])
            : null;

        return DB::transaction(function () use ($data, $source, $tenant): TenantPayment {
            $payment = TenantPayment::query()->create([
                'transaction_id' => $data['transaction_id'] ?? ('TXN-'.strtoupper(uniqid())),
                'tenant_id' => $tenant?->id,
                'tenant_invoice_id' => null,
                'source' => $source,
                'gateway' => $data['gateway'] ?? $source,
                'payer_name' => $data['payer_name'] ?? $tenant?->company_name,
                'payer_phone' => $data['payer_phone'] ?? $tenant?->billing_phone,
                'payer_email' => $data['payer_email'] ?? $tenant?->billing_email,
                'amount' => $data['amount'],
                'unapplied_amount' => $data['amount'],
                'currency' => $data['currency'] ?? $tenant?->billing_preferred_currency ?? $tenant?->tenant_currency ?? 'KES',
                'status' => 'successful',
                'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
                'paid_at' => $data['payment_date'] ?? $data['paid_at'] ?? now(),
                'method' => $data['method'] ?? $source,
                'reference' => $data['reference'] ?? null,
                'bank_source' => $data['bank_source'] ?? null,
                'narration' => $data['narration'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $this->activityLogger->log(
                'payment.recorded',
                ActivityLogCategory::BILLING,
                __('Unreconciled payment :amount from :payer', [
                    'amount' => $payment->formattedAmount(),
                    'payer' => $payment->payer_name ?? __('Unknown'),
                ]),
                $payment,
            );

            return $payment;
        });
    }
}
