@php
    $collectionMax = max(collect($collectionSeries)->max('value') ?? 0, 1);
    $gatewayMax = max($gatewayAnalytics->max('volume') ?? 1, 1);
    $heatmapMax = 1;
    foreach ($heatmap as $row) {
        $heatmapMax = max($heatmapMax, max($row['hours']));
    }
    $statusVariant = fn (?string $s): string => match ($s) {
        'successful' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        'refunded' => 'purple',
        'reversed' => 'neutral',
        default => 'neutral',
    };
    $alertRing = fn (string $t): string => match ($t) {
        'critical', 'danger' => 'ring-rose-500/30 bg-rose-500/10',
        'warning' => 'ring-amber-500/30 bg-amber-500/10',
        'success' => 'ring-emerald-500/30 bg-emerald-500/10',
        default => 'ring-sky-500/30 bg-sky-500/10',
    };
    $gwTone = fn (string $c): string => match ($c) {
        'emerald' => 'from-emerald-500 to-teal-600',
        'indigo' => 'from-indigo-500 to-violet-600',
        'sky' => 'from-sky-500 to-blue-600',
        'violet' => 'from-violet-500 to-fuchsia-600',
        'amber' => 'from-amber-500 to-orange-600',
        default => 'from-slate-500 to-slate-600',
    };
@endphp

