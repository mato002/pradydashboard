<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Live activity stream') }}</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Newest operational signals across transactions, callbacks, alerts, and webhook failures.') }}</p>

    @if (empty($activityStream))
        <p class="mt-4 rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('No recent operational activity.') }}</p>
    @else
        <ol class="mt-4 space-y-3">
            @foreach ($activityStream as $entry)
                <li class="flex gap-3 rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <span @class([
                        'mt-1 h-2.5 w-2.5 shrink-0 rounded-full',
                        'bg-rose-500' => in_array($entry['severity'] ?? '', ['critical', 'high'], true),
                        'bg-amber-500' => ($entry['severity'] ?? '') === 'medium',
                        'bg-emerald-500' => ($entry['severity'] ?? 'low') === 'low',
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $entry['title'] }}</p>
                            <span class="text-xs text-slate-500">{{ $ageLabel($entry['timestamp'] ?? null) }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $entry['subtitle'] }}</p>
                        @if (filled($entry['url'] ?? null))
                            <a href="{{ $entry['url'] }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Open detail') }}</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
