@props(['align' => 'right'])

@php
    $panelAlign = $align === 'left'
        ? 'left-0 origin-top-left'
        : 'right-0 origin-top-right';
@endphp

<div
    class="relative inline-flex justify-end"
    @click.stop
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-500 ring-1 ring-transparent transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        :class="open ? 'bg-slate-100 text-slate-700 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-200' : ''"
        :aria-expanded="open"
        aria-haspopup="menu"
    >
        <span class="sr-only">{{ __('Actions') }}</span>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="5" r="1.5" />
            <circle cx="12" cy="12" r="1.5" />
            <circle cx="12" cy="19" r="1.5" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute z-50 mt-1 min-w-[11rem] overflow-hidden rounded-xl border border-slate-200/90 bg-white py-1 shadow-lg ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/10 {{ $panelAlign }}"
        role="menu"
    >
        <div @click="open = false">
            {{ $slot }}
        </div>
    </div>
</div>
