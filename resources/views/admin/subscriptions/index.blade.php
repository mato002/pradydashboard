@php
    $mrrMax = max(collect($mrrSeries)->max('value') ?? 0, 1);
    $growthMax = max(collect($growthSeries)->max(fn ($b) => $b['new'] + $b['churned']) ?? 0, 1);
@endphp

<x-dashboard-layout :heading="__('Subscriptions')" :subheading="__('SaaS billing & plan operations')">
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

                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Revenue') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Subscription & Billing Center') }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            {{ __('MRR, plans, renewals, grace periods, and tenant billing — enterprise SaaS financial operations.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:brightness-110">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            {{ __('Add Subscription') }}
                        </a>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            {{ __('Upgrade Plan') }}
                        </button>
                        <form method="POST" action="{{ route('subscriptions.renew') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Renew Plan') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('subscriptions.invoice') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Generate Invoice') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <x-ui.kpi-card :title="__('MRR')" :value="$kpis['mrr']" :animate="false" :trend="$kpis['mrrGrowth'] ?? null" :sublabel="__('ARR').': '.$kpis['arr']" :points="$spark('sub-mrr')" tone="violet">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.25-1.638M18 9.75l.75-.75a12 12 0 0 0-12 12h12V9.75" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Active')" :value="$kpis['active']" :trend="'+5'" :sublabel="__('Paying subscriptions')" :points="$spark('sub-active')" tone="emerald">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Trials')" :value="$kpis['trial']" :sublabel="__('Conversion pipeline')" :points="$spark('sub-trial')" tone="sky">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Expiring')" :value="$kpis['expiring']" :trend="$kpis['expiring'] > 0 ? '!' : '0'" :sublabel="__('Next 14 days')" :points="$spark('sub-exp')" tone="amber">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Suspended')" :value="$kpis['suspended']" :sublabel="__('Inc. cancelled')" :points="$spark('sub-sus')" tone="rose">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Churn Rate')" :value="$kpis['churn'].'%'" :animate="false" :sublabel="__('Cancelled share of all subscriptions')" :points="$spark('sub-churn')" tone="indigo">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                </div>

                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-8 space-y-5">
                        <x-ui.table-panel :title="__('Subscriptions')">
                            <table class="prady-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Tenant') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Plan') }}</th>
                                        <th>{{ __('Cycle') }}</th>
                                        <th>{{ __('Renewal') }}</th>
                                        <th class="text-right">{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Auto') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    @forelse ($subscriptions as $sub)
                                        <tr class="group">
                                            <td class="font-semibold text-slate-900 dark:text-white">
                                                <a href="{{ $sub->tenant ? route('tenants.show', $sub->tenant) : '#' }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                    {{ $sub->tenant?->company_name ?? '—' }}
                                                </a>
                                            </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $sub->product_name ?? $sub->tenant?->project?->name ?? '—' }}</td>
                                            <td><span class="rounded-md bg-violet-500/10 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:text-violet-300">{{ $sub->plan_name }}</span></td>
                                            <td class="text-xs capitalize text-slate-500">{{ $sub->billing_cycle }}</td>
                                            <td class="text-xs text-slate-600 dark:text-slate-300">{{ $sub->current_period_end?->format('M j, Y') ?? '—' }}</td>
                                            <td class="text-right font-mono text-sm tabular-nums font-medium text-slate-900 dark:text-white">KES {{ number_format((float) $sub->amount, 0) }}</td>
                                            <td>
                                                <x-ui.status-badge :variant="$sub->statusVariant()">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</x-ui.status-badge>
                                            </td>
                                            <td class="text-center">{{ $sub->auto_renew ? '✓' : '—' }}</td>
                                            <td class="text-right">
                                                <div class="inline-flex gap-1 opacity-70 group-hover:opacity-100">
                                                    <button type="button" title="{{ __('View Usage') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-violet-600 dark:hover:bg-slate-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" /></svg></button>
                                                    <button type="button" title="{{ __('Suspend') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-rose-600 dark:hover:bg-slate-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No subscriptions yet. Add a subscription to start tracking MRR and renewals.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <x-slot name="footer">{{ $subscriptions->links() }}</x-slot>
                        </x-ui.table-panel>

                        <div id="plans" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    @forelse ($plans as $plan)
                                        <div @class([
                                            'relative overflow-hidden rounded-2xl border p-5 shadow-card transition hover:shadow-card-hover',
                                            'border-violet-300/60 bg-gradient-to-br from-violet-50 to-fuchsia-50/50 ring-2 ring-violet-500/20 dark:border-violet-800 dark:from-violet-950/40 dark:to-fuchsia-950/20' => $plan->tier === 'professional',
                                            'border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900/60' => $plan->tier !== 'professional',
                                        ])>
                                            @if ($plan->tier === 'professional')
                                                <span class="absolute right-3 top-3 rounded-full bg-violet-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">{{ __('Popular') }}</span>
                                            @endif
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $plan->tier }}</p>
                                            <h3 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                                            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                                                {{ $plan->slug === 'custom' ? __('Custom') : $plan->formattedMonthly() }}
                                                @if ($plan->slug !== 'custom')
                                                    <span class="text-sm font-normal text-slate-500">/mo</span>
                                                @endif
                                            </p>
                                            <ul class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                                                @foreach ($plan->features ?? [] as $feature)
                                                    <li class="flex items-start gap-2">
                                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-200/80 pt-3 text-[10px] dark:border-slate-700">
                                                <div><span class="text-slate-500">{{ __('API') }}</span><p class="font-semibold tabular-nums">{{ $plan->api_quota ? number_format($plan->api_quota) : '∞' }}</p></div>
                                                <div><span class="text-slate-500">{{ __('Storage') }}</span><p class="font-semibold">{{ $plan->storage_gb ? $plan->storage_gb.' GB' : '∞' }}</p></div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="col-span-full rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('No SaaS plans configured yet.') }}</p>
                                    @endforelse
                                </div>
                            </div>

                    <div class="space-y-5 lg:col-span-4">
                        <div class="overflow-hidden rounded-2xl border border-violet-200/60 bg-gradient-to-br from-violet-50/90 via-white to-fuchsia-50/40 p-5 shadow-card dark:border-violet-900/40 dark:from-violet-950/30 dark:via-slate-900 dark:to-fuchsia-950/20">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('MRR Trend') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('6-month recurring revenue') }}</p>
                            <div class="mt-4 flex h-32 items-end gap-1.5">
                                @foreach ($mrrSeries as $point)
                                    @php $h = max(10, (int) round(($point['value'] / $mrrMax) * 110)); @endphp
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t-md bg-gradient-to-t from-violet-600 to-fuchsia-400" style="height: {{ $h }}px" title="KES {{ number_format($point['value']) }}"></div>
                                        <span class="text-[9px] text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing Alerts') }}</h2>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($alerts as $alert)
                                    <li class="px-4 py-3 text-sm">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $alert['body'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing Automation') }}</h2>
                            <dl class="mt-3 space-y-3 text-xs">
                                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Auto-renew enabled') }}</dt><dd class="font-semibold tabular-nums">{{ $automation['auto_renew_enabled'] }} ({{ $automation['auto_renew_pct'] }}%)</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Failed payment retries') }}</dt><dd class="font-semibold text-amber-600">{{ $automation['retry_queue'] }} {{ __('queued') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Grace period active') }}</dt><dd class="font-semibold">{{ $automation['grace_active'] }}</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Invoice sync') }}</dt><dd class="font-semibold text-emerald-600">{{ $automation['invoice_sync'] }} {{ __('synced') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment success') }}</dt><dd class="font-semibold tabular-nums">{{ $automation['payment_success_rate'] !== null ? $automation['payment_success_rate'].'%' : '—' }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Subscription Growth vs Churn') }}</h2>
                        <div class="mt-4 flex h-28 items-end gap-2">
                            @foreach ($growthSeries as $bucket)
                                @php
                                    $newH = max(6, (int) round(($bucket['new'] / $growthMax) * 90));
                                    $churnH = max(4, (int) round(($bucket['churned'] / $growthMax) * 90));
                                @endphp
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div class="flex w-full items-end justify-center gap-0.5">
                                        <div class="w-2 rounded-t bg-emerald-500" style="height: {{ $newH }}px"></div>
                                        <div class="w-2 rounded-t bg-rose-400" style="height: {{ $churnH }}px"></div>
                                    </div>
                                    <span class="text-[9px] text-slate-500">{{ $bucket['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2 flex gap-4 text-[10px] font-semibold uppercase">
                            <span class="text-emerald-600">{{ __('New') }}</span>
                            <span class="text-rose-500">{{ __('Churned') }}</span>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant Usage Insights') }}</h2>
                        </div>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($insights as $row)
                                <li class="flex items-center justify-between gap-4 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $row['tenant'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $row['plan'] }} · {{ $row['metric'] }}</p>
                                    </div>
                                    <div class="text-right">
                                                <p class="font-mono text-sm font-semibold tabular-nums text-slate-800 dark:text-slate-100">{{ $row['value'] }}</p>
                                                <p class="text-[10px] font-medium text-emerald-600">{{ $row['trend'] }}</p>
                                            </div>
                                        </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
</x-dashboard-layout>

