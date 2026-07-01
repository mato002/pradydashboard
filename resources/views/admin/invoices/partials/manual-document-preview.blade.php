@php
    $isMobilePreview = ($previewMode ?? 'desktop') === 'mobile';
@endphp

@if (! $isMobilePreview)
    @once
        @include('admin.invoices.partials.manual-document-preview-styles')
    @endonce
@endif

<aside
    @if (! $isMobilePreview) id="manual-document-preview" @endif
    data-testid="manual-document-preview"
    data-preview-mode="{{ $previewMode ?? 'desktop' }}"
    data-initial-doc-type="{{ $documentType }}"
    @class([
        'manual-doc-preview-panel flex w-full max-w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-900/60',
        'sticky top-24 max-h-[calc(100vh-7rem)]' => ! $isMobilePreview,
        'fixed inset-y-0 right-0 z-50 hidden max-w-md shadow-2xl' => $isMobilePreview,
    ])
    @if ($isMobilePreview)
        x-show="previewOpen"
        x-cloak
        :class="{ '!flex': previewOpen }"
    @endif
>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200" x-text="documentTypeBadge()">{{ $typeLabels[$documentType] ?? $documentType }}</span>
            @if ($defaultTemplate ?? null)
                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ $defaultTemplate->name }}</span>
                <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-700">{{ strtoupper($defaultTemplate->paper_size) }}</span>
            @endif
        </div>
        <div class="flex items-center gap-1">
            <button type="button" @click="previewZoom = Math.max(0.6, previewZoom - 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="{{ __('Zoom out') }}">−</button>
            <span class="min-w-[3rem] text-center text-[10px] text-slate-500" x-text="Math.round(previewZoom * 100) + '%'">100%</span>
            <button type="button" @click="previewZoom = Math.min(1.2, previewZoom + 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="{{ __('Zoom in') }}">+</button>
            <button type="button" @click="previewOpen = false" class="lg:hidden rounded border px-2 py-0.5 text-xs text-slate-600">{{ __('Close') }}</button>
        </div>
    </div>

    <p class="px-4 pt-2 text-[10px] text-slate-500 dark:text-slate-400">
        {{ __('Preview only — final totals calculated on save') }}
    </p>

    <div class="manual-doc-preview-viewport flex-1 overflow-x-hidden overflow-y-auto p-3">
        <div
            class="manual-doc-preview-scaler mx-auto w-full max-w-full"
            :style="'transform: scale(' + previewZoom + '); transform-origin: top center;'"
        >
            <div
                class="manual-doc-preview-frame"
                data-testid="manual-document-preview-frame"
                :class="previewPaperSize === 'A5' ? 'is-a5' : 'is-a4'"
            >
                <iframe
                    x-ref="previewFrame"
                    :srcdoc="previewHtml"
                    sandbox="allow-same-origin"
                    class="manual-doc-preview-iframe"
                    :title="documentPreviewTitle()"
                ></iframe>
            </div>
        </div>
    </div>
</aside>
