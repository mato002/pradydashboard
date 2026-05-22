@props([
    'html',
    'paperSize' => 'A4',
    'title' => null,
])

@php
    $srcdoc = str_replace(['&', '"'], ['&amp;', '&quot;'], (string) $html);
    $size = strtoupper((string) $paperSize);
    [$frameWidth, $frameMinHeight] = match ($size) {
        'A5' => ['148mm', '210mm'],
        default => ['210mm', '297mm'],
    };
    $frameTitle = $title ?? __('Preview');
@endphp

<div class="document-preview-frame mx-auto w-full max-w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100 p-2 dark:border-slate-700">
    <iframe
        srcdoc="{!! $srcdoc !!}"
        sandbox="allow-same-origin"
        class="mx-auto block w-full max-w-full border-0 bg-white shadow-md"
        style="width: 100%; max-width: 100%; min-height: {{ $frameMinHeight }}; aspect-ratio: {{ $size === 'A5' ? '148 / 210' : '210 / 297' }};"
        title="{{ $frameTitle }}"
    ></iframe>
</div>
