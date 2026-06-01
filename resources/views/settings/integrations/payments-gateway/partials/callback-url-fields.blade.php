@php
    $callbackValues = $callbackUrlDefaults ?? [];
    $canonicalUrls = $canonicalCallbackUrls ?? [];
    $showUseCanonical = \App\Support\PaymentsGateway\CanonicalCallbackUrls::fieldsDifferFromCanonical($callbackValues);
    $hasLegacyUrls = \App\Support\PaymentsGateway\CanonicalCallbackUrls::hasLegacyUrls($callbackValues);
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Callback URLs') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Use public /pay/* URLs registered with Safaricom. The dashboard sends updates to payments.pradytecai.com only.') }}</p>
        </div>
        @if ($showUseCanonical)
            <button
                type="button"
                id="use-canonical-callback-urls"
                data-canonical-urls='@json($canonicalUrls)'
                class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200"
            >
                {{ __('Use canonical URLs') }}
            </button>
        @endif
    </div>

    @if ($hasLegacyUrls)
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            {{ __('One or more callback URLs use the legacy internal /api/v1/callbacks/* path. Safaricom must receive the public /pay/* URLs instead.') }}
        </div>
    @endif

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Validation URL'), 'name' => 'validation_url', 'value' => $callbackValues['validation_url'] ?? ''])
        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Confirmation URL'), 'name' => 'confirmation_url', 'value' => $callbackValues['confirmation_url'] ?? ''])
        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('STK callback URL'), 'name' => 'stk_callback_url', 'value' => $callbackValues['stk_callback_url'] ?? ''])
        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C result URL'), 'name' => 'b2c_result_url', 'value' => $callbackValues['b2c_result_url'] ?? ''])
        @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('B2C timeout URL'), 'name' => 'b2c_timeout_url', 'value' => $callbackValues['b2c_timeout_url'] ?? ''])
    </div>
</div>

@if ($showUseCanonical)
    <script>
        document.getElementById('use-canonical-callback-urls')?.addEventListener('click', function () {
            const canonical = JSON.parse(this.dataset.canonicalUrls || '{}');

            Object.entries(canonical).forEach(([field, url]) => {
                const input = document.getElementById(field);
                if (input) {
                    input.value = url;
                }
            });
        });
    </script>
@endif
