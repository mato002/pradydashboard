<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Treasury alert investigation')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($resource)
            @include('settings.integrations.payments-gateway.operations-console.partials.investigation-risk-badge')

            <x-admin.form-shell
                :title="$resource['title'] ?? __('Treasury alert :uuid', ['uuid' => substr($uuid, 0, 8).'…'])"
                :subtitle="$resource['alert_type'] ?? $uuid"
                :back-href="route('settings.payments-gateway.operations-console').'#incident-panels'"
                :back-label="__('Back to Operations Console')"
                badge="{{ __('Incident investigation') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Alert identity') }}</h3>
                                <x-ui.status-badge :variant="$statusVariant((string) ($resource['status'] ?? 'unknown'))">{{ ucfirst((string) ($resource['status'] ?? 'unknown')) }}</x-ui.status-badge>
                            </div>
                            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $resource['uuid'] ?? '—'])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Alert type'), 'value' => $resource['alert_type'] ?? '—'])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Severity'), 'value' => ucfirst((string) ($resource['severity'] ?? '—'))])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Triggered by'), 'value' => $resource['triggered_by'] ?? '—'])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Message'), 'value' => $resource['message'] ?? $resource['description'] ?? '—'])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Created at'), 'value' => $formatTimestamp($resource['created_at'] ?? null)])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Acknowledged at'), 'value' => $formatTimestamp($resource['acknowledged_at'] ?? null)])
                                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Resolved at'), 'value' => $formatTimestamp($resource['resolved_at'] ?? null)])
                            </dl>
                        </div>

                        @if (filled($resource['acknowledge_comments'] ?? null) || filled($resource['resolve_comments'] ?? null))
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Workflow comments') }}</h3>
                                <dl class="mt-4 space-y-3">
                                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Acknowledge comments'), 'value' => $resource['acknowledge_comments'] ?? '—'])
                                    @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Resolve comments'), 'value' => $resource['resolve_comments'] ?? '—'])
                                </dl>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-tenant-impact')
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-actions', [
                            'type' => 'treasury_alert',
                            'uuid' => $uuid,
                            'quickActions' => $quickActions,
                        ])
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-related-records')
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-recommended-actions')

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Evidence (redacted)'),
                        'payload' => $formatJson($resource['evidence'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                        'redacted' => true,
                    ])
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Metadata'),
                        'payload' => $formatJson($resource['metadata'] ?? null),
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
