<table class="pc-stmt-ledger" width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Ref') }}</th>
            <th>{{ __('Description') }}</th>
            <th class="r">{{ __('Debit') }}</th>
            <th class="r">{{ __('Credit') }}</th>
            <th class="r">{{ __('Balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($statementRows as $row)
            <tr>
                <td>{{ isset($row['date']) ? \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') : '—' }}</td>
                <td>{{ $row['reference'] ?? '—' }}</td>
                <td>{{ $row['description'] ?? '' }}</td>
                <td class="r">{{ (float) ($row['debit'] ?? 0) > 0 ? $fmtKsh((float) $row['debit']) : '—' }}</td>
                <td class="r">{{ (float) ($row['credit'] ?? 0) > 0 ? $fmtKsh((float) $row['credit']) : '—' }}</td>
                <td class="r">{{ $fmtKsh((float) ($row['balance'] ?? 0)) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="pc-ledger" width="100%" cellpadding="0" cellspacing="0" style="margin-top:0;border-top:none;">
    <tfoot>
        <tr>
            <td colspan="2" class="pc-pay-cell">&nbsp;</td>
            <td colspan="2" class="pc-totals-cell">
                <div class="pc-total-line">
                    <span class="pc-total-label">{{ __('Opening Balance') }}:</span>
                    <span class="pc-total-amount">{{ $fmtKsh((float) ($s['opening_balance'] ?? ($s['statement']['opening_balance'] ?? 0))) }}</span>
                </div>
                <div class="pc-total-line pc-balance-line">
                    <span class="pc-total-label">{{ __('Closing Balance') }}:</span>
                    <span class="pc-total-amount">{{ $fmtKsh((float) ($s['closing_balance'] ?? ($s['statement']['closing_balance'] ?? 0))) }}</span>
                </div>
            </td>
        </tr>
    </tfoot>
</table>
