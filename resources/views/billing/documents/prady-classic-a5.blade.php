@php
    $s = $snapshot;
    $b = $branding;
    $pay = $s['payment_options'] ?? [];
    $lineItems = $s['line_items'] ?? [];
    $displayName = $b['display_name'] ?? $b['company_name'] ?? config('app.name');
    $docLabel = strtoupper(str_replace('_', ' ', $s['document_type'] ?? 'invoice'));
    $clientLabel = $s['tenant']['company_name'] ?? '—';
    $attention = $s['tenant']['billing_contact_name'] ?? null;
@endphp
<div class="preview-a5">
    <div class="phdr">
        @if (! empty($b['logo_url'] ?? null))
            <div class="phdr-logo"><img src="{{ $b['logo_url'] }}" alt="{{ $displayName }}"></div>
        @else
            <div class="phdr-mark" aria-hidden="true">P</div>
        @endif
        <div class="phdr-text">
            <div class="phdr-name">{{ $displayName }}</div>
            @if (! empty($b['tagline'] ?? null))
                <div class="phdr-tag">{{ $b['tagline'] }}</div>
            @endif
            <div class="phdr-meta">
                @php $pin = trim((string) ($b['tax_pin'] ?? '')); @endphp
                @if ($pin !== '')
                    <span>{{ __('PIN') }}: {{ $pin }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="pdoc-type">{{ $docLabel }}</div>

    <div class="pgrid">
        <div class="pcol">
            <div class="plab">{{ __('Bill to') }}</div>
            <div class="pval pstrong">{{ $clientLabel }}</div>
            @if ($attention && $attention !== $clientLabel)
                <div class="psub">{{ __('Attn.') }} {{ $attention }}</div>
            @endif
            @if (! empty($s['tenant']['billing_address'] ?? null))
                <div class="psub">{{ $s['tenant']['billing_address'] }}</div>
            @endif
            @if (! empty($s['tenant']['billing_phone'] ?? null))
                <div class="psub">{{ $s['tenant']['billing_phone'] }}</div>
            @endif
        </div>
        <div class="pcol pcol-num">
            <div class="pri-row"><span class="plab">{{ __('No.') }}</span><span class="pval mono">{{ $s['invoice_number'] ?? '—' }}</span></div>
            <div class="pri-row"><span class="plab">{{ __('Date') }}</span><span class="pval">{{ $s['issue_date'] ?? '—' }}</span></div>
            @if (! empty($s['due_date'] ?? null))
                <div class="pri-row"><span class="plab">{{ __('Due') }}</span><span class="pval">{{ $s['due_date'] }}</span></div>
            @endif
        </div>
    </div>

    <table class="ptable">
        <thead>
            <tr>
                <th class="c">{{ __('#') }}</th>
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
                    <td class="r mono">{{ number_format((float) ($line['quantity'] ?? 0), 2) }}</td>
                    <td class="r mono">{{ number_format((float) ($line['unit_price'] ?? 0), 2) }}</td>
                    <td class="r mono">{{ number_format((float) ($line['line_total'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ptotals">
        <div class="ptot-row"><span>{{ __('Subtotal') }}</span><span class="mono">{{ $s['currency'] ?? 'KES' }} {{ number_format((float) ($s['subtotal'] ?? 0), 2) }}</span></div>
        @if ((float) ($s['discount_amount'] ?? 0) > 0)
            <div class="ptot-row"><span>{{ __('Discount') }}</span><span class="mono">− {{ number_format((float) $s['discount_amount'], 2) }}</span></div>
        @endif
        <div class="ptot-row"><span>{{ __('Tax') }}</span><span class="mono">{{ $s['currency'] ?? 'KES' }} {{ number_format((float) ($s['tax_amount'] ?? 0), 2) }}</span></div>
        <div class="ptot-row pgrand"><span>{{ __('Total') }}</span><span class="mono">{{ $s['currency'] ?? 'KES' }} {{ number_format((float) ($s['total'] ?? 0), 2) }}</span></div>
        @if ((float) ($s['amount_paid'] ?? 0) > 0)
            <div class="ptot-row"><span>{{ __('Paid') }}</span><span class="mono">{{ $s['currency'] ?? 'KES' }} {{ number_format((float) $s['amount_paid'], 2) }}</span></div>
            <div class="ptot-row pgrand"><span>{{ __('Balance') }}</span><span class="mono">{{ $s['currency'] ?? 'KES' }} {{ number_format((float) ($s['balance_due'] ?? 0), 2) }}</span></div>
        @endif
    </div>

    <div class="ppay">
        <div class="plab">{{ __('Payment options') }}</div>
        <div class="ppay-grid">
            <div>
                <span class="pk">{{ __('Bank') }}</span>
                <span class="pv">{{ $pay['bank_name'] ?: '—' }}</span>
            </div>
            <div>
                <span class="pk">{{ __('Account no.') }}</span>
                <span class="pv mono">{{ $pay['bank_account_number'] ?: '—' }}</span>
            </div>
            <div>
                <span class="pk">{{ __('M-Pesa Paybill') }}</span>
                <span class="pv mono">{{ $pay['mpesa_paybill'] ?: '—' }}</span>
            </div>
            <div>
                <span class="pk">{{ __('Paybill account') }}</span>
                <span class="pv mono">{{ $pay['paybill_account_number'] ?: '—' }}</span>
            </div>
        </div>
    </div>

    <div class="pfoot">
        {{ $b['footer_text'] ?? $billing->invoiceFooterNotes() }}
        @if ($billing->paymentInstructions())
            <div class="pinst">{{ $billing->paymentInstructions() }}</div>
        @endif
    </div>
</div>
