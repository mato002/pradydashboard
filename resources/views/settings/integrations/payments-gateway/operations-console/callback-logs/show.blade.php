<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Callback investigation')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($resource)
            @if (! empty($investigation))
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-risk-badge')
            @endif

            <x-admin.form-shell
                :title="__('Callback :uuid', ['uuid' => substr($uuid, 0, 8).'…'])"
                :subtitle="$resource['callback_type'] ?? $uuid"
                :back-href="route('settings.payments-gateway.operations-console').'#incident-panels'"
                :back-label="__('Back to Operations Console')"
                badge="{{ __('Incident investigation') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Callback identity') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($resource['processing_status'] ?? 'unknown'))">{{ ucfirst((string) ($resource['processing_status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $resource['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Type'), 'value' => $resource['callback_type'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Processing error'), 'value' => $resource['processing_error'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Received at'), 'value' => $formatTimestamp($resource['received_at'] ?? null)])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Processed at'), 'value' => $formatTimestamp($resource['processed_at'] ?? null)])
                        </dl>
                    </div>

                    <div class="space-y-4">
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-tenant-impact')
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-actions', [
                            'type' => 'callback_log',
                            'uuid' => $uuid,
                            'quickActions' => $quickActions,
                        ])
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-related-records')
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-recommended-actions')

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Normalized payload'),
                        'payload' => $formatJson($resource['normalized_payload'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                    ])
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Raw payload (redacted)'),
                        'payload' => $formatJson($resource['raw_payload'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                        'redacted' => true,
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
