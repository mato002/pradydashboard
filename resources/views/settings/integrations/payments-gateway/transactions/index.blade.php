@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'success' => 'success',
        'pending', 'processing' => 'warning',
        'failed', 'cancelled', 'timeout' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Transactions')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <form method="get" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Tenant UUID'), 'name' => 'tenant_uuid', 'value' => $filters['tenant_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Profile UUID'), 'name' => 'payment_profile_uuid', 'value' => $filters['payment_profile_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('PayBill UUID'), 'name' => 'paybill_account_uuid', 'value' => $filters['paybill_account_uuid']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Type'), 'name' => 'transaction_type', 'type' => 'select', 'value' => $filters['transaction_type'], 'options' => ['' => __('All types')] + collect($transactionTypes)->mapWithKeys(fn ($t) => [$t => strtoupper($t)])->all()])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Status'), 'name' => 'status', 'type' => 'select', 'value' => $filters['status'], 'options' => ['' => __('All statuses')] + collect($transactionStatuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all()])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Phone number'), 'name' => 'phone_number', 'value' => $filters['phone_number']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('M-Pesa receipt'), 'name' => 'mpesa_receipt_number', 'value' => $filters['mpesa_receipt_number']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('From date'), 'name' => 'from_date', 'type' => 'date', 'value' => $filters['from_date']])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('To date'), 'name' => 'to_date', 'type' => 'date', 'value' => $filters['to_date']])
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.transactions.index') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Transaction') }}</th>
                            <th class="px-4 py-3">{{ __('Tenant') }}</th>
                            <th class="px-4 py-3">{{ __('Profile') }}</th>
                            <th class="px-4 py-3">{{ __('Account') }}</th>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Direction') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Receipt') }}</th>
                            <th class="px-4 py-3">{{ __('Phone') }}</th>
                            <th class="px-4 py-3">{{ __('Reference') }}</th>
                            <th class="px-4 py-3">{{ __('Created') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['tenant_uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['payment_profile_uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ substr($item['paybill_account_uuid'] ?? '', 0, 8) }}…</td>
                                <td class="px-4 py-3">{{ strtoupper($item['transaction_type'] ?? '—') }}</td>
                                <td class="px-4 py-3">{{ strtoupper($item['direction'] ?? '—') }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ ($item['currency'] ?? 'KES').' '.number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                                <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($item['status'] ?? 'unknown'))">{{ ucfirst((string) ($item['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item['mpesa_receipt_number'] ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item['phone_number'] ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item['internal_reference'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ filled($item['created_at'] ?? null) ? \Illuminate\Support\Carbon::parse($item['created_at'])->format('M j, H:i') : '—' }}</td>
                                <td class="px-4 py-3"><a href="{{ route('settings.payments-gateway.transactions.show', $item['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="px-4 py-8 text-center text-slate-500">{{ $gatewayUnavailable ? __('No data while Payments Gateway is unavailable.') : __('No transactions found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('settings.integrations.payments-gateway.partials.pagination', ['pagination' => $pagination])
        </div>
    </div>
</x-dashboard-layout>
