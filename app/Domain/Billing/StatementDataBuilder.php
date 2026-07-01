<?php

namespace App\Domain\Billing;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Support\Carbon;

class StatementDataBuilder
{
    /**
     * @return array{
     *     period_start: string,
     *     period_end: string,
     *     opening_balance: float,
     *     closing_balance: float,
     *     rows: list<array<string, mixed>>
     * }
     */
    public function build(Tenant $tenant, Carbon $periodStart, Carbon $periodEnd): array
    {
        $openingBalance = $this->openingBalance($tenant, $periodStart);
        $rows = $this->buildRows($tenant, $periodStart, $periodEnd, $openingBalance);
        $closingBalance = $rows !== []
            ? (float) $rows[array_key_last($rows)]['balance']
            : $openingBalance;

        return [
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildFromInvoice(TenantInvoice $invoice): ?array
    {
        if ($invoice->document_type !== BillingDocumentType::STATEMENT || ! $invoice->tenant) {
            return null;
        }

        $start = $invoice->statement_period_start
            ? Carbon::parse($invoice->statement_period_start)
            : optional($invoice->issue_date)?->copy()->startOfMonth() ?? now()->startOfMonth();
        $end = $invoice->statement_period_end
            ? Carbon::parse($invoice->statement_period_end)
            : optional($invoice->issue_date)?->copy()->endOfMonth() ?? now()->endOfMonth();

        return $this->build($invoice->tenant, $start, $end);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRows(Tenant $tenant, Carbon $start, Carbon $end, float $runningBalance): array
    {
        $rows = [];

        if ($runningBalance != 0.0) {
            $rows[] = [
                'date' => $start->copy()->subDay()->toDateString(),
                'reference' => '—',
                'description' => __('Opening balance'),
                'debit' => $runningBalance > 0 ? $runningBalance : 0.0,
                'credit' => $runningBalance < 0 ? abs($runningBalance) : 0.0,
                'balance' => $runningBalance,
            ];
        }

        $events = collect();

        TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['cancelled', 'void', 'draft'])
            ->orderBy('issue_date')
            ->get()
            ->each(function (TenantInvoice $invoice) use ($events): void {
                $events->push([
                    'sort' => $invoice->issue_date?->format('Y-m-d').'-1-'.$invoice->id,
                    'date' => $invoice->issue_date?->toDateString(),
                    'reference' => $invoice->invoice_number,
                    'description' => __('Invoice :number', ['number' => $invoice->invoice_number]),
                    'debit' => (float) $invoice->invoiceTotal(),
                    'credit' => 0.0,
                ]);
            });

        TenantPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'successful')
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('paid_at')
            ->get()
            ->each(function (TenantPayment $payment) use ($events): void {
                $events->push([
                    'sort' => optional($payment->paid_at)->format('Y-m-d').'-2-'.$payment->id,
                    'date' => optional($payment->paid_at)->toDateString(),
                    'reference' => $payment->reference ?? $payment->transaction_id ?? '—',
                    'description' => __('Payment received'),
                    'debit' => 0.0,
                    'credit' => (float) $payment->amount,
                ]);
            });

        foreach ($events->sortBy('sort') as $event) {
            $runningBalance += (float) $event['debit'] - (float) $event['credit'];
            $rows[] = [
                'date' => $event['date'],
                'reference' => $event['reference'],
                'description' => $event['description'],
                'debit' => (float) $event['debit'],
                'credit' => (float) $event['credit'],
                'balance' => $runningBalance,
            ];
        }

        return $rows;
    }

    private function openingBalance(Tenant $tenant, Carbon $before): float
    {
        $invoiced = (float) TenantInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', BillingDocumentType::INVOICE)
            ->where('issue_date', '<', $before->toDateString())
            ->whereNotIn('status', ['cancelled', 'void', 'draft'])
            ->get()
            ->sum(fn (TenantInvoice $invoice) => $invoice->invoiceTotal());

        $paid = (float) TenantPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'successful')
            ->where('paid_at', '<', $before->copy()->startOfDay())
            ->sum('amount');

        return round($invoiced - $paid, 2);
    }
}
