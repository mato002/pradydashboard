@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active' => 'success',
        'suspended' => 'warning',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Payment Profiles')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($tenant)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900/80">
                <span>
                    {{ __('Tenant') }}:
                    <a href="{{ route('settings.payments-gateway.tenants.show', $dashboardTenant) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $tenant['name'] ?? $dashboardTenant->company_name }}</a>
                </span>
                @permission('payments_gateway.manage')
                <a href="{{ route('settings.payments-gateway.tenants.payment-profiles.create', $dashboardTenant) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create profile') }}</a>
                @endpermission
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Profile') }}</th>
                            <th class="px-4 py-3">{{ __('Environment') }}</th>
                            <th class="px-4 py-3">{{ __('Default collection') }}</th>
                            <th class="px-4 py-3">{{ __('Default disbursement') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Webhook URL') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($profiles as $profile)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $profile['name'] ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $profile['code'] ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $profile['environment'] ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $profile['default_collection_account_uuid'] ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $profile['default_disbursement_account_uuid'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.status-badge :variant="$statusVariant((string) ($profile['status'] ?? 'unknown'))">
                                        {{ ucfirst((string) ($profile['status'] ?? 'unknown')) }}
                                    </x-ui.status-badge>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate text-xs text-slate-600 dark:text-slate-300">{{ $profile['tenant_webhook_url'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('settings.payments-gateway.payment-profiles.show', $profile['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View profile') }}</a>
                                        @permission('payments_gateway.manage')
                                        <a href="{{ route('settings.payments-gateway.payment-profiles.edit', $profile['uuid']) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Edit') }}</a>
                                        @endpermission
                                        <a href="{{ route('settings.payments-gateway.payment-profiles.paybill-accounts.index', $profile['uuid']) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('View PayBills') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ __('No payment profiles found for this tenant.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-layout>
