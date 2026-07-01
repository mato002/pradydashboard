@props(['align' => 'right'])

<div
    class="relative inline-flex justify-end"
    @click.stop
    x-data="rowActionsMenu()"
    @click.outside="close()"
    @keydown.escape.window="close()"
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-500 ring-1 ring-transparent transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        :class="isOpen() ? 'bg-slate-100 text-slate-700 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-200' : ''"
        :aria-expanded="isOpen()"
        aria-haspopup="menu"
    >
        <span class="sr-only">{{ __('Actions') }}</span>
        <x-ui.icon name="ellipsis-vertical" class="h-4 w-4" />
    </button>

    <div
        x-show="isOpen()"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="panelStyle"
        class="overflow-hidden rounded-xl border border-slate-200/90 bg-white py-1 shadow-lg ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/10"
        role="menu"
    >
        <div @click="close()">
            {{ $slot }}
        </div>
    </div>
</div>
