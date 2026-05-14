@props(['variant' => 'neutral'])

@php
    $map = [
        'success' => 'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300',
        'warning' => 'bg-amber-500/12 text-amber-800 ring-amber-500/20 dark:text-amber-200',
        'danger' => 'bg-rose-500/12 text-rose-700 ring-rose-500/20 dark:text-rose-200',
        'info' => 'bg-sky-500/12 text-sky-800 ring-sky-500/20 dark:text-sky-200',
        'neutral' => 'bg-slate-500/10 text-slate-700 ring-slate-500/15 dark:text-slate-200',
        'purple' => 'bg-violet-500/12 text-violet-800 ring-violet-500/20 dark:text-violet-200',
    ];
    $cls = $map[$variant] ?? $map['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide ring-1 ring-inset '.$cls]) }}>
    {{ $slot }}
</span>
