<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Billing\PaymentReconciliationStatus;
use Illuminate\Support\Collection;

class PaymentInboxPresenter
{
    public function __construct(
        private readonly PaymentMatchingSuggester $suggester,
        private readonly PaymentReconciliationService $reconciliation,
    ) {}

    /**
     * @param  Collection<int, TenantPayment>  $payments
     * @return array<int, array{suggestions: list<array<string, mixed>>, duplicate: ?TenantPayment}>
     */
    public function metaForPayments(Collection $payments): array
    {
        $meta = [];

        foreach ($payments as $payment) {
            $meta[$payment->id] = [
                'suggestions' => $this->suggestionsFor($payment),
                'duplicate' => $this->reconciliation->findDuplicate($payment),
            ];
        }

        return $meta;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function suggestionsFor(TenantPayment $payment): array
    {
        if ($payment->reconciliation_status !== PaymentReconciliationStatus::UNRECONCILED) {
            return [];
        }

        $paymentAmount = (float) $payment->amount;

        return $this->suggester->suggest($payment)
            ->take(5)
            ->map(function (array $row) use ($paymentAmount): array {
                /** @var TenantInvoice $invoice */
                $invoice = $row['invoice'];
                $balance = $invoice->balanceDue();
                $isPartial = $paymentAmount < $balance - 0.01;
                $suggestedAmount = $isPartial
                    ? min($paymentAmount, $balance)
                    : min($paymentAmount, $balance > 0.009 ? $balance : $paymentAmount);

                return [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant' => $invoice->tenant?->company_name ?? $invoice->manual_client_name ?? '—',
                    'balance' => $invoice->formattedBalance(),
                    'balance_raw' => $balance,
                    'due_date' => $invoice->due_date?->format('M j, Y') ?? '—',
                    'score' => $row['score'],
                    'reasons' => $row['reasons'],
                    'is_partial' => $isPartial,
                    'suggested_amount' => round($suggestedAmount, 2),
                ];
            })
            ->values()
            ->all();
    }
}
