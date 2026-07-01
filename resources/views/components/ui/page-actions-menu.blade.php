@props([
    'label' => null,
    'align' => 'right',
])

@php
    $panelAlign = $align === 'left'
        ? 'left-0 origin-top-left'
        : 'right-0 origin-top-right';
@endphp

<div
    class="relative inline-flex"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500"
        :aria-expanded="open"
        aria-haspopup="menu"
    >
        {{ $label ?? __('Actions') }}
        <x-ui.icon
            name="chevron-down"
            class="h-4 w-4 transition-transform duration-150"
            x-bind:class="open ? 'rotate-180' : ''"
        />
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
        class="absolute z-50 mt-2 min-w-[14rem] overflow-hidden rounded-xl border border-slate-200/90 bg-white py-1 shadow-lg ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/10 {{ $panelAlign }}"
        role="menu"
    >
        <div @click="open = false">
            {{ $slot }}
        </div>
    </div>
</div>
