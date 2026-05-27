@php
    $accountTypeOptions = collect($accountTypes)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all();
    $environmentOptions = collect($environments)->mapWithKeys(fn ($e) => [$e => ucfirst($e)])->all();
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Edit PayBill account')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            {{ __('Leave credential fields blank to keep existing values on the gateway. Dashboard never stores Daraja secrets.') }}
        </div>

        <x-admin.form-shell
            :title="__('Edit PayBill account')"
            :subtitle="$account['account_name'] ?? $accountUuid"
            :back-href="route('settings.payments-gateway.payment-profiles.paybill-accounts.index', $profileUuid)"
            :back-label="__('Back to PayBills')"
            badge="{{ __('PayBill Accounts') }}"
        >
            @if ($account)
                <form method="post" action="{{ route('settings.payments-gateway.paybill-accounts.update', $accountUuid) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <div class="grid gap-4 md:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account name'), 'name' => 'account_name', 'required' => true, 'value' => $account['account_name'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account code'), 'name' => 'account_code', 'required' => true, 'value' => $account['account_code'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Account type'), 'name' => 'account_type', 'type' => 'select', 'required' => true, 'value' => $account['account_type'] ?? '', 'options' => $accountTypeOptions])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Shortcode'), 'name' => 'shortcode', 'required' => true, 'value' => $account['shortcode'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('STK shortcode'), 'name' => 'stk_shortcode', 'value' => $account['stk_shortcode'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Environment'), 'name' => 'environment', 'type' => 'select', 'value' => $account['environment'] ?? 'sandbox', 'options' => $environmentOptions])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Purpose'), 'name' => 'purpose', 'value' => $account['purpose'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Branch code'), 'name' => 'branch_code', 'value' => $account['branch_code'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Branch name'), 'name' => 'branch_name', 'value' => $account['branch_name'] ?? ''])
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            @foreach (['supports_stk' => 'STK', 'supports_c2b' => 'C2B', 'supports_b2c' => 'B2C', 'supports_b2b' => 'B2B', 'supports_reversal' => 'Reversal'] as $field => $label)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $account[$field] ?? false)) class="rounded border-slate-300">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Daraja credentials') }}</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Consumer key'), 'name' => 'consumer_key', 'placeholder' => __('Configured: :value', ['value' => $account['consumer_key'] ?? '—']), 'hint' => __('Leave blank to keep current value')])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Consumer secret'), 'name' => 'consumer_secret', 'placeholder' => __('Configured: :value', ['value' => $account['consumer_secret'] ?? '—'])])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Passkey'), 'name' => 'passkey', 'placeholder' => __('Configured: :value', ['value' => $account['passkey'] ?? '—'])])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Initiator name'), 'name' => 'initiator_name', 'placeholder' => __('Configured: :value', ['value' => $account['initiator_name'] ?? '—'])])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Security credential'), 'name' => 'security_credential', 'type' => 'textarea', 'placeholder' => __('Configured: :value', ['value' => $account['security_credential'] ?? '—'])])
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <div class="grid gap-4 md:grid-cols-2">
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Validation URL'), 'name' => 'validation_url', 'value' => $account['validation_url'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Confirmation URL'), 'name' => 'confirmation_url', 'value' => $account['confirmation_url'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('STK callback URL'), 'name' => 'stk_callback_url', 'value' => $account['stk_callback_url'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C result URL'), 'name' => 'b2c_result_url', 'value' => $account['b2c_result_url'] ?? ''])
                            @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C timeout URL'), 'name' => 'b2c_timeout_url', 'value' => $account['b2c_timeout_url'] ?? ''])
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        @if (($account['status'] ?? '') === 'active')
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.paybill-accounts.suspend', $accountUuid),
                                'label' => __('Suspend account'),
                                'confirm' => __('Suspend this active PayBill account? Live M-Pesa traffic for this shortcode may stop immediately.'),
                                'variant' => 'danger',
                            ])
                        @else
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.paybill-accounts.activate', $accountUuid),
                                'label' => __('Activate account'),
                                'confirm' => __('Activate this PayBill account on the Payments Gateway?'),
                            ])
                        @endif
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Save changes') }}</button>
                    </div>
                </form>
            @endif
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
