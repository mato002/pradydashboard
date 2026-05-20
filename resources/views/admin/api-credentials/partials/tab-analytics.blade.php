<div class="grid gap-5 lg:grid-cols-2">
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Communication summary') }}</h3>
        </div>
        <ul class="divide-y divide-slate-100 px-4 dark:divide-slate-800/80">
            <li class="flex justify-between py-3 text-sm"><span class="text-slate-500">{{ __('Success rate') }}</span><span class="font-semibold">{{ $summary['success_rate'] !== null ? $summary['success_rate'].'%' : '—' }}</span></li>
            <li class="flex justify-between py-3 text-sm"><span class="text-slate-500">{{ __('Average response time') }}</span><span class="font-semibold">{{ $summary['average_response_time_ms'] ? $summary['average_response_time_ms'].'ms' : '—' }}</span></li>
            <li class="flex justify-between py-3 text-sm"><span class="text-slate-500">{{ __('Checks today') }}</span><span class="font-semibold">{{ $summary['requests_today'] }}</span></li>
            <li class="flex justify-between py-3 text-sm"><span class="text-slate-500">{{ __('Failed checks') }}</span><span class="font-semibold text-rose-600">{{ $summary['failed_checks'] }}</span></li>
        </ul>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('APIs by category') }}</h3>
        </div>
        <ul class="divide-y divide-slate-100 px-4 dark:divide-slate-800/80">
            @forelse ($summary['by_category'] ?? [] as $row)
                <li class="flex justify-between py-2 text-sm"><span>{{ $row['category'] }}</span><span class="font-semibold tabular-nums">{{ $row['count'] }}</span></li>
            @empty
                <li class="py-6 text-center text-sm text-slate-500">{{ __('No API checks recorded yet.') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 lg:col-span-2">
        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Recent API checks') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Metadata only — no sensitive payloads stored.') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="prady-table w-full min-w-[600px]">
                <thead>
                    <tr>
                        <th>{{ __('Integration') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('HTTP') }}</th>
                        <th>{{ __('Time') }}</th>
                        <th>{{ __('Result') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse ($recentChecks as $check)
                        <tr>
                            <td class="text-sm font-medium">{{ $check['integration'] }}</td>
                            <td class="text-xs capitalize">{{ str_replace('_', ' ', $check['category']) }}</td>
                            <td class="text-xs text-slate-500">{{ $check['checked_at']?->diffForHumans() }}</td>
                            <td class="font-mono text-xs">{{ $check['response_code'] ?: '—' }}</td>
                            <td class="font-mono text-xs">{{ $check['response_time_ms'] ? $check['response_time_ms'].'ms' : '—' }}</td>
                            <td><x-ui.status-badge :variant="$check['success'] ? 'success' : 'danger'">{{ $check['success'] ? __('OK') : __('Fail') }}</x-ui.status-badge></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-slate-500">{{ __('No API checks recorded yet. Run a connection test from a tenant integration.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
