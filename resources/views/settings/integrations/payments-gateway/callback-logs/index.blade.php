@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'processed', 'matched' => 'success',
        'received' => 'warning',
        'failed', 'duplicate', 'ignored' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Callback logs')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <form method="get" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Callback type'), 'name' => 'callback_type', 'type' => 'select', 'value' => $filters['callback_type'], 'options' => ['' => __('All types')] + collect($callbackTypes)->mapWithKeys(fn ($t) => [$t => $t])->all()])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Processing status'), 'name' => 'processing_status', 'type' => 'select', 'value' => $filters['processing_status'], 'options' => ['' => __('All statuses')] + collect($processingStatuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Tenant UUID'), 'name' => 'tenant_uuid', 'value' => $filters['tenant_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('M-Pesa receipt'), 'name' => 'mpesa_receipt_number', 'value' => $filters['mpesa_receipt_number']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Checkout request ID'), 'name' => 'checkout_request_id', 'value' => $filters['checkout_request_id']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Conversation ID'), 'name' => 'conversation_id', 'value' => $filters['conversation_id']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('From date'), 'name' => 'from_date', 'type' => 'date', 'value' => $filters['from_date']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('To date'), 'name' => 'to_date', 'type' => 'date', 'value' => $filters['to_date']])
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.callback-logs.index') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Callback') }}</th>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Transaction') }}</th>
                            <th class="px-4 py-3">{{ __('Receipt') }}</th>
                            <th class="px-4 py-3">{{ __('Reference') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('Phone') }}</th>
                            <th class="px-4 py-3">{{ __('Received') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3">{{ $item['callback_type'] ?? '—' }}</td>
                                <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($item['processing_status'] ?? 'unknown'))">{{ ucfirst((string) ($item['processing_status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                <td class="px-4 py-3 font-mono text-xs">{{ filled($item['payment_transaction_uuid'] ?? null) ? substr($item['payment_transaction_uuid'], 0, 8).'…' : '—' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item['mpesa_receipt_number'] ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item['checkout_request_id'] ?? $item['conversation_id'] ?? '—' }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                                <td class="px-4 py-3">{{ $item['phone_number'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ filled($item['received_at'] ?? null) ? \Illuminate\Support\Carbon::parse($item['received_at'])->format('M j, H:i') : '—' }}</td>
                                <td class="px-4 py-3"><a href="{{ route('settings.payments-gateway.callback-logs.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-4 py-8 text-center text-slate-500">{{ $gatewayUnavailable ? __('No data while Payments Gateway is unavailable.') : __('No callback logs found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('settings.integrations.payments-gateway.partials.pagination', ['pagination' => $pagination])
        </div>
    </div>
</x-dashboard-layout>
