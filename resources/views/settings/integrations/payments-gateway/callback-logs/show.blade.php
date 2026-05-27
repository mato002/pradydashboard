@php
    $formatJson = fn ($value) => is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'processed', 'matched' => 'success',
        'received' => 'warning',
        'failed', 'duplicate', 'ignored' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Callback log detail')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($callbackLog)
            <x-admin.form-shell
                :title="__('Callback :uuid', ['uuid' => substr($callbackLogUuid, 0, 8).'…'])"
                :subtitle="$callbackLog['callback_type'] ?? $callbackLogUuid"
                :back-href="route('settings.payments-gateway.callback-logs.index')"
                :back-label="__('Back to callback logs')"
                badge="{{ __('Callback Logs') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Callback identity') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($callbackLog['processing_status'] ?? 'unknown'))">{{ ucfirst((string) ($callbackLog['processing_status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $callbackLog['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Type'), 'value' => $callbackLog['callback_type'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Source'), 'value' => $callbackLog['source'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('M-Pesa receipt'), 'value' => $callbackLog['mpesa_receipt_number'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Phone'), 'value' => $callbackLog['phone_number'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Amount'), 'value' => number_format((float) ($callbackLog['amount'] ?? 0), 2)])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Result'), 'value' => ($callbackLog['result_code'] ?? '—').' · '.($callbackLog['result_desc'] ?? '—')])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Processing error'), 'value' => $callbackLog['processing_error'] ?? '—'])
                        </dl>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Matching transaction') }}</h3>
                        <dl class="mt-4 space-y-3">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Transaction UUID'), 'value' => $callbackLog['payment_transaction_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Checkout request ID'), 'value' => $callbackLog['checkout_request_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Conversation ID'), 'value' => $callbackLog['conversation_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Received at'), 'value' => filled($callbackLog['received_at'] ?? null) ? \Illuminate\Support\Carbon::parse($callbackLog['received_at'])->format('M j, Y H:i:s') : '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Processed at'), 'value' => filled($callbackLog['processed_at'] ?? null) ? \Illuminate\Support\Carbon::parse($callbackLog['processed_at'])->format('M j, Y H:i:s') : '—'])
                        </dl>
                        @if (filled($callbackLog['payment_transaction_uuid'] ?? null))
                            <a href="{{ route('settings.payments-gateway.transactions.show', $callbackLog['payment_transaction_uuid']) }}" class="mt-4 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View transaction') }}</a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Normalized payload'), 'payload' => $formatJson($callbackLog['normalized_payload'] ?? null)])
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Raw payload (redacted)'), 'payload' => $formatJson($callbackLog['raw_payload'] ?? null)])
                </div>
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Callback log could not be loaded from Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
