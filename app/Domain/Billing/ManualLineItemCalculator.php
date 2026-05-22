<?php

namespace App\Domain\Billing;

/**
 * Server-side line totals for manual financial documents.
 */
class ManualLineItemCalculator
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{
     *   lines: array<int, array<string, mixed>>,
     *   subtotal: float,
     *   discount_amount: float,
     *   tax_amount: float,
     *   total: float
     * }
     */
    public function compute(array $lines): array
    {
        $computed = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($lines as $line) {
            $qty = max(0, (float) ($line['quantity'] ?? 0));
            $unit = max(0, (float) ($line['unit_price'] ?? 0));
            $discount = max(0, (float) ($line['discount'] ?? 0));
            $taxRate = max(0, (float) ($line['tax_rate'] ?? 0));

            $lineSubtotal = max(0, ($qty * $unit) - $discount);
            $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $computed[] = array_merge($line, [
                'quantity' => $qty,
                'unit_price' => $unit,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ]);

            $subtotal += $lineSubtotal;
            $discountTotal += $discount;
            $taxTotal += $lineTax;
        }

        $subtotal = round($subtotal, 2);
        $taxTotal = round($taxTotal, 2);
        $total = round($subtotal + $taxTotal, 2);

        return [
            'lines' => $computed,
            'subtotal' => $subtotal,
            'discount_amount' => round($discountTotal, 2),
            'tax_amount' => $taxTotal,
            'total' => $total,
        ];
    }
}
