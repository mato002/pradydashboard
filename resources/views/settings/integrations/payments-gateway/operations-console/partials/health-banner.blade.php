@if (! empty($healthBanner))
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Operations health') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Live treasury incident posture from payments.pradytecai.com.') }}</p>
            </div>
            @if (! empty($healthBanner['overall']))
                <x-ui.status-badge :variant="$operationalTone($healthBanner['overall']['state'])">
                    {{ __('Overall') }}: {{ $healthBanner['overall']['value'] }}
                </x-ui.status-badge>
            @endif
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach (['overall', 'queue', 'treasury_alerts', 'failed_webhooks', 'dead_letters', 'unmatched_reconciliation'] as $key)
                @php($metric = $healthBanner[$key] ?? null)
                @if ($metric)
                    <div @class([
                        'rounded-xl border px-4 py-3',
                        'border-emerald-200/80 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40' => ($metric['state'] ?? '') === 'green',
                        'border-amber-200/80 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40' => ($metric['state'] ?? '') === 'yellow',
                        'border-rose-200/80 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40' => ($metric['state'] ?? '') === 'red',
                        'border-slate-200/80 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40' => ! in_array($metric['state'] ?? '', ['green', 'yellow', 'red'], true),
                    ])>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
