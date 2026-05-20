@php $s = $snapshot; @endphp
<section style="margin-top:16px;text-align:right;font-size:13px;">
    <p>{{ __('Subtotal') }}: {{ $s['currency'] }} {{ number_format($s['subtotal'] ?? 0, 2) }}</p>
    <p>{{ __('Tax') }}: {{ $s['currency'] }} {{ number_format($s['tax_amount'] ?? 0, 2) }}</p>
    <p style="font-size:16px;font-weight:700;">{{ __('Total') }}: {{ $s['currency'] }} {{ number_format($s['total'] ?? 0, 2) }}</p>
</section>
