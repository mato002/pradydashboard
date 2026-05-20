@php $s = $snapshot; @endphp
<table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
        <tr style="background:#4f46e5;color:#fff;">
            <th style="text-align:left;padding:8px;">{{ __('Description') }}</th>
            <th style="text-align:right;padding:8px;">{{ __('Qty') }}</th>
            <th style="text-align:right;padding:8px;">{{ __('Unit') }}</th>
            <th style="text-align:right;padding:8px;">{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($s['line_items'] ?? [] as $line)
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;">{{ $line['description'] }}</td>
                <td style="padding:8px;text-align:right;">{{ number_format($line['quantity'], 2) }}</td>
                <td style="padding:8px;text-align:right;">{{ number_format($line['unit_price'], 2) }}</td>
                <td style="padding:8px;text-align:right;">{{ number_format($line['line_total'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
