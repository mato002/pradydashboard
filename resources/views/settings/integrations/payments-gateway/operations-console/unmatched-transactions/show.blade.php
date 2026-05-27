<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Unmatched transaction investigation')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($resource)
            @include('settings.integrations.payments-gateway.operations-console.partials.investigation-risk-badge')

            <x-admin.form-shell
                :title="__('Unmatched transaction :uuid', ['uuid' => substr($uuid, 0, 8).'…'])"
                :subtitle="$resource['reason'] ?? $resource['unmatched_reason'] ?? $uuid"
                :back-href="route('settings.payments-gateway.operations-console').'#incident-panels'"
                :back-label="__('Back to Operations Console')"
                badge="{{ __('Incident investigation') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Unmatched identity') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($resource['status'] ?? 'unknown'))">{{ ucfirst((string) ($resource['status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $resource['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Reason'), 'value' => $resource['reason'] ?? $resource['unmatched_reason'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Source UUID'), 'value' => $resource['source_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Source type'), 'value' => $resource['source_type'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Amount'), 'value' => filled($resource['amount'] ?? null) ? number_format((float) $resource['amount'], 2) : '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Created at'), 'value' => $formatTimestamp($resource['created_at'] ?? null)])
                        </dl>
                    </div>

                    <div class="space-y-4">
                        @include('settings.integrations.payments-gateway.operations-console.partials.investigation-tenant-impact')
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-related-records')
                @include('settings.integrations.payments-gateway.operations-console.partials.investigation-recommended-actions')

                @if (! empty($resource['suggested_matches']))
                    <div class="mt-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Suggested matches') }}</h3>
                        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-950/40">
                                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-3 py-2">{{ __('Transaction UUID') }}</th>
                                        <th class="px-3 py-2">{{ __('Callback UUID') }}</th>
                                        <th class="px-3 py-2">{{ __('Confidence') }}</th>
                                        <th class="px-3 py-2">{{ __('Reason') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($resource['suggested_matches'] as $match)
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs">
                                                @if (filled($match['transaction_uuid'] ?? null))
                                                    <a href="{{ route('settings.payments-gateway.transactions.show', $match['transaction_uuid']) }}" class="text-indigo-600 dark:text-indigo-400">{{ substr($match['transaction_uuid'], 0, 8) }}…</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 font-mono text-xs">
                                                @if (filled($match['callback_uuid'] ?? null))
                                                    <a href="{{ route('settings.payments-gateway.operations-console.callback-logs.show', $match['callback_uuid']) }}" class="text-indigo-600 dark:text-indigo-400">{{ substr($match['callback_uuid'], 0, 8) }}…</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 tabular-nums text-xs">{{ $match['confidence'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-xs">{{ $match['reason'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (! empty($resource['resolution_history']))
                    @include('settings.integrations.payments-gateway.partials.json-panel', [
                        'title' => __('Resolution history'),
                        'payload' => $formatJson($resource['resolution_history'] ?? null),
                        'collapsible' => true,
                        'copyable' => true,
                    ])
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
