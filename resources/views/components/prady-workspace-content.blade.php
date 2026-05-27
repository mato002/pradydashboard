@props([
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
])

<div
    id="prady-workspace-content"
    class="prady-workspace-content min-w-0"
    @if ($heading) data-page-heading="{{ $heading }}" @endif
    @if ($subheading) data-page-subheading="{{ $subheading }}" @endif
    @if ($documentTitle) data-document-title="{{ $documentTitle }}" @endif
>
    @if (session('status'))
        <div class="prady-flash-status mb-5 rounded-2xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    {{ $slot }}
</div>
