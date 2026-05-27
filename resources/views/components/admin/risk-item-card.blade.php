@props([
    'severity' => 'info',
    'severityLabel' => null,
    'title',
    'description' => null,
    'entity' => null,
    'timeLabel' => null,
    'url' => null,
    'actions' => [],
    'riskKey' => null,
    'nested' => false,
])

@php
    $severityStyles = [
        'critical' => [
            'border' => 'border-l-rose-500',
            'icon' => 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
            'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        ],
        'warning' => [
            'border' => 'border-l-amber-500',
            'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
            'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-200',
        ],
        'info' => [
            'border' => 'border-l-sky-500',
            'icon' => 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
            'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        ],
    ];
    $style = $severityStyles[$severity] ?? $severityStyles['info'];
    $severityIcons = [
        'critical' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'warning' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    ];
@endphp

<article @class([
    'flex flex-col gap-3 border-l-4 bg-white/80 px-3 py-3 dark:bg-slate-900/40 sm:flex-row sm:items-center sm:gap-4',
    $style['border'],
    $nested ? 'rounded-lg border border-slate-200/80 dark:border-slate-800' : 'border-b border-slate-100/80 last:border-b-0 dark:border-slate-800/80',
])>
    <div class="flex min-w-0 flex-1 items-start gap-3 sm:max-w-[38%]">
        <span @class(['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl', $style['icon']]) aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $severityIcons[$severity] ?? $severityIcons['info'] }}" />
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span @class(['inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide', $style['badge']])>
                    {{ $severityLabel ?? ucfirst($severity) }}
                </span>
                @if ($timeLabel)
                    <span class="text-[10px] font-medium text-slate-400">{{ $timeLabel }}</span>
                @endif
            </div>
            <h4 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                @if ($url)
                    <a href="{{ $url }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ $title }}</a>
                @else
                    {{ $title }}
                @endif
            </h4>
            @if ($entity)
                <p class="mt-0.5 truncate text-xs font-medium text-indigo-700 dark:text-indigo-300">{{ $entity }}</p>
            @endif
        </div>
    </div>

    <div class="min-w-0 flex-1 text-xs text-slate-600 dark:text-slate-400 sm:px-2">
        @if ($description)
            <p class="line-clamp-2">{{ $description }}</p>
        @endif
        {{ $context ?? '' }}
    </div>

    <div class="flex shrink-0 items-center justify-end gap-2 sm:w-auto">
        @if (count($actions) > 0)
            <x-ui.row-actions-menu align="right">
                @foreach ($actions as $action)
                    @if (($action['type'] ?? null) === 'acknowledge')
                        <form method="POST" action="{{ route('risk-center.acknowledge') }}" class="block" onsubmit="return confirm(@js(__('Dismiss this risk from the overview?')))">
                            @csrf
                            <input type="hidden" name="risk_key" value="{{ $action['risk_key'] ?? $riskKey }}" />
                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" role="menuitem">
                                {{ $action['label'] }}
                            </button>
                        </form>
                    @elseif (! empty($action['href']))
                        <x-ui.row-action
                            :href="$action['href']"
                            :method="$action['method'] ?? null"
                            :confirm="$action['confirm'] ?? null"
                        >{{ $action['label'] }}</x-ui.row-action>
                    @endif
                @endforeach
            </x-ui.row-actions-menu>
        @endif
    </div>
</article>
