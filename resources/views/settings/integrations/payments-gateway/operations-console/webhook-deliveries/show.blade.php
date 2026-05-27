<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook delivery investigation')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($resource)
            @if (! empty($investigation))
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-risk-badge')
            @endif

            <x-admin.form-shell
                :title="__('Webhook delivery :uuid', ['uuid' => substr($uuid, 0, 8).'…'])"
                :subtitle="$resource['target_url'] ?? $uuid"
                :back-href="route('settings.payments-gateway.operations-console').'#incident-panels'"
                :back-label="__('Back to Operations Console')"
                badge="{{ __('Incident investigation') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Delivery summary') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($resource['delivery_status'] ?? $resource['status'] ?? 'unknown'))">{{ ucfirst((string) ($resource['delivery_status'] ?? $resource['status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $resource['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Event UUID'), 'value' => $resource['webhook_event_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Target URL'), 'value' => $resource['target_url'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Response status'), 'value' => $resource['response_status'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Error message'), 'value' => $resource['error_message'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Delivered at'), 'value' => $formatTimestamp($resource['delivered_at'] ?? null)])
                        </dl>
                    </div>

                    <div class="space-y-4">
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-tenant-impact')
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-actions', [
                            'type' => 'webhook_delivery',
                            'uuid' => $uuid,
                            'quickActions' => $quickActions,
                        ])
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-related-records')
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-recommended-actions')

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Request headers (redacted)'),
                        'payload' => $formatJson($resource['request_headers'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                        'redacted' => true,
                    ])
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Request payload'),
                        'payload' => $formatJson($resource['request_payload'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                    ])
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Response headers (redacted)'),
                        'payload' => $formatJson($resource['response_headers'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                        'redacted' => true,
                    ])
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Response body'),
                        'payload' => $formatJson($resource['response_body'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                    ])
                </div>
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $gatewayMessage ?? $unavailableMessage }}</p>
                <a href="{{ route('settings.payments-gateway.operations-console') }}#incident-panels" class="mt-4 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Back to Operations Console') }}</a>
            </div>
        @endif
    </div>
</x-dashboard-layout>
