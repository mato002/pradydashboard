<table class="pc-ledger" width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="pc-col-num">&nbsp;</th>
            <th class="pc-col-desc">{{ __('Item Description') }}</th>
            <th class="pc-col-unit">{{ __('Unit Price') }}<br>{{ __('Shs') }}</th>
            <th class="pc-col-amt">{{ __('Total Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lineItems as $i => $line)
            <tr>
                <td class="pc-col-num c">{{ $i + 1 }}</td>
                <td class="pc-col-desc">{{ $line['description'] ?? '' }}</td>
                <td class="pc-col-unit r">{{ $fmtAmount((float) ($line['unit_price'] ?? 0)) }}</td>
                <td class="pc-col-amt r">{{ $fmtAmount((float) ($line['line_total'] ?? 0)) }}</td>
            </tr>
        @empty
            <tr>
                <td class="pc-col-num c">&nbsp;</td>
                <td class="pc-col-desc">&nbsp;</td>
                <td class="pc-col-unit r">&nbsp;</td>
                <td class="pc-col-amt r">&nbsp;</td>
            </tr>
        @endforelse

        @php $padRows = max(0, $minLedgerRows - count($lineItems)); @endphp
        @for ($r = 0; $r < $padRows; $r++)
            <tr class="pc-empty-row">
                <td class="pc-col-num c">&nbsp;</td>
                <td class="pc-col-desc">&nbsp;</td>
                <td class="pc-col-unit r">&nbsp;</td>
                <td class="pc-col-amt r">&nbsp;</td>
            </tr>
        @endfor
    </tbody>
    @if ($showPaymentOptions || $showPaidBalance || $isQuotation)
        <tfoot>
            <tr>
                <td colspan="2" class="pc-pay-cell">
                    @if ($showPaymentOptions)
                        @include('billing.documents.prady-classic.partials.payment-box')
                    @endif
                </td>
                <td colspan="2" class="pc-totals-cell">
                    @include('billing.documents.prady-classic.partials.totals-box')
                </td>
            </tr>
        </tfoot>
    @endif
</table>
