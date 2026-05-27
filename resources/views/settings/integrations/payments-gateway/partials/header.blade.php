@php
    $items = [
        ['route' => 'settings.payments-gateway.overview', 'label' => __('Overview'), 'pattern' => 'settings.payments-gateway.overview'],
        ['route' => 'settings.payments-gateway.tenants.index', 'label' => __('Treasury Mapping'), 'pattern' => 'settings.payments-gateway.tenants.*'],
        ['route' => 'settings.payments-gateway.operations-console', 'label' => __('Operations Console'), 'pattern' => 'settings.payments-gateway.operations-console'],
        ['route' => 'settings.payments-gateway.transactions.index', 'label' => __('Transactions'), 'pattern' => 'settings.payments-gateway.transactions.*'],
        ['route' => 'settings.payments-gateway.callback-logs.index', 'label' => __('Callback Logs'), 'pattern' => 'settings.payments-gateway.callback-logs.*'],
        ['route' => 'settings.payments-gateway.webhook-events.index', 'label' => __('Webhook Events'), 'pattern' => 'settings.payments-gateway.webhook-events.*'],
        ['route' => 'settings.payments-gateway.webhook-deliveries.index', 'label' => __('Webhook Deliveries'), 'pattern' => 'settings.payments-gateway.webhook-deliveries.*'],
        ['route' => 'settings.payments-gateway.production-readiness', 'label' => __('Production Readiness'), 'pattern' => 'settings.payments-gateway.production-readiness'],
        ['route' => 'settings.payments-gateway.go-live-dry-run', 'label' => __('Go-Live Dry Run'), 'pattern' => 'settings.payments-gateway.go-live-dry-run'],
        ['route' => 'settings.payments-gateway.health', 'label' => __('Gateway Health'), 'pattern' => 'settings.payments-gateway.health'],
    ];
@endphp

<div class="space-y-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Settings') }} · {{ __('API & Integrations') }}</p>
        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Payments Gateway') }}</h2>
        <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('Control plane for payments.pradytecai.com — monitor tenants, profiles, PayBill accounts, and gateway health.') }}</p>
    </div>

    <div class="flex flex-wrap gap-1 rounded-xl border border-slate-200/80 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-900/80">
        @foreach ($items as $item)
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'rounded-lg px-4 py-2 text-xs font-semibold transition',
                    'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' => request()->routeIs($item['pattern']),
                    'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200' => ! request()->routeIs($item['pattern']),
                ])
            >{{ $item['label'] }}</a>
        @endforeach
    </div>
</div>
