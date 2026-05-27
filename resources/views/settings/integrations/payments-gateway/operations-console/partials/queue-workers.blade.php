<div id="queue-workers" class="scroll-mt-24 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Queue workers') }}</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Worker heartbeats reported to payments.pradytecai.com.') }}</p>

    @unless ($workers['api_available'] ?? true)
        <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ __('Operation API not available yet.') }}</p>
    @endunless

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 dark:border-emerald-900 dark:bg-emerald-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('Active') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $workers['active'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 dark:border-amber-900 dark:bg-amber-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('Stale') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $workers['stale'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 dark:border-rose-900 dark:bg-rose-950/40">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">{{ __('Offline') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $workers['offline'] ?? 0 }}</p>
        </div>
    </div>

    @if (empty($workers['items']))
        <p class="mt-4 rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('No worker heartbeats reported yet.') }}</p>
    @else
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/40">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Worker') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Queues') }}</th>
                        <th class="px-3 py-2">{{ __('Last seen') }}</th>
                        <th class="px-3 py-2">{{ __('Age (seconds)') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($workers['items'] as $worker)
                        <tr>
                            <td class="px-3 py-2 font-mono text-xs">{{ $worker['name'] }}</td>
                            <td class="px-3 py-2"><x-ui.status-badge :variant="$worker['badge']">{{ ucfirst($worker['status']) }}</x-ui.status-badge></td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $worker['queue_names'] }}</td>
                            <td class="px-3 py-2 text-xs">{{ $worker['heartbeat_age'] }}</td>
                            <td class="px-3 py-2 tabular-nums text-xs">{{ $worker['age_seconds'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
