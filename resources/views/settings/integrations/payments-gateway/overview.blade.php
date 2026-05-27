<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Overview')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $key => $card)
                <x-ui.kpi-card
                    :title="match ($key) {
                        'gateway_status' => __('Gateway status'),
                        'total_tenants' => __('Total tenants'),
                        'active_tenants' => __('Active tenants'),
                        'total_payment_profiles' => __('Payment profiles'),
                        'total_paybill_accounts' => __('PayBill accounts'),
                        'failed_callbacks' => __('Failed callbacks'),
                        'reconciliation_issues' => __('Reconciliation issues'),
                        'last_sync_time' => __('Last sync time'),
                        default => ucfirst(str_replace('_', ' ', $key)),
                    }"
                    :value="$card['value']"
                    :sublabel="$card['sublabel']"
                    :tone="$card['tone']"
                    :animate="false"
                />
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Quick links') }}</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('settings.payments-gateway.tenants.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Tenant Profiles') }}
                </a>
                <a href="{{ route('settings.payments-gateway.health') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Gateway Health') }}
                </a>
            </div>
        </div>
    </div>
</x-dashboard-layout>
