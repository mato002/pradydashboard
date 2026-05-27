@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'success' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook deliveries')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <form method="get" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Tenant UUID'), 'name' => 'tenant_uuid', 'value' => $filters['tenant_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Status'), 'name' => 'status', 'type' => 'select', 'value' => $filters['status'], 'options' => ['' => __('All statuses')] + collect($deliveryStatuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Event UUID'), 'name' => 'event_uuid', 'value' => $filters['event_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('From date'), 'name' => 'from_date', 'type' => 'date', 'value' => $filters['from_date']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('To date'), 'name' => 'to_date', 'type' => 'date', 'value' => $filters['to_date']])
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.webhook-deliveries.index') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Delivery') }}</th>
                            <th class="px-4 py-3">{{ __('Event') }}</th>
                            <th class="px-4 py-3">{{ __('Target URL') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('HTTP') }}</th>
                            <th class="px-4 py-3">{{ __('Response ms') }}</th>
                            <th class="px-4 py-3">{{ __('Delivered') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ filled($item['webhook_event_uuid'] ?? null) ? substr($item['webhook_event_uuid'], 0, 8).'…' : '—' }}</td>
                                <td class="px-4 py-3 max-w-xs truncate text-xs">{{ $item['target_url'] ?? '—' }}</td>
                                <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($item['delivery_status'] ?? 'unknown'))">{{ ucfirst((string) ($item['delivery_status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                <td class="px-4 py-3 tabular-nums">{{ $item['response_status'] ?? '—' }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $item['response_time_ms'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ filled($item['delivered_at'] ?? null) ? \Illuminate\Support\Carbon::parse($item['delivered_at'])->format('M j, H:i') : '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('settings.payments-gateway.webhook-deliveries.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a>
                                        @permission('payments_gateway.manage')
                                        <form method="post" action="{{ route('settings.payments-gateway.webhook-deliveries.redispatch', $item['uuid']) }}" onsubmit="return confirm(@js(__('Redispatch this webhook delivery?')))">
                                            @csrf
                                            <button type="submit" class="text-left text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Redispatch') }}</button>
                                        </form>
                                        @endpermission
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">{{ $gatewayUnavailable ? __('No data while Payments Gateway is unavailable.') : __('No webhook deliveries found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('settings.integrations.payments-gateway.partials.pagination', ['pagination' => $pagination])
        </div>
    </div>
</x-dashboard-layout>
