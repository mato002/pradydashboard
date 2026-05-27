<div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950/40">
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-3 py-2">{{ __('Callback') }}</th>
                <th class="px-3 py-2">{{ __('Type') }}</th>
                <th class="px-3 py-2">{{ __('Status') }}</th>
                <th class="px-3 py-2">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($rows as $item)
                <tr>
                    <td class="px-3 py-2 font-mono text-xs">{{ substr($item['uuid'] ?? '', 0, 8) }}…</td>
                    <td class="px-3 py-2 text-xs">{{ strtoupper($item['callback_type'] ?? $item['type'] ?? '—') }}</td>
                    <td class="px-3 py-2">
                        <x-ui.status-badge :variant="match (strtolower((string) ($item['processing_status'] ?? $item['status'] ?? 'unknown'))) {
                            'success', 'processed' => 'success',
                            'pending', 'processing' => 'warning',
                            'failed', 'duplicate', 'malformed', 'unmatched' => 'danger',
                            default => 'neutral',
                        }">{{ ucfirst((string) ($item['processing_status'] ?? $item['status'] ?? 'unknown')) }}</x-ui.status-badge>
                    </td>
                    <td class="px-3 py-2">
                        @if (filled($item['uuid'] ?? null))
                            <a href="{{ route('settings.payments-gateway.callback-logs.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ $empty }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
