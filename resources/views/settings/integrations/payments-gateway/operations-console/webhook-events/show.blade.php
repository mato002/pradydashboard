<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook event investigation')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($resource)
            @include('settings.integrations.payments-gateway.operations-console.partials.investigation-risk-badge')

            <x-admin.form-shell
                :title="__('Webhook event :uuid', ['uuid' => substr($uuid, 0, 8).'…'])"
                :subtitle="$resource['event_type'] ?? $uuid"
                :back-href="route('settings.payments-gateway.operations-console').'#incident-panels'"
                :back-label="__('Back to Operations Console')"
                badge="{{ __('Incident investigation') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Event summary') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($resource['status'] ?? 'unknown'))">{{ ucfirst((string) ($resource['status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $resource['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Event type'), 'value' => $resource['event_type'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Attempts'), 'value' => $resource['attempts'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Failed at'), 'value' => $formatTimestamp($resource['failed_at'] ?? null)])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Last attempt at'), 'value' => $formatTimestamp($resource['last_attempt_at'] ?? null)])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Transaction UUID'), 'value' => $resource['payment_transaction_uuid'] ?? '—'])
                        </dl>
                    </div>

                    <div class="space-y-4">
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-tenant-impact')
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-actions', [
                            'type' => 'webhook_event',
                            'uuid' => $uuid,
                            'quickActions' => $quickActions,
                        ])
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-related-records')
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-recommended-actions')

                @if (filled($resource['latest_delivery'] ?? null))
                    <div class="mt-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Latest delivery') }}</h3>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Delivery UUID'), 'value' => $resource['latest_delivery']['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Status'), 'value' => ucfirst((string) ($resource['latest_delivery']['delivery_status'] ?? $resource['latest_delivery']['status'] ?? '—'))])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Response status'), 'value' => $resource['latest_delivery']['response_status'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Error message'), 'value' => $resource['latest_delivery']['error_message'] ?? '—'])
                        </dl>
                    </div>
                @endif

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Error context'),
                        'payload' => $formatJson($resource['error_context'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                    ])
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Payload (redacted)'),
                        'payload' => $formatJson($resource['payload'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                        'redacted' => true,
                    ])
                </div>

                @if (! empty($resource['deliveries']))
                    <div class="mt-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Deliveries') }}</h3>
                        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-950/40">
                                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-3 py-2">{{ __('UUID') }}</th>
                                        <th class="px-3 py-2">{{ __('Status') }}</th>
                                        <th class="px-3 py-2">{{ __('Response') }}</th>
                                        <th class="px-3 py-2">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($resource['deliveries'] as $delivery)
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs">{{ substr((string) ($delivery['uuid'] ?? ''), 0, 8) }}…</td>
                                            <td class="px-3 py-2 text-xs">{{ ucfirst((string) ($delivery['delivery_status'] ?? $delivery['status'] ?? '—')) }}</td>
                                            <td class="px-3 py-2 text-xs">{{ $delivery['response_status'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-xs">
                                                @if (filled($delivery['uuid'] ?? null))
                                                    <a href="{{ route('settings.payments-gateway.operations-console.webhook-deliveries.show', $delivery['uuid']) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Investigate') }}</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $gatewayMessage ?? $unavailableMessage }}</p>
                <a href="{{ route('settings.payments-gateway.operations-console') }}#incident-panels" class="mt-4 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Back to Operations Console') }}</a>
            </div>
        @endif
    </div>
</x-dashboard-layout>
