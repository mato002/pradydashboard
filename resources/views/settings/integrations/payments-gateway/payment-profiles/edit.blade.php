@php
    $environmentOptions = collect($environments)->mapWithKeys(fn ($e) => [$e => ucfirst($e)])->all();
    $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s => ucfirst($s)])->all();
    $accountSelectOptions = ['' => __('None')] + ($paybillAccountOptions ?? []);
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Edit payment profile')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <x-admin.form-shell
            :title="__('Edit payment profile')"
            :subtitle="$profile['name'] ?? $profileUuid"
            :back-href="route('settings.payments-gateway.payment-profiles.show', $profileUuid)"
            :back-label="__('Back to profile')"
            badge="{{ __('Payment Profiles') }}"
        >
            @if ($profile)
                <form method="post" action="{{ route('settings.payments-gateway.payment-profiles.update', $profileUuid) }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Name'), 'name' => 'name', 'required' => true, 'value' => $profile['name'] ?? ''])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Code'), 'name' => 'code', 'required' => true, 'value' => $profile['code'] ?? ''])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Environment'), 'name' => 'environment', 'type' => 'select', 'value' => $profile['environment'] ?? 'sandbox', 'options' => $environmentOptions])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Status'), 'name' => 'status', 'type' => 'select', 'value' => $profile['status'] ?? 'active', 'options' => $statusOptions])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Default collection account'), 'name' => 'default_collection_account_uuid', 'type' => 'select', 'value' => $profile['default_collection_account_uuid'] ?? '', 'options' => $accountSelectOptions, 'hint' => __('Account name · shortcode · type')])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Default disbursement account'), 'name' => 'default_disbursement_account_uuid', 'type' => 'select', 'value' => $profile['default_disbursement_account_uuid'] ?? '', 'options' => $accountSelectOptions, 'hint' => __('Account name · shortcode · type')])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Tenant webhook URL'), 'name' => 'tenant_webhook_url', 'value' => $profile['tenant_webhook_url'] ?? ''])
                        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Webhook secret'), 'name' => 'webhook_secret', 'placeholder' => __('Leave blank to keep configured secret'), 'hint' => __('Current: :value', ['value' => $profile['webhook_secret'] ?? __('not set')])])
                    </div>
                    <div class="mt-6 flex flex-wrap justify-end gap-2">
                        @if (($profile['status'] ?? '') === 'active')
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.payment-profiles.suspend', $profileUuid),
                                'label' => __('Suspend profile'),
                                'confirm' => __('Suspend this payment profile? Active collections and disbursements may stop.'),
                                'variant' => 'danger',
                            ])
                        @else
                            @include('settings.integrations.payments-gateway.partials.action-form', [
                                'action' => route('settings.payments-gateway.payment-profiles.activate', $profileUuid),
                                'label' => __('Activate profile'),
                                'confirm' => __('Activate this payment profile on the Payments Gateway?'),
                            ])
                        @endif
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Save changes') }}</button>
                    </div>
                </form>
            @endif
        </x-admin.form-shell>
    </div>
</x-dashboard-layout>
