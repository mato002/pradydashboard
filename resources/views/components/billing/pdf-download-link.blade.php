@props([
    'url',
    'label' => null,
    'variant' => 'button',
])

@php
    $label ??= $variant === 'icon' ? __('PDF') : __('Download PDF');
@endphp

@once
    <script>
        function pdfDownloadLink() {
            return {
                downloading: false,
                error: null,
                async download(url) {
                    if (this.downloading) {
                        return;
                    }

                    this.downloading = true;
                    this.error = null;

                    try {
                        const response = await fetch(url, {
                            headers: { Accept: 'application/pdf' },
                            credentials: 'same-origin',
                        });

                        const contentType = response.headers.get('content-type') || '';

                        if (! response.ok || (! contentType.includes('pdf') && ! contentType.includes('octet-stream'))) {
                            throw new Error('pdf_unavailable');
                        }

                        const blob = await response.blob();
                        const disposition = response.headers.get('content-disposition') || '';
                        let filename = 'document.pdf';
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);

                        if (match?.[1]) {
                            filename = match[1].replace(/['"]/g, '');
                        }

                        const blobUrl = URL.createObjectURL(blob);
                        const anchor = document.createElement('a');
                        anchor.href = blobUrl;
                        anchor.download = filename;
                        document.body.appendChild(anchor);
                        anchor.click();
                        anchor.remove();
                        URL.revokeObjectURL(blobUrl);
                    } catch {
                        this.error = @js(__('Could not download PDF. Finalize the document or try again.'));
                    } finally {
                        this.downloading = false;
                    }
                },
            };
        }
    </script>
@endonce

<div
    x-data="pdfDownloadLink()"
    @class([
        'inline-block' => in_array($variant, ['icon', 'menu'], true),
        'inline-flex flex-col items-start gap-1' => ! in_array($variant, ['icon', 'menu'], true),
    ])
>
    @if ($variant === 'menu')
        <button
            type="button"
            @click="download(@js($url))"
            :disabled="downloading"
            {{ $attributes->class([
                'flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-800',
            ]) }}
            role="menuitem"
        >
            <span x-show="! downloading" class="inline-flex w-4 shrink-0 justify-center"><x-ui.icon name="file-pdf" class="text-xs" /></span>
            <span x-show="downloading" x-cloak class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600" aria-hidden="true"></span>
            <span x-text="downloading ? @js(__('Preparing PDF…')) : @js($label)">{{ $label }}</span>
        </button>
    @elseif ($variant === 'icon')
        <button
            type="button"
            @click="download(@js($url))"
            :disabled="downloading"
            :title="downloading ? @js(__('Preparing PDF…')) : @js($label)"
            {{ $attributes->class([
                'rounded p-1 text-slate-500 hover:bg-slate-100 disabled:cursor-wait disabled:opacity-60 dark:hover:bg-slate-800',
            ]) }}
        >
            <span x-show="! downloading" aria-hidden="true"><x-ui.icon name="file-pdf" /></span>
            <span x-show="downloading" x-cloak class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600" aria-hidden="true"></span>
            <span class="sr-only" x-text="downloading ? @js(__('Preparing PDF…')) : @js($label)">{{ $label }}</span>
        </button>
    @else
        <button
            type="button"
            @click="download(@js($url))"
            :disabled="downloading"
            {{ $attributes->class([
                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 disabled:cursor-wait disabled:opacity-70 dark:hover:bg-slate-800',
            ]) }}
        >
            <span
                x-show="downloading"
                x-cloak
                class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"
                aria-hidden="true"
            ></span>
            <span x-text="downloading ? @js(__('Preparing PDF…')) : @js($label)">{{ $label }}</span>
        </button>
    @endif

    <p
        x-show="error"
        x-cloak
        x-text="error"
        class="max-w-xs text-[10px] text-rose-700 dark:text-rose-300"
    ></p>
</div>
