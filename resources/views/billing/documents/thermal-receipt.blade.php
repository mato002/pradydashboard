@php $s = $snapshot; $b = $branding; @endphp
<div style="font-family:monospace;max-width:320px;margin:0 auto;font-size:12px;">
    <p style="text-align:center;font-weight:bold;">{{ $b['company_name'] ?? config('app.name') }}</p>
    <p style="text-align:center;">{{ $s['invoice_number'] }}</p>
    <hr>
    @foreach ($s['line_items'] ?? [] as $line)
        <p>{{ $line['description'] }}<br>{{ number_format($line['line_total'], 2) }}</p>
    @endforeach
    <hr>
    <p style="text-align:right;font-weight:bold;">{{ __('TOTAL') }} {{ $s['currency'] }} {{ number_format($s['total'] ?? 0, 2) }}</p>
</div>
