@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'success' => 'success',
        'pending', 'processing' => 'warning',
        'failed', 'cancelled', 'timeout' => 'danger',
        default => 'neutral',
    };
    $formatJson = fn ($value) => is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Transaction detail')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($transaction)
            <x-admin.form-shell
                :title="__('Transaction :uuid', ['uuid' => substr($transactionUuid, 0, 8).'…'])"
                :subtitle="$transaction['internal_reference'] ?? $transactionUuid"
                :back-href="route('settings.payments-gateway.transactions.index')"
                :back-label="__('Back to transactions')"
                badge="{{ __('Transactions') }}"
            >
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60 lg:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Transaction identity') }}</h3>
                            <x-ui.status-badge :variant="$statusVariant((string) ($transaction['status'] ?? 'unknown'))">{{ ucfirst((string) ($transaction['status'] ?? 'unknown')) }}</x-ui.status-badge>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('UUID'), 'value' => $transaction['uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Type'), 'value' => strtoupper($transaction['transaction_type'] ?? '—')])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Direction'), 'value' => strtoupper($transaction['direction'] ?? '—')])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Amount'), 'value' => ($transaction['currency'] ?? 'KES').' '.number_format((float) ($transaction['amount'] ?? 0), 2)])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Internal reference'), 'value' => $transaction['internal_reference'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('External reference'), 'value' => $transaction['external_reference'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('M-Pesa receipt'), 'value' => $transaction['mpesa_receipt_number'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Phone'), 'value' => $transaction['phone_number'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Result code'), 'value' => $transaction['result_code'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Result description'), 'value' => $transaction['result_desc'] ?? '—'])
                        </dl>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Scope & references') }}</h3>
                        <dl class="mt-4 space-y-3">
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Tenant UUID'), 'value' => $transaction['tenant_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Profile UUID'), 'value' => $transaction['payment_profile_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('PayBill UUID'), 'value' => $transaction['paybill_account_uuid'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Checkout request ID'), 'value' => $transaction['checkout_request_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Merchant request ID'), 'value' => $transaction['merchant_request_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Conversation ID'), 'value' => $transaction['conversation_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Originator conversation ID'), 'value' => $transaction['originator_conversation_id'] ?? '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Created at'), 'value' => filled($transaction['created_at'] ?? null) ? \Illuminate\Support\Carbon::parse($transaction['created_at'])->format('M j, Y H:i:s') : '—'])
                            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Processed at'), 'value' => filled($transaction['processed_at'] ?? null) ? \Illuminate\Support\Carbon::parse($transaction['processed_at'])->format('M j, Y H:i:s') : '—'])
                        </dl>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Request payload (redacted)'), 'payload' => $formatJson($transaction['request_payload'] ?? null)])
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Response payload (redacted)'), 'payload' => $formatJson($transaction['response_payload'] ?? null)])
                    @include('settings.integrations.payments-gateway.partials.json-panel', ['title' => __('Callback payload (redacted)'), 'payload' => $formatJson($transaction['callback_payload'] ?? null)])
                </div>
            </x-admin.form-shell>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Transaction could not be loaded from Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
