@php
    $s = $snapshot;
    $b = $branding;
@endphp
<div style="font-family:system-ui,sans-serif;max-width:800px;margin:0 auto;padding:24px;color:#0f172a;">
    <header style="display:flex;justify-content:space-between;border-bottom:3px solid {{ $b['primary_color'] ?? '#4f46e5' }};padding-bottom:16px;margin-bottom:24px;">
        <div>
            <h1 style="margin:0;font-size:22px;">{{ $b['company_name'] ?? config('app.name') }}</h1>
            <p style="font-size:12px;color:#64748b;">{{ $b['tax_pin'] ?? '' }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:11px;text-transform:uppercase;color:#64748b;">{{ ucfirst(str_replace('_', ' ', $s['document_type'] ?? 'invoice')) }}</p>
            <p style="font-size:18px;font-weight:700;">{{ $s['invoice_number'] }}</p>
        </div>
    </header>
    <p><strong>{{ __('Bill to') }}:</strong> {{ $s['tenant']['company_name'] ?? '—' }}</p>
    <p style="font-size:12px;color:#64748b;">{{ __('Issue') }}: {{ $s['issue_date'] ?? '—' }} · {{ __('Due') }}: {{ $s['due_date'] ?? '—' }}</p>
    @include('billing.documents.partials.line-items-table')
    @include('billing.documents.partials.totals')
    <footer style="margin-top:24px;font-size:11px;color:#94a3b8;">{{ $b['footer_text'] ?? '' }}</footer>
</div>
