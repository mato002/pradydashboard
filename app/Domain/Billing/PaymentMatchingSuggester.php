<?php

namespace App\Domain\Billing;

use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Collection;

class PaymentMatchingSuggester
{
    /**
     * @return Collection<int, array{invoice: TenantInvoice, score: int, reasons: list<string>}>
     */
    public function suggest(TenantPayment $payment): Collection
    {
        $payment->loadMissing(['tenant', 'allocations']);

        $query = TenantInvoice::query()
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereNotIn('status', ['paid', 'cancelled', 'void', 'draft'])
            ->with('tenant');

        if ($payment->tenant_id) {
            $query->where('tenant_id', $payment->tenant_id);
        }

        $candidates = $query->get()->filter(fn (TenantInvoice $i) => $i->balanceDue() > 0.009);

        $scored = $candidates->map(function (TenantInvoice $invoice) use ($payment): array {
            $score = 0;
            $reasons = [];

            if ($payment->tenant_id && (int) $invoice->tenant_id === (int) $payment->tenant_id) {
                $score += 30;
                $reasons[] = __('Same tenant');
            }

            $balance = $invoice->balanceDue();
            $amount = (float) $payment->amount;
            if (abs($balance - $amount) < 0.02) {
                $score += 40;
                $reasons[] = __('Amount matches balance');
            } elseif ($amount < $balance) {
                $score += 15;
                $reasons[] = __('Partial amount match');
            }

            $ref = strtolower((string) ($payment->reference ?? '').' '.($payment->narration ?? ''));
            if ($ref !== '' && str_contains($ref, strtolower($invoice->invoice_number))) {
                $score += 35;
                $reasons[] = __('Reference mentions invoice number');
            }

            if ($payment->payer_email && $invoice->tenant?->billing_email
                && strcasecmp($payment->payer_email, $invoice->tenant->billing_email) === 0) {
                $score += 20;
                $reasons[] = __('Email matches tenant');
            }

            if ($payment->payer_phone && $invoice->tenant?->billing_phone
                && $this->phonesMatch($payment->payer_phone, $invoice->tenant->billing_phone)) {
                $score += 20;
                $reasons[] = __('Phone matches tenant');
            }

            if ($payment->payer_email && $invoice->manual_client_email
                && strcasecmp($payment->payer_email, $invoice->manual_client_email) === 0) {
                $score += 15;
                $reasons[] = __('Email matches manual client');
            }

            return [
                'invoice' => $invoice,
                'score' => $score,
                'reasons' => $reasons,
            ];
        })->filter(fn (array $row) => $row['score'] > 0)
            ->sortByDesc('score')
            ->values();

        return $scored->take(10);
    }

    private function phonesMatch(string $a, string $b): bool
    {
        $norm = fn (string $p) => preg_replace('/\D+/', '', $p) ?? '';

        $na = $norm($a);
        $nb = $norm($b);

        if ($na === '' || $nb === '') {
            return false;
        }

        return str_ends_with($na, substr($nb, -9)) || str_ends_with($nb, substr($na, -9));
    }
}
