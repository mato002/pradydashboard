@props(['label', 'value', 'hint' => null])

<div class="rounded-2xl border border-slate-200/80 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $value }}</p>
    @if ($hint)
        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
