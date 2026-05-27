@php
    $formatJson = fn ($value) => is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'success' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook delivery detail')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($delivery)
            <x-admin.form-shell
                :title="__('Webhook delivery :uuid', ['uuid' => substr($deliveryUuid, 0, 8).'…'])"
                :subtitle="$delivery['target_url'] ?? $deliveryUuid"
                :back-href="route('settings.payments-gateway.webhook-deliveries.index')"
                :back-label="__('Back to webhook deliveries')"
                badge="{{ __('Webhook Deliveries') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Delivery summary') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($delivery['delivery_status'] ?? 'unknown'))">{{ ucfirst((string) ($delivery['delivery_status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $delivery['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Event UUID'), 'value' => $delivery['webhook_event_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Endpoint UUID'), 'value' => $delivery['webhook_endpoint_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Target URL'), 'value' => $delivery['target_url'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('HTTP method'), 'value' => strtoupper($delivery['http_method'] ?? 'POST')])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Response status'), 'value' => $delivery['response_status'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Response time'), 'value' => filled($delivery['response_time_ms'] ?? null) ? $delivery['response_time_ms'].' ms' : '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Error message'), 'value' => $delivery['error_message'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Delivered at'), 'value' => filled($delivery['delivered_at'] ?? null) ? \Illuminate\Support\Carbon::parse($delivery['delivered_at'])->format('M j, Y H:i:s') : '—'])
                        </dl>
                        @if (filled($delivery['webhook_event_uuid'] ?? null))
                            <a href="{{ route('settings.payments-gateway.webhook-events.show', $delivery['webhook_event_uuid']) }}" class="mt-4 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View webhook event') }}</a>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Redispatch') }}</h3>
                        @permission('payments_gateway.manage')
                        <form method="post" action="{{ route('settings.payments-gateway.webhook-deliveries.redispatch', $deliveryUuid) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white" onclick="return confirm(@js(__('Redispatch this webhook delivery?')))">{{ __('Redispatch delivery') }}</button>
                        </form>
                        @else
                        <p class="mt-4 text-sm text-slate-500">{{ __('Redispatch requires payments_gateway.manage permission.') }}</p>
                        @endpermission
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Request headers (redacted)'), 'payload' => $formatJson($delivery['request_headers'] ?? null)])
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Request payload'), 'payload' => $formatJson($delivery['request_payload'] ?? null)])
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Response headers (redacted)'), 'payload' => $formatJson($delivery['response_headers'] ?? null)])
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Response body') }}</h3>
                        <pre class="mt-3 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ $delivery['response_body'] ?? '—' }}</pre>
                    </div>
                </div>
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Webhook delivery could not be loaded from Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