<x-dashboard-layout :heading="__('Payments')" :subheading="__('Treasury & collection operations')">
    <div
        x-data="{ toast: @js(session('status')) }"
        x-init="if (toast) { setTimeout(() => toast = null, 4000) }"
        class="space-y-6"
    >
        <div
            x-show="toast"
            x-transition
            class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-emerald-500/30 bg-emerald-950/90 px-4 py-3 text-sm text-emerald-100 shadow-2xl backdrop-blur"
            x-cloak
        >
            <span x-text="toast"></span>
        </div>

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ __('Financials') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Payment Operations Center') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Tenant collections, gateway orchestration, reconciliations, allocations, and treasury monitoring — enterprise-grade payment rails.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('payments.reconcile') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                        {{ __('Run Reconciliation') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('payments.retry-failed') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                        {{ __('Retry Failed') }}
                    </button>
                </form>
                <a href="#gateways" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                    {{ __('Gateway Fleet') }}
                </a>
            </div>
        </div>

        @if ($kpis['failed'] > 0 || $kpis['pending'] > 0)
            <div class="flex items-center gap-3 rounded-2xl border border-amber-500/25 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-rose-500/10 px-5 py-3 ring-1 ring-amber-500/20">
                <span class="relative flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-60"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                </span>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    {{ __(':failed failed and :pending pending transaction(s) require treasury review.', ['failed' => $kpis['failed'], 'pending' => $kpis['pending']]) }}
                </p>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Payments Collected')" :value="$kpis['collected']" :animate="false" :sublabel="__('Successful settlements')" :points="$spark('pay-collected')" tone="emerald">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Failed Transactions')" :value="$kpis['failed']" :trend="$kpis['failed'] > 0 ? '-3' : '0'" :sublabel="__('Declined / timeout')" :points="$spark('pay-failed')" tone="rose">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                </x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Pending Payments')" :value="$kpis['pending']" :trend="'+2'" :sublabel="__('Awaiting confirmation')" :points="$spark('pay-pending')" tone="amber">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Refunds')" :value="$kpis['refunds']" :animate="false" :sublabel="__('Processed reversals')" :points="$spark('pay-refund')" tone="violet">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                </x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Collection Rate')" :value="$kpis['collectionRate'].'%'" :animate="false" :sublabel="__('Success vs attempts')" :points="$spark('pay-rate')" tone="sky">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                </x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Gateway Health')" :value="$kpis['gatewayHealth'].'%'" :animate="false" :sublabel="__('Fleet uptime avg')" :points="$spark('pay-gw')" tone="indigo">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.374 3.374 0 001.22 3.68 3.375 3.375 0 003.68-1.22A3.375 3.375 0 0018 12.75a3.375 3.375 0 00-3.68-1.22 3.374 3.374 0 00-1.22-3.68A3.375 3.375 0 0012 8.25c1.268 0 2.39.63 3.068 1.593a3.374 3.374 0 003.68 1.22 3.375 3.375 0 001.22 3.68A3.375 3.375 0 0015.75 21a3.375 3.375 0 003.68-1.22 3.374 3.374 0 001.22-3.68A3.375 3.375 0 0021 12.75a3.375 3.375 0 00-3.68-1.22 3.374 3.374 0 00-1.22-3.68A3.375 3.375 0 0012 8.25z" /></svg>
                </x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Transactions + Alerts --}}
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Payment Ledger') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['' => __('All'), 'successful' => __('Successful'), 'pending' => __('Pending'), 'failed' => __('Failed'), 'refunded' => __('Refunded')] as $val => $label)
                                <a
                                    href="{{ route('payments.index', array_filter(['status' => $val ?: null, 'gateway' => request('gateway')])) }}"
                                    @class([
                                        'rounded-lg px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide transition',
                                        'bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-500/25 dark:text-emerald-300' => request('status', '') === $val,
                                        'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' => request('status', '') !== $val,
                                    ])
                                >{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction ID') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Gateway') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @forelse ($payments as $payment)
                                    <tr class="group">
                                        <td>
                                            <p class="font-mono text-xs font-semibold text-slate-900 dark:text-white">{{ $payment->displayId() }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $payment->reference }}</p>
                                        </td>
                                        <td class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $payment->tenant?->company_name ?? '—' }}</td>
                                        <td class="text-right font-mono text-sm tabular-nums font-semibold text-slate-900 dark:text-white">{{ $payment->formattedAmount() }}</td>
                                        <td class="text-xs text-slate-600 dark:text-slate-300">{{ $payment->method ?? '—' }}</td>
                                        <td class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                                        <td>
                                            <x-ui.status-badge :variant="$statusVariant($payment->status)">
                                                {{ ucfirst($payment->status ?? 'unknown') }}
                                            </x-ui.status-badge>
                                        </td>
                                        <td>
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $payment->gatewayLabel() }}</span>
                                        </td>
                                        <td class="text-xs text-slate-500">{{ $payment->paid_at?->format('M j, Y H:i') ?? $payment->created_at?->format('M j, Y') }}</td>
                                        <td class="text-right">
                                            <div class="inline-flex gap-1 opacity-70 transition group-hover:opacity-100">
                                                <button type="button" title="{{ __('View Receipt') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-slate-800">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75H7.5m-1.5-3H21M3.75 19.5h15A2.25 2.25 0 0 0 21 17.25V9.75A2.25 2.25 0 0 0 18.75 7.5h-15A2.25 2.25 0 0 0 2.25 9.75v7.75A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                                                </button>
                                                <button type="button" title="{{ __('Allocate') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                                </button>
                                                <button type="button" title="{{ __('Refund') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-violet-600 dark:hover:bg-slate-800">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-12 text-center text-sm text-slate-500">{{ __('No payment transactions recorded yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200/80 px-4 py-3 text-sm dark:border-slate-800/80">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>

            <div class="space-y-5 lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Treasury Alerts') }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Failed collections & settlement exceptions') }}</p>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($alerts as $alert)
                            <li class="flex gap-3 px-4 py-3.5">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $alertRing($alert['type']) }}">
                                    @if ($alert['type'] === 'danger')
                                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                    @elseif ($alert['type'] === 'success')
                                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @else
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-3.75 9h7.5M12 3v.75" /></svg>
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $alert['body'] }}</p>
                                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $alert['time'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 shadow-card dark:border-emerald-900/40 dark:from-emerald-950/40 dark:via-slate-900 dark:to-teal-950/30">
                    <div class="border-b border-emerald-200/50 px-4 py-3 dark:border-emerald-900/50">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Reconciliation Summary') }}</h2>
                    </div>
                    <div class="space-y-4 p-4">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                <p class="text-slate-500">{{ __('Matched') }}</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-emerald-700 dark:text-emerald-300">{{ $reconciliation['matched'] }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                <p class="text-slate-500">{{ __('Pending') }}</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ $reconciliation['pending'] }}</p>
                            </div>
                            <div class="rounded-xl border border-rose-200/60 bg-rose-50/50 px-3 py-2.5 dark:border-rose-900/40 dark:bg-rose-950/30">
                                <p class="text-rose-700 dark:text-rose-300">{{ __('Exceptions') }}</p>
                                <p class="mt-1 font-mono text-sm font-bold text-rose-900 dark:text-rose-100">{{ $reconciliation['exceptions'] }}</p>
                            </div>
                            <div class="rounded-xl border border-violet-200/60 bg-violet-50/50 px-3 py-2.5 dark:border-violet-900/40 dark:bg-violet-950/30">
                                <p class="text-violet-700 dark:text-violet-300">{{ __('Unallocated') }}</p>
                                <p class="mt-1 font-mono text-sm font-bold text-violet-900 dark:text-violet-100">{{ $reconciliation['unallocated'] }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs">
                                <span class="text-slate-500">{{ __('Match rate') }}</span>
                                <span class="font-semibold tabular-nums">{{ $reconciliation['match_rate'] }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ min(100, $reconciliation['match_rate']) }}%"></div>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400">{{ __('Last run :time · :window', ['time' => $reconciliation['last_run'], 'window' => $reconciliation['settlement_window']]) }}</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Recurring Collections') }}</h2>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li class="flex justify-between"><span class="text-slate-500">{{ __('Active mandates') }}</span><span class="font-semibold tabular-nums">{{ $recurring['active_mandates'] }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">{{ __('Next auto-collect') }}</span><span class="font-medium">{{ $recurring['next_run'] }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">{{ __('Retry queue') }}</span><span class="font-semibold text-rose-600">{{ $recurring['retry_queue'] }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">{{ __('Auto-collect rate') }}</span><span class="font-semibold text-emerald-600">{{ $recurring['auto_collect_rate'] }}%</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Gateway fleet --}}
        <div id="gateways" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Gateway Management') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('M-Pesa, Stripe, PayPal, Flutterwave, and bank transfer rails') }}</p>
            </div>
            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($gateways as $gw)
                    <a
                        href="{{ route('payments.index', ['gateway' => $gw['key']]) }}"
                        class="group relative overflow-hidden rounded-xl border border-slate-200/80 p-4 transition hover:border-slate-300 hover:shadow-md dark:border-slate-700 dark:hover:border-slate-600"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br {{ $gwTone($gw['color']) }} text-xs font-bold text-white shadow">
                                {{ strtoupper(substr($gw['name'], 0, 2)) }}
                            </span>
                            <x-ui.status-badge :variant="$gw['status'] === 'operational' ? 'success' : 'warning'">
                                {{ $gw['status'] === 'operational' ? __('Live') : __('Degraded') }}
                            </x-ui.status-badge>
                        </div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $gw['name'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $gw['volume'] }} {{ __('volume') }}</p>
                        <div class="mt-3 space-y-2">
                            <div>
                                <div class="mb-0.5 flex justify-between text-[10px]">
                                    <span class="text-slate-500">{{ __('Uptime') }}</span>
                                    <span class="font-semibold tabular-nums">{{ $gw['uptime'] }}%</span>
                                </div>
                                <div class="h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $gw['uptime'] }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-0.5 flex justify-between text-[10px]">
                                    <span class="text-slate-500">{{ __('Success rate') }}</span>
                                    <span class="font-semibold tabular-nums">{{ $gw['success'] }}%</span>
                                </div>
                                <div class="h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $gw['success'] }}%"></div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400">{{ __('Latency') }}: <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">{{ $gw['latency'] }}ms</span></p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Analytics --}}
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Collection Trends') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('6-month payment volume') }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">+18.6%</span>
                    </div>
                    <div class="flex h-44 items-end gap-2" aria-hidden="true">
                        @foreach ($collectionSeries as $point)
                            @php
                                $h = max(12, (int) round(($point['value'] / $collectionMax) * 160));
                                $failH = max(4, (int) round(($point['failed'] / $collectionMax) * 160));
                            @endphp
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <div class="flex w-full flex-col items-center justify-end gap-0.5" style="height: 168px">
                                    <div class="w-full max-w-[28px] rounded-t-sm bg-gradient-to-t from-emerald-600/80 to-teal-400/90" style="height: {{ max(4, $h - $failH) }}px" title="{{ __('Collected') }}"></div>
                                    <div class="w-full max-w-[28px] rounded-t-sm bg-rose-400/70" style="height: {{ $failH }}px" title="{{ __('Failed') }}"></div>
                                </div>
                                <span class="text-[10px] font-medium text-slate-500">{{ $point['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex gap-4 text-[10px] text-slate-500">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-emerald-500"></span> {{ __('Collected') }}</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-rose-400"></span> {{ __('Failed') }}</span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-5">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Gateway Analytics') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Volume by payment rail') }}</p>
                    <ul class="mt-4 space-y-3">
                        @forelse ($gatewayAnalytics as $row)
                            <li>
                                <div class="mb-1 flex justify-between text-xs">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $row['label'] }}</span>
                                    <span class="tabular-nums text-slate-500">KES {{ number_format($row['volume'], 0) }} · {{ $row['success'] }}%</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500" style="width: {{ round(($row['volume'] / $gatewayMax) * 100) }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-xs text-slate-500">{{ __('No gateway data yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Heatmap --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Transaction Heatmap') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Volume intensity by day and hour (UTC)') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[640px]">
                    <div class="mb-1 grid grid-cols-[40px_repeat(24,minmax(0,1fr))] gap-0.5 text-[9px] text-slate-400">
                        <div></div>
                        @for ($h = 0; $h < 24; $h += 2)
                            <div class="col-span-2 text-center">{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}</div>
                        @endfor
                    </div>
                    @foreach ($heatmap as $row)
                        <div class="mb-0.5 grid grid-cols-[40px_repeat(24,minmax(0,1fr))] gap-0.5">
                            <div class="flex items-center text-[10px] font-medium text-slate-500">{{ $row['day'] }}</div>
                            @foreach ($row['hours'] as $intensity)
                                @php
                                    $pct = ($intensity / $heatmapMax) * 100;
                                    $opacity = 0.15 + ($pct / 100) * 0.85;
                                @endphp
                                <div
                                    class="aspect-square rounded-sm bg-emerald-500"
                                    style="opacity: {{ $opacity }}"
                                    title="{{ $intensity }} {{ __('events') }}"
                                ></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
