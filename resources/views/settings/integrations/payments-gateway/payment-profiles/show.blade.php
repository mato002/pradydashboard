@php
    use App\Support\PaymentsGateway\GatewayFormOptions;

    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active' => 'success',
        'suspended' => 'warning',
        default => 'neutral',
    };

    $defaultCollection = is_array($summary['default_collection_account'] ?? null)
        ? GatewayFormOptions::formatPaybillAccountLabel($summary['default_collection_account'])
        : GatewayFormOptions::formatPaybillAccountLabel(
            filled($profile['default_collection_account_uuid'] ?? null)
                ? ['uuid' => $profile['default_collection_account_uuid']]
                : null
        );

    $defaultDisbursement = is_array($summary['default_disbursement_account'] ?? null)
        ? GatewayFormOptions::formatPaybillAccountLabel($summary['default_disbursement_account'])
        : GatewayFormOptions::formatPaybillAccountLabel(
            filled($profile['default_disbursement_account_uuid'] ?? null)
                ? ['uuid' => $profile['default_disbursement_account_uuid']]
                : null
        );
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="$profile['name'] ?? __('Payment profile')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($profile)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $profile['name'] ?? '—' }}</h3>
                        <p class="text-sm text-slate-500">{{ $profile['code'] ?? '' }}</p>
                    </div>
                    <x-ui.status-badge :variant="$statusVariant((string) ($profile['status'] ?? 'unknown'))">
                        {{ ucfirst((string) ($profile['status'] ?? 'unknown')) }}
                    </x-ui.status-badge>
                </div>

                @if ($summary)
                    <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div><dt class="text-xs uppercase text-slate-500">{{ __('PayBill accounts') }}</dt><dd class="tabular-nums">{{ $summary['paybill_accounts_count'] ?? '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">{{ __('Active PayBill accounts') }}</dt><dd class="tabular-nums">{{ $summary['active_paybill_accounts_count'] ?? '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">{{ __('Webhook endpoints') }}</dt><dd class="tabular-nums">{{ $summary['webhook_endpoints_count'] ?? '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">{{ __('API keys') }}</dt><dd class="tabular-nums">{{ $summary['api_keys_count'] ?? '—' }}</dd></div>
                    </dl>
                @endif

                <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="text-xs uppercase text-slate-500">{{ __('Environment') }}</dt><dd>{{ $profile['environment'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">{{ __('Tenant') }}</dt><dd>{{ $tenant['name'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">{{ __('Default collection account') }}</dt><dd>{{ $defaultCollection }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">{{ __('Default disbursement account') }}</dt><dd>{{ $defaultDisbursement }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">{{ __('Webhook URL') }}</dt><dd class="break-all">{{ $profile['tenant_webhook_url'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">{{ __('Webhook secret') }}</dt><dd>{{ $profile['webhook_secret'] ?? '—' }}</dd></div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('settings.payments-gateway.payment-profiles.paybill-accounts.index', $profile['uuid']) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('View PayBill accounts') }}</a>
                    <a href="{{ route('settings.payments-gateway.payment-profiles.api-keys.index', $profile['uuid']) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">{{ __('Gateway API keys') }}</a>
                    <a href="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profile['uuid']) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">{{ __('Webhook endpoints') }}</a>
                    @permission('payments_gateway.manage')
                    <a href="{{ route('settings.payments-gateway.payment-profiles.edit', $profile['uuid']) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">{{ __('Edit profile') }}</a>
                    @endpermission
                    @if ($dashboardTenant)
                        <a href="{{ route('settings.payments-gateway.tenants.show', $dashboardTenant) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">{{ __('Back to treasury mapping') }}</a>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-500 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                {{ __('Payment profile could not be loaded from the Payments Gateway.') }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
