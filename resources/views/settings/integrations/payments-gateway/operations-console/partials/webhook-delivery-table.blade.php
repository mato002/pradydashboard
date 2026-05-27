<div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950/40">
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">{{ __('Delivery') }}</th>
                <th class="px-3 py-2">{{ __('HTTP') }}</th>
                <th class="px-3 py-2">{{ __('Status') }}</th>
                <th class="px-3 py-2">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($rows as $item)
                <tr>
                    <td class="px-3 py-2 font-mono text-xs">{{ substr($item['uuid'] ?? '', 0, 8) }}…</td>
                    <td class="px-3 py-2 text-xs tabular-nums">{{ $item['http_status'] ?? $item['response_status'] ?? '—' }}</td>
                    <td class="px-3 py-2">
                        <x-ui.status-badge :variant="match (strtolower((string) ($item['status'] ?? 'unknown'))) {
                            'delivered', 'success' => 'success',
                            'pending', 'processing' => 'warning',
                            'failed' => 'danger',
                            default => 'neutral',
                        }">{{ ucfirst((string) ($item['status'] ?? 'unknown')) }}</x-ui.status-badge>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @if (filled($item['uuid'] ?? null))
                                <a href="{{ route('settings.payments-gateway.webhook-deliveries.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a>
                            @endif
                            @permission('payments_gateway.manage')
                                @if (filled($item['uuid'] ?? null))
                                    <form method="post" action="{{ route('settings.payments-gateway.webhook-deliveries.redispatch', $item['uuid']) }}" onsubmit="return confirm(@js(__('Redispatch this webhook delivery?')))">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ __('Redispatch') }}</button>
                                    </form>
                                @endif
                            @endpermission
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ $empty }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
