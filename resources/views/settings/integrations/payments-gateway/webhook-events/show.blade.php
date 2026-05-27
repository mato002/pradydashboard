@php
    $formatJson = fn ($value) => is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'delivered', 'success' => 'success',
        'pending', 'queued', 'processing' => 'warning',
        'failed', 'cancelled' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook event detail')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($event)
            <x-admin.form-shell
                :title="__('Webhook event :uuid', ['uuid' => substr($eventUuid, 0, 8).'…'])"
                :subtitle="$event['event_type'] ?? $eventUuid"
                :back-href="route('settings.payments-gateway.webhook-events.index')"
                :back-label="__('Back to webhook events')"
                badge="{{ __('Webhook Events') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Event summary') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($event['status'] ?? 'unknown'))">{{ ucfirst((string) ($event['status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $event['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Event type'), 'value' => $event['event_type'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Event key'), 'value' => $event['event_key'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Tenant UUID'), 'value' => $event['tenant_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Transaction UUID'), 'value' => $event['transaction_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Attempts'), 'value' => $event['attempts'] ?? 0])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Delivered at'), 'value' => filled($event['delivered_at'] ?? null) ? \Illuminate\Support\Carbon::parse($event['delivered_at'])->format('M j, Y H:i:s') : '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Failed at'), 'value' => filled($event['failed_at'] ?? null) ? \Illuminate\Support\Carbon::parse($event['failed_at'])->format('M j, Y H:i:s') : '—'])
                        </dl>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Redispatch') }}</h3>
                        @permission('payments_gateway.manage')
                        <form method="post" action="{{ route('settings.payments-gateway.webhook-events.redispatch', $eventUuid) }}" class="mt-4 space-y-3">
                            @csrf
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="force" value="1" class="rounded border-slate-300">
                                {{ __('Force redispatch even if already delivered') }}
                            </label>
                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white" onclick="return confirm(@js(__('Redispatch this webhook event?')))">{{ __('Redispatch event') }}</button>
                        </form>
                        @else
                        <p class="mt-4 text-sm text-slate-500">{{ __('Redispatch requires payments_gateway.manage permission.') }}</p>
                        @endpermission
                    </div>
                </div>

                @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Payload (redacted)'), 'payload' => $formatJson($event['payload'] ?? null)])

                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Delivery history') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950/40">
                                <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-3">{{ __('Delivery') }}</th>
                                    <th class="px-4 py-3">{{ __('Target URL') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3">{{ __('HTTP') }}</th>
                                    <th class="px-4 py-3">{{ __('Response ms') }}</th>
                                    <th class="px-4 py-3">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($deliveries as $delivery)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs">{{ substr($delivery['uuid'] ?? '', 0, 8) }}…</td>
                                        <td class="px-4 py-3 max-w-xs truncate text-xs">{{ $delivery['target_url'] ?? '—' }}</td>
                                        <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($delivery['delivery_status'] ?? 'unknown'))">{{ ucfirst((string) ($delivery['delivery_status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                        <td class="px-4 py-3 tabular-nums">{{ $delivery['response_status'] ?? '—' }}</td>
                                        <td class="px-4 py-3 tabular-nums">{{ $delivery['response_time_ms'] ?? '—' }}</td>
                                        <td class="px-4 py-3"><a href="{{ route('settings.payments-gateway.webhook-deliveries.show', $delivery['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No deliveries recorded for this event.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Webhook event could not be loaded from Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
