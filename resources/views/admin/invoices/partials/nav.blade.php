@php
    $tabUrl = fn (string $t) => route('invoices.index', array_merge(request()->except('tab', 'page'), ['tab' => $t]));
    $tabClass = fn (string $t) => $tab === $t
        ? 'border-amber-600 text-amber-700 dark:border-amber-400 dark:text-amber-300'
        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400';
@endphp
<nav class="-mb-px flex gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800" aria-label="{{ __('Financial operations tabs') }}">
    @foreach ([
        'overview' => __('Overview'),
        'invoices' => __('Invoices'),
        'quotations' => __('Quotations'),
        'proforma' => __('Proforma'),
        'receipts' => __('Receipts'),
        'recurring' => __('Recurring Billing'),
        'collections' => __('Collections'),
        'payments' => __('Payment Inbox'),
        'templates' => __('Templates'),
        'statements' => __('Statements'),
        'automation' => __('Automation Rules'),
        'activity' => __('Activity'),
    ] as $key => $label)
        <a href="{{ $tabUrl($key) }}" class="whitespace-nowrap border-b-2 px-3 py-2.5 text-xs font-semibold {{ $tabClass($key) }}">{{ $label }}</a>
    @endforeach
</nav>
