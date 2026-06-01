<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Gateway operational status') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Summarized launch posture from payments.pradytecai.com operations APIs.') }}</p>
        </div>
        @if (! empty($operationalStatus['overall_severity']))
            <x-ui.status-badge :variant="$severityVariant($operationalStatus['overall_severity'])">
                {{ __('Overall') }}: {{ $severityLabel($operationalStatus['overall_severity']) }}
            </x-ui.status-badge>
        @endif
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($operationalStatus as $key => $metric)
            @if ($key === 'overall_severity' || ! is_array($metric))
                @continue
            @endif
            <div @class([
                'rounded-xl border px-4 py-3',
                'border-emerald-200/80 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40' => ($metric['severity'] ?? '') === 'pass',
                'border-amber-200/80 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40' => ($metric['severity'] ?? '') === 'warning',
                'border-rose-200/80 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40' => ($metric['severity'] ?? '') === 'blocked',
                'border-slate-200/80 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40' => ! in_array($metric['severity'] ?? '', ['pass', 'warning', 'blocked'], true),
            ])>
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</p>
                    <x-ui.status-badge :variant="$severityVariant($metric['severity'] ?? 'neutral')">{{ $severityLabel($metric['severity'] ?? 'neutral') }}</x-ui.status-badge>
                </div>
                <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>
</div>
