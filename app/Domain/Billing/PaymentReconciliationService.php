<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\CollectionNote;
use App\Models\PaymentAllocation;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\ActivityLogCategory;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Cache\OperationalCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentReconciliationService
{
    public function __construct(
        private readonly InvoicePaymentRecorder $invoiceRecorder,
        private readonly ReceiptGenerator $receiptGenerator,
        private readonly ActivityLogger $activityLogger,
        private readonly OperationalCache $operationalCache,
    ) {}

    public function findDuplicate(TenantPayment $payment): ?TenantPayment
    {
        if (! $payment->reference) {
            return null;
        }

        return TenantPayment::query()
            ->where('id', '!=', $payment->id)
            ->where('reference', $payment->reference)
            ->where('amount', $payment->amount)
            ->where('reconciliation_status', '!=', PaymentReconciliationStatus::IGNORED)
            ->where('paid_at', '>=', optional($payment->paid_at)->subDays(30) ?? now()->subDays(30))
            ->first();
    }

    /**
     * @return array{payment: TenantPayment, receipt: TenantInvoice|null}
     */
    public function matchToInvoice(TenantPayment $payment, TenantInvoice $invoice, ?float $amount = null): array
    {
        if (in_array($payment->reconciliation_status, [PaymentReconciliationStatus::IGNORED, PaymentReconciliationStatus::DUPLICATE], true)) {
            throw ValidationException::withMessages([
                'payment' => [__('Payment cannot be matched in its current state.')],
            ]);
        }

        $locks = ['payment:reconcile:'.$payment->id];
        $referenceLock = $this->operationalCache->paymentReferenceLockKey(
            $payment->tenant_id ? (int) $payment->tenant_id : ($invoice->tenant_id ? (int) $invoice->tenant_id : null),
            $payment->reference,
        );
        if ($referenceLock !== null) {
            $locks[] = $referenceLock;
        }

        return $this->withPaymentLocks($locks, fn () => $this->performMatchToInvoice($payment, $invoice, $amount));
    }

    /**
     * @return array{payment: TenantPayment, receipt: TenantInvoice|null}
     */
    private function performMatchToInvoice(TenantPayment $payment, TenantInvoice $invoice, ?float $amount = null): array
    {
        return DB::transaction(function () use ($payment, $invoice, $amount): array {
            $payment = $payment->fresh(['allocations']);
            $invoice = $invoice->fresh(['lineItems', 'tenant']);
            $allocate = $amount ?? $payment->remainingToAllocate();

            if ($allocate <= 0.009) {
                throw ValidationException::withMessages([
                    'amount' => [__('No remaining amount to allocate on this payment.')],
                ]);
            }

            $balanceBefore = $invoice->balanceDue();
            $apply = min($allocate, $balanceBefore > 0.009 ? $balanceBefore : $allocate);

            if ($payment->tenant_id && $invoice->tenant_id && (int) $payment->tenant_id !== (int) $invoice->tenant_id) {
                throw ValidationException::withMessages([
                    'invoice' => [__('Payment tenant does not match invoice tenant.')],
                ]);
            }

            $this->applyAllocation($payment, $invoice, $apply);

            $receipt = null;
            $invoice->refresh();
            if ($invoice->status === 'paid' && $invoice->document_type === 'invoice') {
                $receipt = $this->receiptGenerator->generateForPayment($invoice->fresh(), $payment->fresh());
            }

            $this->refreshPaymentReconciliationState($payment);
            $this->noteOverdueCollection($invoice);

            $this->activityLogger->log(
                'payment.matched',
                ActivityLogCategory::BILLING,
                __('Payment :ref matched to invoice :number (:amount)', [
                    'ref' => $payment->displayId(),
                    'number' => $invoice->invoice_number,
                    'amount' => TenantInvoice::formatMoney($apply, $invoice->currency),
                ]),
                $invoice,
                null,
                ['payment_id' => $payment->id, 'amount' => $apply],
            );

            return ['payment' => $payment->fresh(['allocations', 'tenant']), 'receipt' => $receipt];
        });
    }

    /**
     * @param  list<string>  $lockNames
     * @param  \Closure(): array{payment: TenantPayment, receipt: TenantInvoice|null}  $callback
     * @return array{payment: TenantPayment, receipt: TenantInvoice|null}
     */
    private function withPaymentLocks(array $lockNames, \Closure $callback): array
    {
        $seconds = config('redis_cache.locks.payment_reconcile', 30);

        if (count($lockNames) === 1) {
            $result = $this->operationalCache->lock($lockNames[0], $seconds, $callback);
            if ($result === null) {
                throw ValidationException::withMessages([
                    'payment' => [__('Payment reconciliation is already in progress.')],
                ]);
            }

            return $result;
        }

        return $this->operationalCache->lock(
            $lockNames[0],
            $seconds,
            fn () => $this->withPaymentLocks(array_slice($lockNames, 1), $callback),
        ) ?? throw ValidationException::withMessages([
            'payment' => [__('Payment reconciliation is already in progress.')],
        ]);
    }

    /**
     * @param  array<int, array{invoice_id: int, amount: float}>  $lines
     */
    public function splitAcrossInvoices(TenantPayment $payment, array $lines): TenantPayment
    {
        return DB::transaction(function () use ($payment, $lines): TenantPayment {
            $payment = $payment->fresh(['allocations']);
            $total = round(array_sum(array_column($lines, 'amount')), 2);

            if ($total > $payment->remainingToAllocate() + 0.01) {
                throw ValidationException::withMessages([
                    'allocations' => [__('Split total exceeds available payment amount.')],
                ]);
            }

            foreach ($lines as $line) {
                $invoice = TenantInvoice::query()->findOrFail((int) $line['invoice_id']);
                $this->matchToInvoice($payment, $invoice, (float) $line['amount']);
                $payment = $payment->fresh(['allocations']);
            }

            $this->activityLogger->log(
                'payment.split',
                ActivityLogCategory::BILLING,
                __('Payment :ref split across :count invoice(s)', [
                    'ref' => $payment->displayId(),
                    'count' => count($lines),
                ]),
                $payment,
            );

            return $payment->fresh(['allocations', 'tenant']);
        });
    }

    public function markDuplicate(TenantPayment $payment, ?string $note = null): TenantPayment
    {
        $duplicate = $this->findDuplicate($payment);

        $payment->update([
            'reconciliation_status' => PaymentReconciliationStatus::DUPLICATE,
            'notes' => trim(($payment->notes ?? '')."\n".($note ?? __('Flagged as duplicate.'))),
        ]);

        $this->activityLogger->log(
            'payment.duplicate_flagged',
            ActivityLogCategory::BILLING,
            __('Payment :ref flagged as duplicate', ['ref' => $payment->displayId()]),
            $payment,
            null,
            ['duplicate_of' => $duplicate?->id],
        );

        return $payment->fresh();
    }

    public function markIgnored(TenantPayment $payment, ?string $note = null): TenantPayment
    {
        $payment->update([
            'reconciliation_status' => PaymentReconciliationStatus::IGNORED,
            'notes' => trim(($payment->notes ?? '')."\n".($note ?? __('Ignored by admin.'))),
        ]);

        $this->activityLogger->log(
            'payment.ignored',
            ActivityLogCategory::BILLING,
            __('Payment :ref ignored', ['ref' => $payment->displayId()]),
            $payment,
        );

        return $payment->fresh();
    }

    public function reverse(TenantPayment $payment): TenantPayment
    {
        return DB::transaction(function () use ($payment): TenantPayment {
            $payment = $payment->fresh(['allocations.invoice']);

            foreach ($payment->allocations as $allocation) {
                $invoice = $allocation->invoice;
                if (! $invoice) {
                    continue;
                }

                $invoice->amount_paid = max(0, (float) $invoice->amount_paid - (float) $allocation->amount);
                $invoice->syncPaymentStatus();
                if ($invoice->status === 'draft') {
                    $invoice->status = 'sent';
                }
                $invoice->save();
            }

            $payment->allocations()->delete();

            if ($payment->tenant_invoice_id) {
                $inv = $payment->invoice;
                if ($inv) {
                    $paid = (float) $inv->payments()->where('id', '!=', $payment->id)->sum('amount');
                    $inv->amount_paid = $paid;
                    $inv->syncPaymentStatus();
                    $inv->save();
                }
            }

            $payment->update([
                'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
                'tenant_invoice_id' => null,
                'unapplied_amount' => $payment->amount,
                'matched_at' => null,
                'matched_by' => null,
                'status' => 'reversed',
            ]);

            $this->activityLogger->log(
                'payment.reversed',
                ActivityLogCategory::BILLING,
                __('Payment :ref reconciliation reversed', ['ref' => $payment->displayId()]),
                $payment,
            );

            return $payment->fresh();
        });
    }

    private function applyAllocation(TenantPayment $payment, TenantInvoice $invoice, float $amount): void
    {
        $payment->update([
            'tenant_id' => $payment->tenant_id ?? $invoice->tenant_id,
            'tenant_invoice_id' => $invoice->id,
            'status' => 'successful',
        ]);

        PaymentAllocation::query()->create([
            'tenant_payment_id' => $payment->id,
            'tenant_invoice_id' => $invoice->id,
            'amount' => $amount,
        ]);

        $invoice->refresh();
        $invoice->amount_paid = (float) $invoice->payments()->sum('amount');
        $invoice->syncPaymentStatus();
        $invoice->save();
    }

    private function refreshPaymentReconciliationState(TenantPayment $payment): void
    {
        $payment = $payment->fresh(['allocations']);
        $allocated = $payment->allocatedAmount();
        $unapplied = max(0, round((float) $payment->amount - $allocated, 2));

        $status = PaymentReconciliationStatus::MATCHED;
        if ($unapplied > 0.009 && $allocated > 0.009) {
            $status = PaymentReconciliationStatus::PARTIALLY_MATCHED;
        } elseif ($allocated <= 0.009) {
            $status = PaymentReconciliationStatus::UNRECONCILED;
        }

        $payment->update([
            'reconciliation_status' => $status,
            'unapplied_amount' => $unapplied,
            'matched_at' => now(),
            'matched_by' => Auth::id(),
        ]);
    }

    private function noteOverdueCollection(TenantInvoice $invoice): void
    {
        if (! in_array($invoice->status, ['paid', 'partially_paid', 'partial'], true)) {
            return;
        }

        if ($invoice->due_date && $invoice->due_date->isPast()) {
            CollectionNote::query()->create([
                'tenant_invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'user_id' => Auth::id(),
                'note_type' => 'payment',
                'body' => __('Payment reconciled for overdue invoice.'),
                'note' => __('Payment reconciled for overdue invoice.'),
                'status' => 'completed',
                'outcome' => 'paid',
            ]);
        }
    }
}
