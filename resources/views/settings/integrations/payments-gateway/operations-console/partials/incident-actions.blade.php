@php
    $actionKey = $action ?? null;
    $quickAction = $actionKey ? ($quickActions[$actionKey] ?? null) : null;
@endphp

@if ($panelKey === 'failed_webhooks' && filled($item['delivery_uuid'] ?? null))
    @if ($quickActions['redispatch_webhook']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.webhook-deliveries.redispatch', $item['delivery_uuid']) }}" onsubmit="return confirm(@js(__('Redispatch this webhook delivery? This queues another signed POST to the tenant endpoint.')))">
                @csrf
                <button type="submit" class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ __('Redispatch') }}</button>
            </form>
        @endpermission
    @else
        <p class="text-xs text-slate-500">{{ __('Operation API not available yet.') }}</p>
    @endif
@elseif ($panelKey === 'dead_letters' && filled($item['uuid'] ?? null))
    @if ($quickActions['replay_dead_letter']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.dead-letters.replay', $item['uuid']) }}" onsubmit="return confirm(@js($quickActions['replay_dead_letter']['warning'] ?? __('Replay this dead letter?')))">
                @csrf
                <button type="submit" class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ __('Replay') }}</button>
            </form>
        @endpermission
    @else
        <p class="text-xs text-slate-500">{{ __('Operation API not available yet.') }}</p>
    @endif
    @if ($quickActions['discard_dead_letter']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.dead-letters.discard', $item['uuid']) }}" onsubmit="return confirm(@js($quickActions['discard_dead_letter']['warning'] ?? __('Discard this dead letter?')))">
                @csrf
                <button type="submit" class="text-xs font-semibold text-rose-700 dark:text-rose-300">{{ __('Discard') }}</button>
            </form>
        @endpermission
    @endif
@elseif ($panelKey === 'failed_callbacks' && filled($item['uuid'] ?? null))
    @if ($quickActions['retry_callback']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.callback-logs.retry', $item['uuid']) }}" onsubmit="return confirm(@js($quickActions['retry_callback']['warning'] ?? __('Retry processing this callback on the gateway?')))">
                @csrf
                <button type="submit" class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ __('Retry callback') }}</button>
            </form>
        @endpermission
    @else
        <p class="text-xs text-slate-500">{{ __('Operation API not available yet.') }}</p>
    @endif
@elseif ($panelKey === 'critical_alerts' && filled($item['uuid'] ?? null))
    @if ($quickActions['acknowledge_alert']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.treasury-alerts.acknowledge', $item['uuid']) }}" class="flex flex-col items-end gap-2" onsubmit="return confirm(@js($quickActions['acknowledge_alert']['warning'] ?? __('Acknowledge this alert?')))">
                @csrf
                <input type="text" name="comments" maxlength="500" placeholder="{{ __('Comments (optional)') }}" class="w-full min-w-[12rem] rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900">
                <button type="submit" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Acknowledge') }}</button>
            </form>
        @endpermission
    @else
        <p class="text-xs text-slate-500">{{ __('Operation API not available yet.') }}</p>
    @endif
    @if ($quickActions['resolve_alert']['available'] ?? false)
        @permission('payments_gateway.manage')
            <form method="post" action="{{ route('settings.payments-gateway.operations-console.treasury-alerts.resolve', $item['uuid']) }}" class="flex flex-col items-end gap-2" onsubmit="return confirm(@js($quickActions['resolve_alert']['warning'] ?? __('Resolve this alert after remediation?')))">
                @csrf
                <input type="text" name="comments" maxlength="500" placeholder="{{ __('Comments (optional)') }}" class="w-full min-w-[12rem] rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900">
                <button type="submit" class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Resolve') }}</button>
            </form>
        @endpermission
    @endif
@elseif ($panelKey === 'unmatched_transactions')
    <a href="#reconciliation" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Review reconciliation') }}</a>
@endif
