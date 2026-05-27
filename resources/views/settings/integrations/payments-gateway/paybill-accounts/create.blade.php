@php
    $accountTypeOptions = ['' => __('Select type')] + collect($accountTypes)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all();
    $environmentOptions = collect($environments)->mapWithKeys(fn ($e) => [$e => ucfirst($e)])->all();
    $backHref = ($returnToMapping ?? false) && isset($dashboardTenant)
        ? route('settings.payments-gateway.tenants.show', $dashboardTenant)
        : route('settings.payments-gateway.payment-profiles.paybill-accounts.index', $profileUuid);
    $backLabel = ($returnToMapping ?? false) && isset($dashboardTenant)
        ? __('Back to treasury mapping')
        : __('Back to PayBills');
    $storeRoute = ($returnToMapping ?? false) && isset($dashboardTenant)
        ? route('settings.payments-gateway.tenants.paybill-accounts.store', [$dashboardTenant, $profileUuid])
        : route('settings.payments-gateway.payment-profiles.paybill-accounts.store', $profileUuid);
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Create PayBill account')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            {{ __('Daraja credentials are sent directly to payments.pradytecai.com and are never stored in the dashboard database.') }}
        </div>

        <x-admin.form-shell
            :title="__('Create PayBill account')"
            :subtitle="$profile['name'] ?? $profileUuid"
            :back-href="$backHref"
            :back-label="$backLabel"
            badge="{{ __('PayBill Accounts') }}"
        >
            <form method="post" action="{{ $storeRoute }}" class="space-y-6">
                @csrf
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Account details') }}</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account name'), 'name' => 'account_name', 'required' => true])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account code'), 'name' => 'account_code', 'required' => true])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account type'), 'name' => 'account_type', 'type' => 'select', 'required' => true, 'options' => $accountTypeOptions])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Shortcode'), 'name' => 'shortcode', 'required' => true])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('STK shortcode'), 'name' => 'stk_shortcode'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Environment'), 'name' => 'environment', 'type' => 'select', 'value' => 'sandbox', 'options' => $environmentOptions])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Purpose'), 'name' => 'purpose'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Branch code'), 'name' => 'branch_code'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Branch name'), 'name' => 'branch_name'])
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach (['supports_stk' => 'STK', 'supports_c2b' => 'C2B', 'supports_b2c' => 'B2C', 'supports_b2b' => 'B2B', 'supports_reversal' => 'Reversal'] as $field => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field)) class="rounded border-slate-300">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Daraja credentials') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Stored only on payments.pradytecai.com') }}</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Consumer key'), 'name' => 'consumer_key'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Consumer secret'), 'name' => 'consumer_secret'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Passkey'), 'name' => 'passkey'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Initiator name'), 'name' => 'initiator_name'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Security credential'), 'name' => 'security_credential', 'type' => 'textarea'])
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Callback URLs') }}</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Validation URL'), 'name' => 'validation_url'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Confirmation URL'), 'name' => 'confirmation_url'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('STK callback URL'), 'name' => 'stk_callback_url'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C result URL'), 'name' => 'b2c_result_url'])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C timeout URL'), 'name' => 'b2c_timeout_url'])
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ $backHref }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create PayBill account') }}</button>
                </div>
            </form>
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
