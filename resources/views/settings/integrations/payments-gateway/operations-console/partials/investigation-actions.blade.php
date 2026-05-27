@props(['type', 'uuid', 'quickActions' => []])

<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Remediation actions') }}</h3>

    @permission('payments_gateway.manage')
        <div class="mt-4 space-y-3">
            @if ($type === 'dead_letter')
                @if ($quickActions['replay'] ?? false)
                    <form method="post" action="{{ route('settings.payments-gateway.operations-console.dead-letters.replay', $uuid) }}" onsubmit="return confirm(@js(__('Replays the underlying queue job. Confirm the root cause is resolved first.')))">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Replay dead letter') }}</button>
                    </form>
                @endif
                @if ($quickActions['discard'] ?? false)
                    <form method="post" action="{{ route('settings.payments-gateway.operations-console.dead-letters.discard', $uuid) }}" onsubmit="return confirm(@js(__('Discarding a dead letter marks it as handled and it will not be replayed automatically.')))">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Discard dead letter') }}</button>
                    </form>
                @endif
            @elseif ($type === 'callback_log' && ($quickActions['retry'] ?? false))
                <form method="post" action="{{ route('settings.payments-gateway.operations-console.callback-logs.retry', $uuid) }}" onsubmit="return confirm(@js(__('Reprocesses the callback on payments.pradytecai.com.')))">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Retry callback') }}</button>
                </form>
            @elseif ($type === 'webhook_delivery' && ($quickActions['redispatch'] ?? false))
                <form method="post" action="{{ route('settings.payments-gateway.operations-console.webhook-deliveries.redispatch', $uuid) }}" onsubmit="return confirm(@js(__('Redispatch this webhook delivery? This queues another signed POST to the tenant endpoint.')))">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Redispatch delivery') }}</button>
                </form>
            @elseif ($type === 'webhook_event' && ($quickActions['redispatch_event'] ?? false))
                <form method="post" action="{{ route('settings.payments-gateway.webhook-events.redispatch', $uuid) }}" onsubmit="return confirm(@js(__('Redispatch this webhook event?')))">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Redispatch event') }}</button>
                </form>
            @elseif ($type === 'treasury_alert')
                @if ($quickActions['acknowledge'] ?? false)
                    <form method="post" action="{{ route('settings.payments-gateway.operations-console.treasury-alerts.acknowledge', $uuid) }}" class="space-y-2" onsubmit="return confirm(@js(__('Marks the alert as acknowledged without resolving the underlying issue.')))">
                        @csrf
                        <input type="text" name="comments" maxlength="500" placeholder="{{ __('Comments (optional)') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900">
                        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Acknowledge alert') }}</button>
                    </form>
                @endif
                @if ($quickActions['resolve'] ?? false)
                    <form method="post" action="{{ route('settings.payments-gateway.operations-console.treasury-alerts.resolve', $uuid) }}" class="space-y-2" onsubmit="return confirm(@js(__('Closes the alert after remediation is complete.')))">
                        @csrf
                        <input type="text" name="comments" maxlength="500" placeholder="{{ __('Comments (optional)') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900">
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Resolve alert') }}</button>
                    </form>
                @endif
            @endif
        </div>
    @else
        <p class="mt-4 text-sm text-slate-500">{{ __('Remediation actions require payments_gateway.manage permission.') }}</p>
    @endpermission
</div>
