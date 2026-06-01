<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('PayBill deployment readiness') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Select a PayBill to evaluate launch blockers. All checks proxy to gateway readiness APIs.') }}</p>
        </div>
    </div>

    <form method="get" action="{{ route('settings.payments-gateway.launch-console') }}" class="mt-4 grid gap-3 md:grid-cols-3">
        <div>
            <label for="paybill_account_uuid" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('PayBill account UUID') }}</label>
            <input id="paybill_account_uuid" name="paybill_account_uuid" value="{{ $filters['paybill_account_uuid'] ?? '' }}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="00000000-0000-0000-0000-000000000000">
        </div>
        <div>
            <label for="environment" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Environment') }}</label>
            <select id="environment" name="environment" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                @foreach ($environments as $environment)
                    <option value="{{ $environment }}" @selected(($filters['environment'] ?? 'production') === $environment)>{{ ucfirst($environment) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Load readiness') }}</button>
        </div>
    </form>

    @if (filled($filters['paybill_account_uuid'] ?? null))
        @if ($paybillReadiness)
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <x-ui.status-badge :variant="$severityVariant($paybillReadiness['overall_severity'])">
                    {{ __('Deployment') }}: {{ $severityLabel($paybillReadiness['overall_severity']) }}
                </x-ui.status-badge>
                <span class="text-xs text-slate-500">{{ $paybillReadiness['account']['account_name'] ?? $filters['paybill_account_uuid'] }}</span>
            </div>

            @if (! empty($paybillReadiness['blockers']))
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-100">
                    <p class="font-semibold">{{ __('Production blockers') }}</p>
                    <ul class="mt-2 list-disc pl-5 text-xs">
                        @foreach ($paybillReadiness['blockers'] as $blocker)
                            <li>{{ $blocker }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ __('Check') }}</th>
                            <th class="px-3 py-2">{{ __('Severity') }}</th>
                            <th class="px-3 py-2">{{ __('Evidence') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($paybillReadiness['checks'] as $check)
                            <tr @class(['bg-rose-50/60 dark:bg-rose-950/20' => ($check['severity'] ?? '') === 'blocked'])>
                                <td class="px-3 py-2 font-medium">{{ $check['label'] }}</td>
                                <td class="px-3 py-2">
                                    <x-ui.status-badge :variant="$severityVariant($check['severity'])">{{ $severityLabel($check['severity']) }}</x-ui.status-badge>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ $check['message'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-4 text-sm text-slate-500">{{ __('PayBill readiness could not be loaded.') }}</p>
        @endif
    @else
        <p class="mt-4 text-sm text-slate-500">{{ __('Enter a PayBill account UUID to evaluate deployment readiness.') }}</p>
    @endif
</div>
