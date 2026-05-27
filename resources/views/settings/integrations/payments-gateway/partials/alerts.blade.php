@if ($gatewayContractWarning ?? false)
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <p class="font-semibold">{{ __('Payments Gateway API contract warning') }}</p>
        <p class="mt-1">{{ $gatewayContractWarning }}</p>
    </div>
@endif

@if ($gatewayUnavailable ?? false)
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
        <p class="font-semibold">{{ __('Payments Gateway unavailable') }}</p>
        <p class="mt-1">{{ $gatewayMessage ?? __('Unable to reach payments.pradytecai.com.') }}</p>
    </div>
@endif

@if (session('status'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
        {{ session('status') }}
    </div>
@endif

@if (session('bulk_action_errors'))
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <p class="font-semibold">{{ __('Some bulk actions failed') }}</p>
        <ul class="mt-2 space-y-1 font-mono text-xs">
            @foreach (session('bulk_action_errors') as $error)
                <li>{{ substr($error['uuid'] ?? '', 0, 8) }}… — {{ $error['message'] ?? __('Gateway request failed.') }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('gateway_error'))
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        {{ session('gateway_error') }}
    </div>
@endif
