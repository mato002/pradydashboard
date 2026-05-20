<x-dashboard-layout :heading="$invoice->invoice_number" :subheading="__('Document preview')">
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Back to invoice') }}</a>
        <a href="{{ route('invoices.pdf', $invoice) }}" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Download PDF') }}</a>
        <form method="post" action="{{ route('invoices.email', $invoice) }}" class="inline">@csrf
            <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Email') }}</button>
        </form>
        <form method="post" action="{{ route('invoices.regenerate', $invoice) }}" class="inline">@csrf
            <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold">{{ __('Regenerate') }}</button>
        </form>
    </div>
    <div class="overflow-hidden rounded-2xl border bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
        <iframe srcdoc="{{ e($document->html_snapshot) }}" class="h-[80vh] w-full border-0" title="{{ __('Preview') }}"></iframe>
    </div>
</x-dashboard-layout>
