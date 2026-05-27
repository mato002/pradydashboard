<div id="reconciliation-urgency" class="rounded-2xl border p-5 shadow-card @if(($reconciliationUrgency['state'] ?? 'green') === 'red') border-rose-200/80 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40 @elseif(($reconciliationUrgency['state'] ?? 'green') === 'yellow') border-amber-200/80 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40 @else border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900/60 @endif">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Reconciliation urgency') }}</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Unresolved matching exceptions and settlement variance indicators.') }}</p>
        </div>
        <x-ui.status-badge :variant="$operationalTone($reconciliationUrgency['state'] ?? 'green')">{{ ucfirst($reconciliationUrgency['state'] ?? 'green') }}</x-ui.status-badge>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Unmatched') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $reconciliationUrgency['unmatched_count'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Large variances') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $reconciliationUrgency['large_variances'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Settlement status') }}</p>
            <p class="mt-1 text-lg font-semibold">{{ $reconciliationUrgency['settlement_status'] ?? '—' }}</p>
        </div>
    </div>

    @if ($reconciliationUrgency['settlement_variance_unresolved'] ?? false)
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            {{ __('Settlement variance requires treasury review before closing the books for this period.') }}
        </p>
    @elseif (($reconciliationUrgency['unmatched_count'] ?? 0) === 0)
        <p class="mt-4 text-sm text-slate-500">{{ __('No unresolved reconciliation exceptions in the current snapshot.') }}</p>
    @endif
</div>
