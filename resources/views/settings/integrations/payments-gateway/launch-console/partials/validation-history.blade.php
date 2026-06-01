<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Validation run history') }}</h3>
    <p class="mt-1 text-xs text-slate-500">{{ __('Recent observational validation runs from payments.pradytecai.com.') }}</p>

    @if (! ($validationHistory['available'] ?? false))
        <p class="mt-4 text-sm text-slate-500">{{ __('Validation history API not available yet.') }}</p>
    @else
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Run') }}</th>
                        <th class="px-3 py-2">{{ __('Environment') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Duration') }}</th>
                        <th class="px-3 py-2">{{ __('Strict') }}</th>
                        <th class="px-3 py-2">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($validationHistory['items'] as $item)
                        @php
                            $severity = strtolower((string) ($item['overall_status'] ?? 'unknown'));
                            $mapped = match ($severity) {
                                'pass', 'success' => 'pass',
                                'warn', 'warning' => 'warning',
                                'fail', 'blocked', 'error' => 'blocked',
                                default => 'warning',
                            };
                        @endphp
                        <tr>
                            <td class="px-3 py-2 font-mono text-xs">{{ $shortUuid($item['uuid'] ?? null) }}</td>
                            <td class="px-3 py-2">{{ ucfirst((string) ($item['environment'] ?? '—')) }}</td>
                            <td class="px-3 py-2"><x-ui.status-badge :variant="$severityVariant($mapped)">{{ strtoupper((string) ($item['overall_status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                            <td class="px-3 py-2 text-xs">{{ $item['duration_ms'] ?? $item['duration_seconds'] ?? '—' }}</td>
                            <td class="px-3 py-2 text-xs">{{ ! empty($item['strict_mode']) ? __('Yes') : __('No') }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('settings.payments-gateway.launch-console', array_filter(['validation_run_uuid' => $item['uuid'] ?? null, 'paybill_account_uuid' => $item['paybill_account_uuid'] ?? null, 'environment' => $item['environment'] ?? null])) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No validation runs loaded.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
