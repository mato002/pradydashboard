<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Incident escalation workspace') }}</h3>
    <p class="mt-1 text-xs text-slate-500">{{ __('Launch blockers and live incidents. Remediation actions proxy to existing gateway operations APIs.') }}</p>

    @if (($escalation['blocked_deployment_issues'] ?? 0) > 0)
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-100">
            {{ __(':count blocked deployment issue(s) require resolution before launch.', ['count' => $escalation['blocked_deployment_issues']]) }}
        </div>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        @include('settings.integrations.payments-gateway.launch-console.partials.incident-list', [
            'title' => __('Failed webhooks'),
            'items' => $escalation['failed_webhooks'] ?? [],
            'empty' => __('No failed webhook deliveries.'),
            'linkRoute' => 'settings.payments-gateway.operations-console.webhook-deliveries.show',
            'linkKey' => 'uuid',
            'actionRoute' => 'settings.payments-gateway.operations-console.webhook-deliveries.redispatch',
            'actionKey' => 'uuid',
            'actionLabel' => __('Redispatch'),
        ])

        @include('settings.integrations.payments-gateway.launch-console.partials.incident-list', [
            'title' => __('Dead letters'),
            'items' => $escalation['dead_letters'] ?? [],
            'empty' => __('No pending dead letters.'),
            'linkRoute' => 'settings.payments-gateway.operations-console.dead-letters.show',
            'linkKey' => 'uuid',
            'actionRoute' => 'settings.payments-gateway.operations-console.dead-letters.replay',
            'actionKey' => 'uuid',
            'actionLabel' => __('Replay'),
        ])

        @include('settings.integrations.payments-gateway.launch-console.partials.incident-list', [
            'title' => __('Unmatched reconciliation'),
            'items' => $escalation['unmatched_reconciliation'] ?? [],
            'empty' => __('No unmatched transactions.'),
            'linkRoute' => 'settings.payments-gateway.operations-console.unmatched-transactions.show',
            'linkKey' => 'uuid',
        ])

        @include('settings.integrations.payments-gateway.launch-console.partials.incident-list', [
            'title' => __('Treasury alerts'),
            'items' => $escalation['treasury_alerts'] ?? [],
            'empty' => __('No open treasury alerts.'),
            'linkRoute' => 'settings.payments-gateway.operations-console.treasury-alerts.show',
            'linkKey' => 'uuid',
        ])

        @include('settings.integrations.payments-gateway.launch-console.partials.incident-list', [
            'title' => __('Failed callbacks'),
            'items' => $escalation['failed_callbacks'] ?? [],
            'empty' => __('No failed callbacks.'),
            'linkRoute' => 'settings.payments-gateway.operations-console.callback-logs.show',
            'linkKey' => 'uuid',
            'actionRoute' => 'settings.payments-gateway.operations-console.callback-logs.retry',
            'actionKey' => 'uuid',
            'actionLabel' => __('Retry'),
        ])
    </div>

    <div class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-xs dark:border-slate-800 dark:bg-slate-950/40">
        <p class="font-semibold text-slate-700 dark:text-slate-200">{{ __('Queue incidents') }}</p>
        <p class="mt-1 text-slate-600 dark:text-slate-300">
            {{ __('Dead letters: :dead · Stuck jobs: :stuck · Failed jobs: :failed', [
                'dead' => $escalation['queue_incidents']['dead_letters'] ?? 0,
                'stuck' => $escalation['queue_incidents']['stuck_jobs'] ?? 0,
                'failed' => $escalation['queue_incidents']['failed_jobs'] ?? 0,
            ]) }}
        </p>
        <a href="{{ route('settings.payments-gateway.operations-console') }}#incident-panels" class="mt-2 inline-flex font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Open Operations Console') }}</a>
    </div>
</div>
