<x-dashboard-layout :heading="__('Tenants')">
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Total'), 'value' => $kpis['total'], 'tone' => 'border-slate-200/80 dark:border-slate-700'],
            ['label' => __('Active'), 'value' => $kpis['active'], 'tone' => 'border-emerald-200/80 bg-emerald-50/50 dark:border-emerald-900/40 dark:bg-emerald-950/20'],
            ['label' => __('Trial'), 'value' => $kpis['trial'], 'tone' => 'border-amber-200/80 bg-amber-50/50 dark:border-amber-900/40 dark:bg-amber-950/20'],
            ['label' => __('Overdue'), 'value' => $kpis['overdue'], 'tone' => 'border-rose-200/80 bg-rose-50/50 dark:border-rose-900/40 dark:bg-rose-950/20'],
            ['label' => __('Suspended'), 'value' => $kpis['suspended'], 'tone' => 'border-slate-200/80 dark:border-slate-700'],
        ] as $card)
            <div class="rounded-2xl border bg-white p-4 shadow-card dark:bg-slate-900/60 {{ $card['tone'] }}">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Tenant management') }}</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Client companies') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('Client companies subscribed to your hosted products.') }}</p>
        </div>
        <a href="{{ route('tenants.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Add tenant') }}</a>
    </div>

    <x-ui.table-panel :title="__('Directory')" :action-href="route('tenants.create')" :action-label="__('New')">
        <table class="prady-table">
            <thead>
                <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Renewal') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach ($tenants as $tenant)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('tenants.show', $tenant) }}" class="text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">{{ $tenant->company_name }}</a>
                        </td>
                        <td class="text-slate-600 dark:text-slate-300">{{ $tenant->project?->domain }}</td>
                        <td class="text-slate-600 dark:text-slate-300">{{ $tenant->subscription_plan ?? '—' }}</td>
                        <td class="text-slate-600 dark:text-slate-300">{{ optional($tenant->renewal_date)->toFormattedDateString() ?? '—' }}</td>
                        <td>
                            <x-ui.status-badge :variant="match ($tenant->status) {
                                'active' => 'success',
                                'trial' => 'warning',
                                'overdue' => 'danger',
                                'suspended', 'cancelled' => 'neutral',
                                default => 'info',
                            }">{{ $tenant->status }}</x-ui.status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-slot name="footer">
            {{ $tenants->links() }}
        </x-slot>
    </x-ui.table-panel>
</x-dashboard-layout>
