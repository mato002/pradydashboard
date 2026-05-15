@php
    $trendMax = max(collect($invoiceTrend)->max(fn ($p) => max($p['issued'], $p['paid'])) ?? 0, 1);
    $revenueMax = max(collect($revenueSeries)->max('value') ?? 0, 1);
    $alertRing = fn (string $t): string => match ($t) {
        'critical', 'danger' => 'ring-rose-500/30 bg-rose-500/10',
        'warning' => 'ring-amber-500/30 bg-amber-500/10',
        'success' => 'ring-emerald-500/30 bg-emerald-500/10',
        default => 'ring-sky-500/30 bg-sky-500/10',
    };
@endphp

<x-dashboard-layout :heading="__('Invoices')" :subheading="__('Financial operations & billing')">
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
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">{{ __('Financial Operations') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Invoice & Billing Center') }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Enterprise invoice generation, recurring billing, PDF delivery, tax automation, collections, and tenant receivables — unified finance console.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('invoices.generate') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition hover:brightness-110">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" /></svg>
                                {{ __('Generate Invoice') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('invoices.reminders') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                {{ __('Send Reminders') }}
                            </button>
                        </form>
                        <a href="#recurring" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                            {{ __('Recurring Billing') }}
                        </a>
                    </div>
                </div>

                @if ($kpis['overdue'] > 0 || $kpis['failedCollections'] > 0)
                    <div class="flex items-center gap-3 rounded-2xl border border-amber-500/25 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-rose-500/10 px-5 py-3 ring-1 ring-amber-500/20">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-60"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                        </span>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ __(':overdue overdue invoice(s) · :failed failed collection(s) · :rate% collection efficiency.', [
                                'overdue' => $kpis['overdue'],
                                'failed' => $kpis['failedCollections'],
                                'rate' => $kpis['collectionRate'],
                            ]) }}
                        </p>
                    </div>
                @endif

                {{-- KPIs --}}
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <x-ui.kpi-card :title="__('Total Invoiced')" :value="$kpis['totalInvoiced']" :animate="false" :trend="'+11.8%'" :sublabel="__('Gross billings YTD window')" :points="$spark('inv-total')" tone="indigo">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0V3.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .621-.504 1.125-1.125 1.125H3.75m19.5-1.5H21M3.75 20.25h17.25" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Paid Invoices')" :value="$kpis['paid']" :trend="'+6'" :sublabel="__('Settled receivables')" :points="$spark('inv-paid')" tone="emerald">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Overdue Invoices')" :value="$kpis['overdue']" :trend="$kpis['overdue'] > 0 ? '!' : '0'" :sublabel="__('Past due date')" :points="$spark('inv-overdue')" tone="rose">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Outstanding Balance')" :value="$kpis['outstanding']" :animate="false" :trend="$kpis['collectionRate'].'%'" :sublabel="__('Open AR balance')" :points="$spark('inv-out')" tone="amber">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('This Month Revenue')" :value="$kpis['monthRevenue']" :animate="false" :trend="'+9.2%'" :sublabel="__('Cash collected MTD')" :points="$spark('inv-rev')" tone="violet">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 13.5 13.5 2.25 9m13.5-3L21.75 12 15.75 15.75M21.75 12H9" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Failed Collections')" :value="$kpis['failedCollections']" :trend="$kpis['failedCollections'] > 0 ? '!' : '0'" :sublabel="__('Retry queue')" :points="$spark('inv-fail')" tone="sky">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-3.75 9h7.5M12 3v.75" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                </div>

                {{-- Table + Alerts --}}
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <x-ui.table-panel :title="__('Invoice Register')">
                            <table class="prady-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice Number') }}</th>
                                        <th>{{ __('Tenant') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-right">{{ __('Amount') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Generated By') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    @forelse ($invoices as $invoice)
                                        <tr class="group">
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-200/80 dark:bg-amber-950/40 dark:text-amber-400 dark:ring-amber-900/50">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" /></svg>
                                                    </span>
                                                    <div>
                                                        <p class="font-mono text-xs font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                                                        @if ($invoice->is_recurring)
                                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">{{ __('Recurring') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $invoice->tenant?->company_name ?? '—' }}</td>
                                            <td class="max-w-[140px] truncate text-xs text-slate-600 dark:text-slate-300" title="{{ $invoice->product_name }}">{{ $invoice->product_name ?? '—' }}</td>
                                            <td class="text-right">
                                                <p class="font-mono text-xs font-semibold tabular-nums text-slate-900 dark:text-white">{{ $invoice->formattedAmount() }}</p>
                                                @if ($invoice->status === 'partial')
                                                    <p class="text-[10px] text-amber-600 dark:text-amber-400">{{ __('Bal') }} {{ $invoice->formattedBalance() }}</p>
                                                @endif
                                            </td>
                                            <td class="text-xs text-slate-600 dark:text-slate-300">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                                            <td>
                                                <x-ui.status-badge :variant="$invoice->statusVariant()">
                                                    {{ $invoice->statusLabel() }}
                                                </x-ui.status-badge>
                                            </td>
                                            <td class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->payment_method ?? '—' }}</td>
                                            <td class="max-w-[120px] truncate text-xs text-slate-500" title="{{ $invoice->generated_by }}">{{ $invoice->generated_by ?? '—' }}</td>
                                            <td class="text-right">
                                                <div class="inline-flex gap-1 opacity-70 transition group-hover:opacity-100">
                                                    <button type="button" title="{{ __('Download PDF') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:hover:bg-slate-800 dark:hover:text-amber-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" /></svg>
                                                    </button>
                                                    <button type="button" title="{{ __('Email Invoice') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-sky-600 dark:hover:bg-slate-800 dark:hover:text-sky-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                                    </button>
                                                    <button type="button" title="{{ __('Record Payment') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-slate-800 dark:hover:text-emerald-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V8.25" /></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-12 text-center text-sm text-slate-500">{{ __('No invoices recorded yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <x-slot name="footer">{{ $invoices->links() }}</x-slot>
                        </x-ui.table-panel>
                    </div>

                    <div class="space-y-5 lg:col-span-4">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing Alerts') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Collections & overdue signals') }}</p>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($alerts as $alert)
                                    <li class="flex gap-3 px-4 py-3.5">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $alertRing($alert['type']) }}">
                                            @if (in_array($alert['type'], ['danger', 'critical'], true))
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

                        {{-- Collection efficiency --}}
                        <div class="overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 shadow-card dark:border-amber-900/40 dark:from-amber-950/40 dark:via-slate-900 dark:to-orange-950/30">
                            <div class="border-b border-amber-200/50 px-4 py-3 dark:border-amber-900/50">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Collection Efficiency') }}</h2>
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                        <p class="text-slate-500 dark:text-slate-400">{{ __('Overdue exposure') }}</p>
                                        <p class="mt-1 font-semibold text-rose-700 dark:text-rose-300">{{ $overdueAnalytics['exposure'] }}</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                        <p class="text-slate-500 dark:text-slate-400">{{ __('Avg days late') }}</p>
                                        <p class="mt-1 font-mono font-semibold text-slate-900 dark:text-white">{{ $overdueAnalytics['avgDaysLate'] }}d</p>
                                    </div>
                                    <div class="rounded-xl border border-amber-200/60 bg-amber-50/50 px-3 py-2.5 dark:border-amber-900/40 dark:bg-amber-950/30">
                                        <p class="text-amber-700 dark:text-amber-300">{{ __('Penalties accrued') }}</p>
                                        <p class="mt-1 font-semibold text-amber-900 dark:text-amber-100">{{ $overdueAnalytics['penalties'] }}</p>
                                    </div>
                                    <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/50 px-3 py-2.5 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                                        <p class="text-emerald-700 dark:text-emerald-300">{{ __('Efficiency') }}</p>
                                        <p class="mt-1 font-mono text-sm font-bold text-emerald-900 dark:text-emerald-100">{{ $kpis['collectionRate'] }}%</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 flex justify-between text-xs">
                                        <span class="text-slate-500 dark:text-slate-400">{{ __('Collection rate') }}</span>
                                        <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $kpis['collectionRate'] }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-emerald-500 transition-all" style="width: {{ min(100, $kpis['collectionRate']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Analytics --}}
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Invoice Trends') }}</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Issued vs paid — 6 month view') }}</p>
                                </div>
                                <div class="flex gap-3 text-[10px] font-semibold uppercase tracking-wide">
                                    <span class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400"><span class="h-2 w-2 rounded-sm bg-indigo-500"></span>{{ __('Issued') }}</span>
                                    <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><span class="h-2 w-2 rounded-sm bg-emerald-500"></span>{{ __('Paid') }}</span>
                                </div>
                            </div>
                            <div class="flex h-40 items-end gap-2" aria-hidden="true">
                                @foreach ($invoiceTrend as $point)
                                    @php
                                        $hIssued = max(8, (int) round(($point['issued'] / $trendMax) * 130));
                                        $hPaid = max(6, (int) round(($point['paid'] / $trendMax) * 130));
                                    @endphp
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="flex w-full items-end justify-center gap-0.5">
                                            <div class="w-[42%] rounded-t bg-gradient-to-t from-indigo-600/80 to-indigo-400/90" style="height: {{ $hIssued }}px" title="{{ $point['issued'] }} {{ __('issued') }}"></div>
                                            <div class="w-[42%] rounded-t bg-gradient-to-t from-emerald-600/80 to-emerald-400/90" style="height: {{ $hPaid }}px" title="{{ $point['paid'] }} {{ __('paid') }}"></div>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-5">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Revenue Collected') }}</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Cash receipts trend') }}</p>
                                </div>
                                <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-semibold text-violet-700 ring-1 ring-violet-500/20 dark:text-violet-300">+9.2%</span>
                            </div>
                            <div class="flex h-40 items-end gap-2" aria-hidden="true">
                                @foreach ($revenueSeries as $point)
                                    @php $h = max(12, (int) round(($point['value'] / $revenueMax) * 140)); @endphp
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div
                                            class="w-full rounded-t-md bg-gradient-to-t from-violet-600/70 to-fuchsia-400/90 transition-all hover:from-violet-500 hover:to-fuchsia-300"
                                            style="height: {{ $h }}px"
                                            title="{{ \App\Models\TenantInvoice::formatMoney($point['value']) }}"
                                        ></div>
                                        <span class="text-[10px] font-medium text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aging + Automation --}}
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Payment Aging') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Outstanding receivables by bucket') }}</p>
                            <ul class="mt-4 space-y-3">
                                @foreach ($agingBuckets as $bucket)
                                    <li>
                                        <div class="mb-1 flex justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $bucket['label'] }} <span class="text-slate-400">({{ $bucket['count'] }})</span></span>
                                            <span class="tabular-nums text-slate-500">{{ \App\Models\TenantInvoice::formatMoney($bucket['amount']) }} · {{ $bucket['pct'] }}%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-rose-500" style="width: {{ max(2, $bucket['pct']) }}%"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="lg:col-span-7">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing Automation') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('PDF, email, reminders, and tax pipeline') }}</p>
                            </div>
                            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-950/40">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 ring-1 ring-violet-500/20 dark:text-violet-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                                        </span>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Recurring invoices') }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $automation['recurring_active'] }}<span class="text-sm font-normal text-slate-400">/{{ $automation['recurring_total'] }}</span></p>
                                    <p class="text-[10px] text-slate-500">{{ __('Active schedules') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-950/40">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 ring-1 ring-amber-500/20 dark:text-amber-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" /></svg>
                                        </span>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('PDF generation') }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $automation['pdf_rate'] }}%</p>
                                    <p class="text-[10px] text-slate-500">{{ __('Invoices with PDF') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-950/40">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20 dark:text-sky-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                        </span>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Email delivery') }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $automation['email_rate'] }}%</p>
                                    <p class="text-[10px] text-slate-500">{{ __('Delivered to tenant') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-950/40">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 ring-1 ring-rose-500/20 dark:text-rose-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                        </span>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Payment reminders') }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold tabular-nums text-slate-900 dark:text-white">{{ $automation['reminder_queue'] }}</p>
                                    <p class="text-[10px] text-slate-500">{{ __('Queued this cycle') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-950/40 sm:col-span-2 lg:col-span-2">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H12v-.008Zm0 2.25h.008v.008H12V12Zm0 2.25h.008v.008H12v-.008Zm0 2.25h.008v.008H12V18Zm2.498-6.75h7.5" /></svg>
                                        </span>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Tax automation (VAT 16%)') }}</p>
                                    </div>
                                    <p class="mt-2 text-lg font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">{{ $automation['tax_automation'] }}%</p>
                                    <p class="text-[10px] text-slate-500">{{ __('KRA-compliant line items on all generated invoices') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recurring schedules --}}
                <div id="recurring" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Recurring Invoice Schedules') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Billing cadence, tax rates, and next run') }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="prady-table min-w-[800px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Schedule') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Tax') }}</th>
                                    <th>{{ __('Next run') }}</th>
                                    <th>{{ __('Automation') }}</th>
                                    <th class="text-center">{{ __('Enabled') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($schedules as $schedule)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">{{ $schedule->name }}</td>
                                        <td class="text-sm text-slate-600 dark:text-slate-300">{{ $schedule->tenant?->company_name ?? '—' }}</td>
                                        <td class="text-xs text-slate-500">{{ $schedule->product_name ?? '—' }}</td>
                                        <td class="text-right font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">{{ \App\Models\TenantInvoice::formatMoney($schedule->totalWithTax()) }}</td>
                                        <td><span class="rounded-md bg-violet-500/10 px-2 py-0.5 text-[11px] font-semibold text-violet-700 dark:text-violet-300">{{ $schedule->frequencyLabel() }}</span></td>
                                        <td class="text-xs tabular-nums text-slate-500">{{ number_format((float) $schedule->tax_rate, 0) }}%</td>
                                        <td class="text-xs text-slate-600 dark:text-slate-300">{{ $schedule->next_run_at?->format('M j, H:i') ?? '—' }}</td>
                                        <td class="text-xs text-slate-500">
                                            @if ($schedule->auto_pdf) PDF @endif
                                            @if ($schedule->auto_email) · Email @endif
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('invoices.schedules.toggle', $schedule) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    role="switch"
                                                    aria-checked="{{ $schedule->enabled ? 'true' : 'false' }}"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 {{ $schedule->enabled ? 'bg-amber-600' : 'bg-slate-300 dark:bg-slate-600' }}"
                                                >
                                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition {{ $schedule->enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-dashboard-layout>
