@php
    $environmentOptions = collect($environments)->mapWithKeys(fn ($e) => [$e => ucfirst($e)])->all();
    $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all();
    $accountSelectOptions = ['' => __('None')] + ($paybillAccountOptions ?? []);
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Create payment profile')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <x-admin.form-shell
            :title="__('Create payment profile')"
            :subtitle="$tenant['name'] ?? $dashboardTenant->company_name"
            :back-href="route('settings.payments-gateway.tenants.show', $dashboardTenant)"
            :back-label="__('Back to treasury mapping')"
            badge="{{ __('Payment Profiles') }}"
        >
            <form method="post" action="{{ route('settings.payments-gateway.tenants.payment-profiles.store', $dashboardTenant) }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Name'), 'name' => 'name', 'required' => true])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Code'), 'name' => 'code', 'required' => true])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Environment'), 'name' => 'environment', 'type' => 'select', 'value' => 'sandbox', 'options' => $environmentOptions])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Status'), 'name' => 'status', 'type' => 'select', 'value' => 'active', 'options' => $statusOptions])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Default collection account'), 'name' => 'default_collection_account_uuid', 'type' => 'select', 'options' => $accountSelectOptions, 'hint' => __('PayBill accounts can be linked after the profile is created.')])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Default disbursement account'), 'name' => 'default_disbursement_account_uuid', 'type' => 'select', 'options' => $accountSelectOptions, 'hint' => __('PayBill accounts can be linked after the profile is created.')])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Tenant webhook URL'), 'name' => 'tenant_webhook_url', 'hint' => __('Optional callback URL for tenant systems')])
                    @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Webhook secret'), 'name' => 'webhook_secret', 'hint' => __('Sent to gateway only — not stored in dashboard')])
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('settings.payments-gateway.tenants.show', $dashboardTenant) }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create profile') }}</button>
                </div>
            </form>
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
