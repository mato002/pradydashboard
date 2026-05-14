@props([
    'title',
    'value' => 0,
    'sublabel' => null,
    'trend' => null,
    'tone' => 'indigo',
    'points' => [],
    'animate' => true,
])

@php
    $tones = [
        'indigo' => [
            'icon' => 'from-indigo-500 to-violet-600 shadow-indigo-500/25',
            'spark' => 'stroke-indigo-500',
            'fill' => 'fill-indigo-500/10',
        ],
        'emerald' => [
            'icon' => 'from-emerald-500 to-teal-600 shadow-emerald-500/25',
            'spark' => 'stroke-emerald-500',
            'fill' => 'fill-emerald-500/10',
        ],
        'amber' => [
            'icon' => 'from-amber-500 to-orange-600 shadow-amber-500/25',
            'spark' => 'stroke-amber-500',
            'fill' => 'fill-amber-500/10',
        ],
        'rose' => [
            'icon' => 'from-rose-500 to-red-600 shadow-rose-500/25',
            'spark' => 'stroke-rose-500',
            'fill' => 'fill-rose-500/10',
        ],
        'violet' => [
            'icon' => 'from-violet-500 to-fuchsia-600 shadow-violet-500/25',
            'spark' => 'stroke-violet-500',
            'fill' => 'fill-violet-500/10',
        ],
        'sky' => [
            'icon' => 'from-sky-500 to-blue-600 shadow-sky-500/25',
            'spark' => 'stroke-sky-500',
            'fill' => 'fill-sky-500/10',
        ],
    ];
    $t = $tones[$tone] ?? $tones['indigo'];
    $numeric = is_numeric($value);
@endphp

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card transition-shadow duration-200 hover:shadow-card-hover dark:border-slate-800/80 dark:bg-slate-900/60']) }}>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white to-slate-50/40 opacity-90 dark:from-slate-900 dark:to-slate-950/40"></div>
    <div class="relative flex items-start justify-between gap-3">
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <div class="flex flex-wrap items-baseline gap-2">
                @if ($numeric && $animate)
                    <p class="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white" x-data="countUp({{ (int) $value }})" x-text="display">0</p>
                @else
                    <p class="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white">{{ $value }}</p>
                @endif
                @if ($trend)
                    @php $trendUp = str_starts_with(ltrim($trend), '+'); @endphp
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1',
                        'bg-emerald-500/10 text-emerald-700 ring-emerald-500/15 dark:text-emerald-300' => $trendUp,
                        'bg-rose-500/10 text-rose-700 ring-rose-500/15 dark:text-rose-300' => ! $trendUp,
                    ])>{{ $trend }}</span>
                @endif
            </div>
            @if ($sublabel)
                <p class="text-xs text-slate-500 dark:text-slate-400">{!! $sublabel !!}</p>
            @endif
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $t['icon'] }} text-white shadow-lg">
                {!! $icon ?? '' !!}
            </div>
            <x-ui.sparkline :points="$points" :stroke-class="$t['spark']" :fill-class="$t['fill']" />
        </div>
    </div>
</div>
