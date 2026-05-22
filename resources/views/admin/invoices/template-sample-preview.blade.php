<x-dashboard-layout :heading="$documentTemplate->name" :subheading="__('Sample preview (demo data only)')">
    <div class="mb-4 flex flex-wrap items-center gap-3 text-sm">
        <a href="{{ route('invoices.index', ['tab' => 'templates']) }}" class="text-indigo-600 hover:underline">{{ __('Back to templates') }}</a>
        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800">{{ $documentTemplate->paper_size }}</span>
        @if ($documentTemplate->is_default)
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Default') }}</span>
        @endif
    </div>
    <x-billing.document-preview-frame
        :html="$previewHtml"
        :paper-size="$documentTemplate->paper_size"
        :title="__('Template preview')"
    />
</x-dashboard-layout>
