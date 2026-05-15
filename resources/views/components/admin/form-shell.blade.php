@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'backHref',
    'backLabel' => null,
])

@php
    $backLabel = $backLabel ?? __('Back');
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ $backHref }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                {{ $backLabel }}
            </a>
            @if ($badge)
                <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ $badge }}</p>
            @endif
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    {{ $slot }}
</div>
