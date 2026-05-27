@if (! empty($tenantImpact))
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant impact') }}</h3>
        <dl class="mt-4 space-y-3">
            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Tenant'), 'value' => $tenantImpact['tenant_name'] ?? '—'])
            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Profile'), 'value' => $tenantImpact['payment_profile_label'] ?? '—'])
            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('PayBill'), 'value' => $tenantImpact['paybill_label'] ?? '—'])
            @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Tenant UUID'), 'value' => $tenantImpact['tenant_uuid'] ?? '—'])
        </dl>
        @if (filled($tenantImpact['tenant_mapping_url'] ?? null))
            <a href="{{ $tenantImpact['tenant_mapping_url'] }}" class="mt-4 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Treasury mapping') }}</a>
        @endif
    </div>
@endif
