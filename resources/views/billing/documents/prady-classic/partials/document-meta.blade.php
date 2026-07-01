<div class="pc-doc-title">{{ $docLabel }}</div>

<table class="pc-meta-row" width="100%" cellpadding="0" cellspacing="0">
    <tr class="pc-meta-line-row">
        <td class="pc-meta-no" align="left" width="50%" valign="bottom">
            No. <span class="pc-meta-no-val">{{ str_replace('No. ', '', $displayNumber) }}</span>
        </td>
        <td class="pc-meta-date" align="right" width="50%" valign="bottom">
            Date <span class="pc-meta-date-val">{{ $issueDateDisplay }}</span>
        </td>
    </tr>
    <tr class="pc-meta-line-row pc-meta-line-row--second">
        <td class="pc-meta-client" align="left" width="50%" valign="bottom">
            <span class="pc-client-prefix">{{ $clientPrefix }}</span>
            <span class="pc-client-name-block">
                <span class="pc-client-name">{{ strtoupper($clientName) }}</span>
                <span class="pc-client-dots">&nbsp;</span>
            </span>
        </td>
        <td class="pc-meta-due" align="right" width="50%" valign="bottom">
            @if ($showDueDate && ! empty($dueDate))
                {{ __('Due') }} <span class="pc-meta-due-val">{{ \Illuminate\Support\Carbon::parse($dueDate)->format('d/m/Y') }}</span>
            @elseif ($showValidity && ! empty($dueDate))
                {{ __('Valid until') }} <span class="pc-meta-due-val">{{ \Illuminate\Support\Carbon::parse($dueDate)->format('d/m/Y') }}</span>
            @else
                &nbsp;
            @endif
        </td>
    </tr>
</table>

@if ($isStatement && ! empty($s['period_start'] ?? ($s['statement']['period_start'] ?? null)))
    <div class="pc-meta-sub">
        {{ __('Period') }}:
        {{ $s['period_start'] ?? $s['statement']['period_start'] }}
        —
        {{ $s['period_end'] ?? $s['statement']['period_end'] ?? '—' }}
    </div>
@endif

@if ($isReceipt && $linkedInvoice)
    <div class="pc-meta-sub">{{ __('Invoice ref') }}: {{ $linkedInvoice }}</div>
@endif
