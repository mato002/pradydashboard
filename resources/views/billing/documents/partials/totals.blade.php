@php
    $s = $snapshot;
    $currency = $s['currency'] ?? 'KES';
    $amountPaid = (float) ($s['amount_paid'] ?? 0);
    $balanceDue = (float) ($s['balance_due'] ?? max(0, (float) ($s['total'] ?? 0) - $amountPaid));
@endphp
<section style="margin-top:16px;text-align:right;font-size:13px;">
    <p>{{ __('Subtotal') }}: {{ $currency }} {{ number_format($s['subtotal'] ?? 0, 2) }}</p>
    @if ((float) ($s['discount_amount'] ?? 0) > 0)
        <p>{{ __('Discount') }}: − {{ number_format((float) $s['discount_amount'], 2) }}</p>
    @endif
    <p>{{ __('Tax') }}: {{ $currency }} {{ number_format($s['tax_amount'] ?? 0, 2) }}</p>
    <p style="font-size:16px;font-weight:700;">{{ __('Total') }}: {{ $currency }} {{ number_format($s['total'] ?? 0, 2) }}</p>
    @if ($amountPaid > 0)
        <p style="color:#059669;">{{ __('Paid') }}: {{ $currency }} {{ number_format($amountPaid, 2) }}</p>
        <p style="font-size:15px;font-weight:700;">{{ __('Balance') }}: {{ $currency }} {{ number_format($balanceDue, 2) }}</p>
    @endif
</section>
