<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Operations Console')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if (! empty($missingApis))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                <p class="font-semibold">{{ __('Some gateway operations APIs are not available yet') }}</p>
                <p class="mt-1">{{ __('The console falls back to existing list endpoints where possible. Missing endpoints:') }}</p>
                <ul class="mt-2 list-inside list-disc font-mono text-xs">
                    @foreach ($missingApis as $endpoint)
                        <li>{{ $endpoint }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless ($gatewayUnavailable ?? false)
            @include('settings.integrations.payments-gateway.operations-console.partials.health-banner')
            @include('settings.integrations.payments-gateway.operations-console.partials.incident-panels')
            @include('settings.integrations.payments-gateway.operations-console.partials.activity-stream')
            @include('settings.integrations.payments-gateway.operations-console.partials.reconciliation-urgency')
        @endunless

        <div class="flex flex-wrap gap-1 rounded-xl border border-slate-200/80 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-900/80">
            @foreach ([
                ['href' => '#incident-panels', 'label' => __('Incidents')],
                ['href' => '#live-transactions', 'label' => __('Live Transactions')],
                ['href' => '#callback-health', 'label' => __('Callback Health')],
                ['href' => '#webhook-health', 'label' => __('Webhook Health')],
                ['href' => '#queue-health', 'label' => __('Queue Health')],
                ['href' => '#reconciliation', 'label' => __('Reconciliation')],
                ['href' => '#treasury-alerts', 'label' => __('Treasury Alerts')],
                ['href' => '#go-live-readiness', 'label' => __('Go-Live & Readiness')],
            ] as $tab)
                <a href="{{ $tab['href'] }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-indigo-400">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        {{-- 1. Live Transactions --}}
        <section id="live-transactions" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Live Transactions') }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Recent STK, C2B, and B2C activity from payments.pradytecai.com.') }}</p>
                </div>
                <a href="{{ route('settings.payments-gateway.transactions.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('All transactions') }} →</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (['pending', 'processing', 'success', 'failed', 'timeout'] as $status)
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ ucfirst($status) }}</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $transactions['status_counts'][$status] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            <form method="get" class="rounded-xl border border-slate-200/80 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="grid gap-3 md:grid-cols-3">
                    @include('settings.integrations.payments-gateway.partials.form-field', [
                        'label' => __('Type'),
                        'name' => 'transaction_type',
                        'type' => 'select',
                        'value' => $filters['transaction_type'],
                        'options' => ['' => __('All types')] + collect($transactionTypes)->mapWithKeys(fn ($t) => [$t => strtoupper($t)])->all(),
                    ])
                    @include('settings.integrations.payments-gateway.partials.form-field', [
                        'label' => __('Status'),
                        'name' => 'transaction_status',
                        'type' => 'select',
                        'value' => $filters['transaction_status'],
                        'options' => ['' => __('All statuses')] + collect($transactionStatuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all(),
                    ])
                    <div class="flex items-end gap-2">
                        <a href="{{ route('settings.payments-gateway.operations-console') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ __('Transaction') }}</th>
                            <th class="px-3 py-2">{{ __('Type') }}</th>
                            <th class="px-3 py-2">{{ __('Amount') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Created') }}</th>
                            <th class="px-3 py-2">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($transactions['recent'] as $item)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $shortUuid($item['uuid'] ?? null) }}</td>
                                <td class="px-3 py-2">{{ strtoupper($item['transaction_type'] ?? '—') }}</td>
                                <td class="px-3 py-2 tabular-nums">{{ ($item['currency'] ?? 'KES').' '.number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                                <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($item['status'] ?? 'unknown'))">{{ ucfirst((string) ($item['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                <td class="px-3 py-2 text-xs">{{ $formatTimestamp($item['created_at'] ?? null) }}</td>
                                <td class="px-3 py-2">
                                    @if (filled($item['uuid'] ?? null))
                                        <a href="{{ route('settings.payments-gateway.transactions.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ $gatewayUnavailable ? __('No data while Payments Gateway is unavailable.') : __('No recent transactions.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 2. Callback Health --}}
        <section id="callback-health" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Callback Health') }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Daraja callback processing status and anomalies.') }}</p>
                </div>
                <a href="{{ route('settings.payments-gateway.callback-logs.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('All callback logs') }} →</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['failed' => __('Failed'), 'duplicate' => __('Duplicates'), 'unmatched' => __('Unmatched'), 'malformed' => __('Malformed')] as $key => $label)
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $callbacks['counts'][$key] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Recent callbacks') }}</h4>
                    @include('settings.integrations.payments-gateway.operations-console.partials.callback-table', ['rows' => $callbacks['recent'], 'empty' => __('No recent callbacks.')])
                </div>
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Failed callbacks') }}</h4>
                    @include('settings.integrations.payments-gateway.operations-console.partials.callback-table', ['rows' => $callbacks['failed'], 'empty' => __('No failed callbacks.')])
                </div>
            </div>
        </section>

        {{-- 3. Webhook Health --}}
        <section id="webhook-health" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Webhook Health') }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Outbound tenant webhook events and delivery failures.') }}</p>
                </div>
                <div class="flex flex-wrap gap-3 text-xs font-semibold">
                    <a href="{{ route('settings.payments-gateway.webhook-events.index') }}" class="text-indigo-600 dark:text-indigo-400">{{ __('Events') }} →</a>
                    <a href="{{ route('settings.payments-gateway.webhook-deliveries.index') }}" class="text-indigo-600 dark:text-indigo-400">{{ __('Deliveries') }} →</a>
                    <a href="{{ route('settings.payments-gateway.tenants.index') }}" class="text-indigo-600 dark:text-indigo-400">{{ __('Endpoint tests') }} →</a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (['pending_events' => __('Pending events'), 'failed_events' => __('Failed events'), 'failed_deliveries' => __('Failed deliveries')] as $key => $label)
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $webhooks['counts'][$key] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Pending events') }}</h4>
                    @include('settings.integrations.payments-gateway.operations-console.partials.webhook-event-table', ['rows' => $webhooks['pending_events'], 'empty' => __('No pending webhook events.')])
                </div>
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Failed deliveries') }}</h4>
                    @include('settings.integrations.payments-gateway.operations-console.partials.webhook-delivery-table', ['rows' => $webhooks['failed_deliveries'], 'empty' => __('No failed deliveries.')])
                </div>
            </div>
        </section>

        {{-- 4. Queue Health --}}
        <section id="queue-health" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            @include('settings.integrations.payments-gateway.operations-console.partials.queue-workers')

            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Queue Health') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Worker status, dead letters, and stuck or failed jobs on the gateway.') }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Worker status') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $queue['worker_status'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Dead letters') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $queue['dead_letters'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Stuck jobs') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $queue['stuck_jobs'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Failed jobs') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $queue['failed_jobs'] }}</p>
                </div>
            </div>

            @if (empty($queue['overview']) && ! $gatewayUnavailable)
                <p class="rounded-xl border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500 dark:border-slate-700">{{ __('Queue overview API not available yet — counts shown when the gateway exposes GET /api/v1/operations/queue/overview.') }}</p>
            @endif
        </section>

        {{-- 5. Reconciliation Snapshot --}}
        <section id="reconciliation" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Reconciliation Snapshot') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Latest reconciliation runs, unmatched transactions, and settlement status.') }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Latest variance count') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $reconciliation['variance_count'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Settlement status') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $reconciliation['settlement_status'] }}</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Latest reconciliation runs') }}</h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950/40">
                                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-3 py-2">{{ __('Run') }}</th>
                                    <th class="px-3 py-2">{{ __('Status') }}</th>
                                    <th class="px-3 py-2">{{ __('Started') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($reconciliation['runs'] as $run)
                                    <tr>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $shortUuid($run['uuid'] ?? $run['id'] ?? null) }}</td>
                                        <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($run['status'] ?? 'unknown'))">{{ ucfirst((string) ($run['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                        <td class="px-3 py-2 text-xs">{{ $formatTimestamp($run['started_at'] ?? $run['created_at'] ?? null) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">{{ __('No reconciliation runs loaded.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Unmatched transactions') }}</h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950/40">
                                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-3 py-2">{{ __('Transaction') }}</th>
                                    <th class="px-3 py-2">{{ __('Reason') }}</th>
                                    <th class="px-3 py-2">{{ __('Detected') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($reconciliation['unmatched'] as $item)
                                    <tr>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $shortUuid($item['transaction_uuid'] ?? $item['uuid'] ?? null) }}</td>
                                        <td class="px-3 py-2 text-xs">{{ $item['reason'] ?? $item['variance_reason'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-xs">{{ $formatTimestamp($item['detected_at'] ?? $item['created_at'] ?? null) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">{{ __('No unmatched transactions loaded.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. Treasury Alerts --}}
        <section id="treasury-alerts" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Treasury Alerts') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Critical, high-risk, and unresolved fraud flags requiring operator attention.') }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 dark:border-rose-900 dark:bg-rose-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">{{ __('Critical') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-rose-900 dark:text-rose-100">{{ $alerts['critical'] }}</p>
                </div>
                <div class="rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 dark:border-amber-900 dark:bg-amber-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('High risk') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-900 dark:text-amber-100">{{ $alerts['high_risk'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Unresolved fraud') }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $alerts['unresolved_fraud'] }}</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ __('Alert') }}</th>
                            <th class="px-3 py-2">{{ __('Severity') }}</th>
                            <th class="px-3 py-2">{{ __('Category') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($alerts['items'] as $alert)
                            <tr>
                                <td class="px-3 py-2">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $alert['title'] ?? $alert['message'] ?? $shortUuid($alert['uuid'] ?? null) }}</p>
                                    @if (filled($alert['description'] ?? null))
                                        <p class="mt-0.5 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit((string) $alert['description'], 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($alert['severity'] ?? 'unknown'))">{{ ucfirst((string) ($alert['severity'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                <td class="px-3 py-2 text-xs">{{ ucfirst((string) ($alert['category'] ?? '—')) }}</td>
                                <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($alert['status'] ?? 'open'))">{{ ucfirst((string) ($alert['status'] ?? 'open')) }}</x-ui.status-badge></td>
                                <td class="px-3 py-2 text-xs">
                                    @if (filled($alert['acknowledge_url'] ?? null))
                                        <a href="{{ $alert['acknowledge_url'] }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Acknowledge') }}</a>
                                    @elseif (filled($alert['resolve_url'] ?? null))
                                        <a href="{{ $alert['resolve_url'] }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Resolve') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">{{ __('No open treasury alerts loaded.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 7. Go-Live & Readiness --}}
        <section id="go-live-readiness" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Go-Live & Readiness') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Shortcuts to production readiness and go-live dry-run reports.') }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Last production readiness') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $readiness['last_production_readiness_status'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Last go-live dry run') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $readiness['last_go_live_dry_run_status'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Last run') }}</p>
                    <p class="mt-1 text-sm text-slate-900 dark:text-white">{{ $formatTimestamp($readiness['last_run_at']) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('settings.payments-gateway.production-readiness') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Production Readiness') }}
                </a>
                <a href="{{ route('settings.payments-gateway.go-live-dry-run') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Go-Live Dry Run') }}
                </a>
                <a href="{{ route('settings.payments-gateway.health') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Gateway Health') }}
                </a>
            </div>
        </section>
    </div>
</x-dashboard-layout>
