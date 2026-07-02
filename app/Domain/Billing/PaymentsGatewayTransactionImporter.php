<?php

namespace App\Domain\Billing;

use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Billing\PaymentSource;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PaymentsGatewayTransactionImporter
{
    public function __construct(
        private readonly PaymentAutoReconciliationService $autoReconciliation,
    ) {}

    /**
     * @param  array<string, mixed>  $transaction
     */
    public function import(array $transaction): ?TenantPayment
    {
        $uuid = (string) ($transaction['uuid'] ?? '');
        if ($uuid === '') {
            return null;
        }

        $existing = TenantPayment::query()
            ->where('gateway_transaction_uuid', $uuid)
            ->first();

        $tenant = $this->resolveTenant($transaction);
        $status = $this->mapStatus((string) ($transaction['status'] ?? ''));
        $amount = round((float) ($transaction['amount'] ?? 0), 2);
        $reference = $this->resolveReference($transaction);
        $gateway = $this->resolveGateway($transaction);
        $paidAt = $this->resolvePaidAt($transaction, $status);

        $attributes = [
            'gateway_transaction_uuid' => $uuid,
            'transaction_id' => 'PG-'.strtoupper(substr($uuid, 0, 8)),
            'tenant_id' => $tenant?->id,
            'source' => $gateway === 'mpesa' ? PaymentSource::MPESA : PaymentSource::PAYMENTS_GATEWAY,
            'payer_phone' => Arr::get($transaction, 'phone_number'),
            'amount' => $amount,
            'unapplied_amount' => $amount,
            'currency' => strtoupper((string) ($transaction['currency'] ?? 'KES')),
            'status' => $status,
            'paid_at' => $paidAt,
            'method' => strtoupper((string) ($transaction['transaction_type'] ?? 'gateway')),
            'gateway' => $gateway,
            'reference' => $reference,
            'narration' => Arr::get($transaction, 'result_desc')
                ?? Arr::get($transaction, 'internal_reference')
                ?? Arr::get($transaction, 'account_reference'),
            'notes' => __('Imported from Payments Gateway webhook/sync.'),
        ];

        if ($existing !== null) {
            $existing->fill($attributes);

            if ($existing->reconciliation_status === null) {
                $existing->reconciliation_status = PaymentReconciliationStatus::UNRECONCILED;
            }

            if ($existing->isDirty()) {
                $existing->save();
            }

            $payment = $existing->fresh();

            $this->maybeAutoReconcile($payment);

            return $payment;
        }

        $payment = TenantPayment::query()->create(array_merge($attributes, [
            'reconciliation_status' => PaymentReconciliationStatus::UNRECONCILED,
        ]));

        $this->maybeAutoReconcile($payment);

        return $payment;
    }

    private function maybeAutoReconcile(?TenantPayment $payment): void
    {
        if ($payment === null || ! config('payment_gateway.auto_reconcile_enabled', true)) {
            return;
        }

        if ($payment->status !== 'successful') {
            return;
        }

        $this->autoReconciliation->tryReconcile($payment);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function resolveTenant(array $transaction): ?Tenant
    {
        $tenantUuid = (string) ($transaction['tenant_uuid'] ?? '');

        if ($tenantUuid === '') {
            return null;
        }

        return Tenant::query()
            ->where('payments_gateway_tenant_uuid', $tenantUuid)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function resolveReference(array $transaction): ?string
    {
        foreach (['mpesa_receipt_number', 'external_reference', 'internal_reference', 'checkout_request_id', 'account_reference'] as $key) {
            $value = Arr::get($transaction, $key);
            if (filled($value)) {
                return (string) $value;
            }
        }

        $metaInvoice = Arr::get($transaction, 'callback_metadata.invoice_number');
        if (filled($metaInvoice)) {
            return (string) $metaInvoice;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function resolveGateway(array $transaction): string
    {
        $type = strtolower((string) ($transaction['transaction_type'] ?? ''));

        if (str_contains($type, 'mpesa') || filled($transaction['mpesa_receipt_number'] ?? null)) {
            return 'mpesa';
        }

        return 'payments_gateway';
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'success', 'successful', 'completed', 'paid' => 'successful',
            'pending', 'processing', 'queued' => 'pending',
            'failed', 'cancelled', 'timeout', 'reversed' => 'failed',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function resolvePaidAt(array $transaction, string $status): ?Carbon
    {
        if ($status !== 'successful') {
            return null;
        }

        foreach (['processed_at', 'created_at', 'paid_at'] as $key) {
            $value = Arr::get($transaction, $key);
            if (filled($value)) {
                return Carbon::parse($value);
            }
        }

        return now();
    }
}
