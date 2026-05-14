@props([
    'title',
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60']) }}>
    <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
        <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>
        @if ($actionHref && $actionLabel)
            <a href="{{ $actionHref }}" class="text-xs font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">{{ $actionLabel }}</a>
        @endif
    </div>
    <div class="prady-scrollbar overflow-x-auto">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="border-t border-slate-200/80 px-4 py-3 text-sm dark:border-slate-800/80">
            {{ $footer }}
        </div>
    @endisset
</div>
