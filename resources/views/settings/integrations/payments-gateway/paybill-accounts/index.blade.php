@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active' => 'success',
        'suspended' => 'warning',
        default => 'neutral',
    };

    $formatDate = fn (?string $value): string => filled($value) ? \Illuminate\Support\Carbon::parse($value)->format('M j, Y H:i') : '—';
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('PayBill Accounts')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($profile)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900/80">
                <span>
                    {{ __('Payment profile') }}:
                    <a href="{{ route('settings.payments-gateway.payment-profiles.show', $profileUuid) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $profile['name'] ?? $profileUuid }}</a>
                </span>
                @permission('payments_gateway.manage')
                <a href="{{ route('settings.payments-gateway.payment-profiles.paybill-accounts.create', $profileUuid) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create PayBill account') }}</a>
                @endpermission
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Account') }}</th>
                            <th class="px-4 py-3">{{ __('Shortcode') }}</th>
                            <th class="px-4 py-3">{{ __('STK shortcode') }}</th>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Services') }}</th>
                            <th class="px-4 py-3">{{ __('Environment') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Branch / purpose') }}</th>
                            <th class="px-4 py-3">{{ __('Last OAuth') }}</th>
                            <th class="px-4 py-3">{{ __('Last transaction') }}</th>
                            @permission('payments_gateway.manage')<th class="px-4 py-3">{{ __('Actions') }}</th>@endpermission
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($accounts as $account)
                            @php
                                $services = collect([
                                    'STK' => $account['supports_stk'] ?? false,
                                    'C2B' => $account['supports_c2b'] ?? false,
                                    'B2C' => $account['supports_b2c'] ?? false,
                                    'B2B' => $account['supports_b2b'] ?? false,
                                    'Reversal' => $account['supports_reversal'] ?? false,
                                ])->filter()->keys()->implode(', ');
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $account['account_name'] ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $account['account_code'] ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 tabular-nums">{{ $account['shortcode'] ?? '—' }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $account['stk_shortcode'] ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $account['account_type'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $services !== '' ? $services : '—' }}</td>
                                <td class="px-4 py-3">{{ $account['environment'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.status-badge :variant="$statusVariant((string) ($account['status'] ?? 'unknown'))">
                                        {{ ucfirst((string) ($account['status'] ?? 'unknown')) }}
                                    </x-ui.status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div>{{ $account['branch_name'] ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $account['purpose'] ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $formatDate($account['last_oauth_check_at'] ?? null) }}</td>
                                <td class="px-4 py-3 text-xs">{{ $formatDate($account['last_transaction_at'] ?? null) }}</td>
                                @permission('payments_gateway.manage')
                                <td class="px-4 py-3">
                                    <a href="{{ route('settings.payments-gateway.paybill-accounts.edit', $account['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Edit') }}</a>
                                </td>
                                @endpermission
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-slate-500">{{ __('No PayBill accounts found for this profile.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-layout>
