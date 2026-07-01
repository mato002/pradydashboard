<table class="items" width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="c">#</th>
            <th>{{ __('Description') }}</th>
            <th class="r">{{ __('Qty') }}</th>
            <th class="r">{{ __('Unit') }}</th>
            <th class="r">{{ __('Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lineItems as $i => $line)
            <tr>
                <td class="c muted">{{ $i + 1 }}</td>
                <td>{{ $line['description'] ?? '' }}</td>
                <td class="r mono">{{ $fmt((float) ($line['quantity'] ?? 0)) }}</td>
                <td class="r mono">{{ $fmt((float) ($line['unit_price'] ?? 0)) }}</td>
                <td class="r mono">{{ $fmt((float) ($line['line_total'] ?? 0)) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
