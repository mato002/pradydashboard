<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Operational validation workspace') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Observational validation only — attach existing transaction UUIDs. The dashboard never initiates Daraja payments.') }}</p>
        </div>
    </div>

    @permission('payments_gateway.manage')
        <form method="post" action="{{ route('settings.payments-gateway.launch-console.validation-runs.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
            @csrf
            <div>
                <label for="validation_environment" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Environment') }}</label>
                <select id="validation_environment" name="environment" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    @foreach ($environments as $environment)
                        <option value="{{ $environment }}" @selected(($filters['environment'] ?? 'production') === $environment)>{{ ucfirst($environment) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="validation_paybill" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('PayBill account UUID') }} *</label>
                <input id="validation_paybill" name="paybill_account_uuid" value="{{ $filters['paybill_account_uuid'] ?? '' }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <div>
                <label for="stk_transaction_uuid" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Existing STK transaction UUID') }}</label>
                <input id="stk_transaction_uuid" name="stk_transaction_uuid" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <div>
                <label for="c2b_transaction_uuid" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Existing C2B transaction UUID') }}</label>
                <input id="c2b_transaction_uuid" name="c2b_transaction_uuid" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <div>
                <label for="b2c_payout_uuid" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Existing B2C payout UUID') }}</label>
                <input id="b2c_payout_uuid" name="b2c_payout_uuid" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" id="strict_mode" name="strict_mode" value="1" class="rounded border-slate-300">
                <label for="strict_mode" class="text-sm">{{ __('Strict mode (treat warnings as blocking)') }}</label>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Run observational validation') }}</button>
            </div>
        </form>
    @else
        <p class="mt-4 text-sm text-slate-500">{{ __('Validation runs require payments_gateway.manage permission.') }}</p>
    @endpermission

    @if ($validationRun)
        <div class="mt-6 space-y-4 rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.status-badge :variant="$severityVariant($validationRun['overall_severity'])">{{ $severityLabel($validationRun['overall_severity']) }}</x-ui.status-badge>
                <span class="text-xs text-slate-500">{{ __('Run') }} {{ $shortUuid($validationRun['uuid']) }}</span>
                @if ($validationRun['strict_mode'])
                    <x-ui.status-badge variant="warning">{{ __('Strict mode') }}</x-ui.status-badge>
                @endif
            </div>

            @if (! empty($validationRun['stages']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-2 py-1">{{ __('Stage') }}</th>
                                <th class="px-2 py-1">{{ __('Status') }}</th>
                                <th class="px-2 py-1">{{ __('Evidence') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($validationRun['stages'] as $stage)
                                <tr>
                                    <td class="px-2 py-1">{{ $stage['label'] }}</td>
                                    <td class="px-2 py-1"><x-ui.status-badge :variant="$severityVariant($stage['severity'])">{{ $severityLabel($stage['severity']) }}</x-ui.status-badge></td>
                                    <td class="px-2 py-1">{{ $stage['message'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="grid gap-3 md:grid-cols-3 text-xs">
                <div><span class="font-semibold text-slate-500">{{ __('Reconciliation') }}:</span> {{ is_array($validationRun['reconciliation'] ?? null) ? json_encode($validationRun['reconciliation']) : ($validationRun['reconciliation'] ?? '—') }}</div>
                <div><span class="font-semibold text-slate-500">{{ __('Webhook delivery') }}:</span> {{ is_array($validationRun['webhook_delivery'] ?? null) ? json_encode($validationRun['webhook_delivery']) : ($validationRun['webhook_delivery'] ?? '—') }}</div>
                <div><span class="font-semibold text-slate-500">{{ __('Callback ingestion') }}:</span> {{ is_array($validationRun['callback_ingestion'] ?? null) ? json_encode($validationRun['callback_ingestion']) : ($validationRun['callback_ingestion'] ?? '—') }}</div>
            </div>
        </div>
    @endif
</div>
