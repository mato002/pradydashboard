@php
    $growthMax = max(collect($growthSeries)->max('value') ?? 1, 1);
    $lifecycleStatuses = [
        'prospect' => __('Prospect'),
        'onboarding' => __('Onboarding'),
        'trial' => __('Trial'),
        'active' => __('Active'),
        'warning' => __('Warning'),
        'restricted' => __('Restricted'),
        'overdue' => __('Overdue'),
        'suspended' => __('Suspended'),
        'cancelled' => __('Cancelled'),
        'terminated' => __('Terminated'),
    ];
    $quickStatuses = [
        ['value' => 'active', 'label' => __('Mark as active')],
        ['value' => 'trial', 'label' => __('Mark as trial')],
        ['value' => 'warning', 'label' => __('Mark as warning')],
        ['value' => 'overdue', 'label' => __('Mark as overdue')],
        ['value' => 'suspended', 'label' => __('Mark as suspended')],
        ['value' => 'restricted', 'label' => __('Mark as restricted')],
        ['value' => 'cancelled', 'label' => __('Mark as cancelled')],
    ];
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'trial' => 'warning',
        'overdue', 'suspended', 'terminated' => 'danger',
        'warning', 'restricted' => 'warning',
        default => 'info',
    };
@endphp

<x-dashboard-layout :heading="__('Tenants')" :subheading="__('Multi-tenant SaaS operations control center')">
    <div
        x-data="tenantControlCenter(@js($directory), @js($tenantDetails), @js($lifecycleStatuses), @js($quickStatuses), @js(session('tenant_drawer')))"
        class="space-y-6"
    >
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Tenant management') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Multi-Tenant SaaS Control Center') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('Client companies, onboarding, product allocation, health, usage, hosting, billing, and deployments.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    @if (! ($healthOverview['empty'] ?? false) && $healthOverview['avg_health'] !== null)
                        {{ __('Fleet health') }} {{ $healthOverview['avg_health'] }}%
                    @else
                        {{ __('No tenants yet') }}
                    @endif
                </span>
                <a href="{{ route('tenants.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add tenant') }}
                </a>
                <a href="{{ route('tenants.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                    {{ __('Provision tenant') }}
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Total tenants')" :value="$kpis['total']['value']" :trend="$kpis['total']['trend']" :sublabel="$kpis['total']['sublabel']" :points="$kpis['total']['points']" :tone="$kpis['total']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 12.75a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Active tenants')" :value="$kpis['active']['value']" :trend="$kpis['active']['trend']" :sublabel="$kpis['active']['sublabel']" :points="$kpis['active']['points']" :tone="$kpis['active']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Trial tenants')" :value="$kpis['trial']['value']" :trend="$kpis['trial']['trend']" :sublabel="$kpis['trial']['sublabel']" :points="$kpis['trial']['points']" :tone="$kpis['trial']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Overdue tenants')" :value="$kpis['overdue']['value']" :trend="$kpis['overdue']['trend']" :sublabel="$kpis['overdue']['sublabel']" :points="$kpis['overdue']['points']" :tone="$kpis['overdue']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Suspended')" :value="$kpis['suspended']['value']" :trend="$kpis['suspended']['trend']" :sublabel="$kpis['suspended']['sublabel']" :points="$kpis['suspended']['points']" :tone="$kpis['suspended']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Monthly growth')" :value="$kpis['growth']['value']" :animate="false" :trend="$kpis['growth']['trend']" :sublabel="$kpis['growth']['sublabel']" :points="$kpis['growth']['points']" :tone="$kpis['growth']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.25-1.638M18 9.75l.75-.75a12 12 0 00-12 12h12V9.75" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Onboarding + Health row --}}
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Onboarding pipeline') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Signup → verification → provisioning → deployment → billing → go-live') }}</p>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            @foreach ($onboarding as $i => $stage)
                                <div class="flex flex-col items-center" style="min-width: 14%">
                                    <div @class([
                                        'flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold ring-2',
                                        'bg-emerald-500 text-white ring-emerald-500/30' => $stage['status'] === 'complete',
                                        'bg-indigo-500 text-white ring-indigo-500/30 animate-pulse' => $stage['status'] === 'active',
                                        'bg-slate-200 text-slate-500 ring-slate-300 dark:bg-slate-700 dark:text-slate-400' => $stage['status'] === 'pending',
                                    ])>{{ $stage['count'] }}</div>
                                    <p class="mt-2 text-center text-[10px] font-semibold text-slate-700 dark:text-slate-300">{{ $stage['label'] }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $stage['pct'] }}%</p>
                                </div>
                                @if (! $loop->last)
                                    <div class="hidden h-0.5 flex-1 bg-gradient-to-r from-indigo-300 to-violet-300 sm:block dark:from-indigo-600 dark:to-violet-600"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-5">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Fleet health') }}</h3>
                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ $healthOverview['avg_health'] }}%</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-4">
                        @foreach ($healthOverview['metrics'] as $metric)
                            <div class="rounded-xl border border-slate-200/80 p-2.5 dark:border-slate-700">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <p @class([
                                    'mt-0.5 text-sm font-bold',
                                    'text-emerald-600' => $metric['status'] === 'good',
                                    'text-amber-600' => $metric['status'] === 'warn',
                                ])>{{ $metric['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Growth chart + Directory --}}
        <div class="grid gap-5 xl:grid-cols-12">
            <div class="xl:col-span-12">
                <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tenant growth (6 months)') }}</p>
                    <div class="flex h-20 items-end gap-2">
                        @foreach ($growthSeries as $bar)
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <div class="w-full rounded-t bg-gradient-to-t from-violet-600 to-indigo-500" style="height: {{ max(12, ($bar['value'] / $growthMax) * 100) }}%"></div>
                                <span class="text-[10px] text-slate-500">{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant directory') }}</h3>
                            <p class="text-xs text-slate-500" x-text="filteredDirectory.length + ' {{ __('tenants on this page') }}'"></p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium dark:border-slate-700 dark:bg-slate-800">
                                <option value="">{{ __('All statuses') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="trial">{{ __('Trial') }}</option>
                                <option value="overdue">{{ __('Overdue') }}</option>
                                <option value="suspended">{{ __('Suspended') }}</option>
                                <option value="warning">{{ __('Warning') }}</option>
                            </select>
                            <a href="{{ route('tenants.create') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('New') }} →</a>
                        </div>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table w-full min-w-[1100px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Server') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Renewal') }}</th>
                                    <th class="text-right">{{ __('Users') }}</th>
                                    <th>{{ __('Storage') }}</th>
                                    <th>{{ __('Last activity') }}</th>
                                    <th>{{ __('Health') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <template x-for="tenant in filteredDirectory" :key="tenant.id">
                                    <tr
                                        @click="openDrawer(tenant)"
                                        class="cursor-pointer transition hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                                        :class="selectedTenant?.id === tenant.id && drawerOpen ? 'bg-indigo-50/80 dark:bg-indigo-500/10' : ''"
                                    >
                                        <td>
                                            <p class="font-semibold text-slate-900 dark:text-white" x-text="tenant.company"></p>
                                            <p class="text-[10px] text-slate-400" x-text="tenant.domain"></p>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400" x-text="tenant.product"></td>
                                        <td><span class="rounded-md bg-violet-500/10 px-2 py-0.5 text-[11px] font-medium text-violet-700 dark:text-violet-300" x-text="tenant.plan"></span></td>
                                        <td class="font-mono text-xs text-slate-500" x-text="tenant.server"></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ring-1 ring-inset"
                                                :class="{
                                                    'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': tenant.status === 'active',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20': tenant.status === 'trial' || tenant.status === 'warning',
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20': tenant.status === 'overdue' || tenant.status === 'suspended',
                                                    'bg-slate-500/10 text-slate-600 ring-slate-500/15': !['active','trial','warning','overdue','suspended'].includes(tenant.status),
                                                }"
                                                x-text="tenant.status"
                                            ></span>
                                        </td>
                                        <td class="text-xs text-slate-500" x-text="tenant.renewal"></td>
                                        <td class="text-right tabular-nums font-medium" x-text="tenant.users"></td>
                                        <td>
                                            <div class="flex min-w-[72px] flex-col gap-1">
                                                <span class="text-xs text-slate-600 dark:text-slate-400" x-text="tenant.storage"></span>
                                                <div class="h-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                    <div class="h-full rounded-full bg-indigo-500" :style="'width:' + tenant.storage_pct + '%'"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-xs text-slate-500" x-text="tenant.last_activity"></td>
                                        <td>
                                            <div class="flex items-center gap-1.5">
                                                <div class="h-2 w-2 rounded-full" :class="tenant.health_score >= 80 ? 'bg-emerald-500' : tenant.health_score >= 50 ? 'bg-amber-500' : 'bg-rose-500'"></div>
                                                <span class="text-xs font-semibold tabular-nums" x-text="tenant.health_score + '%'"></span>
                                            </div>
                                        </td>
                                        <td class="text-right" @click.stop>
                                            @include('admin.tenants.partials.directory-row-actions')
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        {{ $tenants->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Change status modal --}}
        <div
            x-show="statusModalOpen"
            x-cloak
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @keydown.escape.window="closeStatusModal()"
            @click.self="closeStatusModal()"
        >
            <div
                x-show="statusModalOpen"
                x-transition
                class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                @click.stop
            >
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Change tenant status') }}</h3>
                <p class="mt-1 text-sm text-slate-500" x-show="statusModalTenant">
                    <span x-text="statusModalTenant?.company"></span>
                    <span class="text-slate-400">·</span>
                    <span class="font-medium capitalize" x-text="statusModalTenant?.status"></span>
                </p>
                <form
                    x-show="statusModalTenant"
                    :action="statusModalTenant?.status_url"
                    method="post"
                    class="mt-4 space-y-4"
                >
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                    <div>
                        <label for="tenant-status-select" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Lifecycle status') }}</label>
                        <select
                            id="tenant-status-select"
                            name="status"
                            x-model="statusModalValue"
                            class="mt-1.5 block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm font-medium dark:border-slate-700 dark:bg-slate-800"
                            required
                        >
                            <template x-for="[value, label] in Object.entries(lifecycleStatuses)" :key="value">
                                <option :value="value" x-text="label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeStatusModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold dark:border-slate-700">{{ __('Cancel') }}</button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:brightness-110">{{ __('Save status') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Detail drawer --}}
        <div
            x-show="drawerOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex justify-end bg-slate-950/50 backdrop-blur-sm"
            x-cloak
            @click.self="closeDrawer()"
            @keydown.escape.window="closeDrawer()"
        >
            <div
                x-show="drawerOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="flex h-full w-full max-w-lg flex-col border-l border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <template x-if="selectedTenant && detail">
                    <div class="flex flex-1 flex-col overflow-hidden">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400" x-text="detail.profile?.plan"></p>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" x-text="selectedTenant.company"></h3>
                                <p class="text-xs text-slate-500" x-text="detail.profile?.domain"></p>
                            </div>
                            <button type="button" @click="closeDrawer()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto prady-scrollbar p-5 space-y-5">
                            {{-- Health --}}
                            <div class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-700">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tenant health') }}</h4>
                                    <span class="text-lg font-bold text-emerald-600" x-text="detail.health?.score + '%'"></span>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                    <div><span class="text-slate-500">{{ __('Uptime') }}</span><p class="font-semibold" x-text="detail.health?.uptime"></p></div>
                                    <div><span class="text-slate-500">{{ __('Sessions') }}</span><p class="font-semibold" x-text="detail.health?.sessions"></p></div>
                                    <div><span class="text-slate-500">{{ __('API calls') }}</span><p class="font-semibold" x-text="detail.health?.api_calls"></p></div>
                                    <div><span class="text-slate-500">{{ __('Storage') }}</span><p class="font-semibold" x-text="detail.health?.storage"></p></div>
                                </div>
                            </div>

                            {{-- Profile --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Company profile') }}</h4>
                                <dl class="space-y-1.5 text-sm">
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Contact') }}</dt><dd class="font-medium" x-text="detail.profile?.contact"></dd></div>
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Email') }}</dt><dd class="font-medium" x-text="detail.profile?.email"></dd></div>
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('MRR') }}</dt><dd class="font-semibold text-indigo-600" x-text="detail.profile?.mrr"></dd></div>
                                </dl>
                            </div>

                            {{-- Infrastructure --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Infrastructure') }}</h4>
                                <dl class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800/80 space-y-1.5">
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Server') }}</dt><dd class="font-mono text-xs" x-text="detail.infrastructure?.server"></dd></div>
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Product') }}</dt><dd x-text="detail.infrastructure?.product"></dd></div>
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Deployment') }}</dt><dd class="font-mono" x-text="detail.infrastructure?.deployment"></dd></div>
                                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Backup') }}</dt><dd x-text="detail.infrastructure?.backup"></dd></div>
                                </dl>
                            </div>

                            {{-- Modules --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Active modules') }}</h4>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="mod in detail.modules ?? []" :key="mod">
                                        <span class="rounded-md bg-indigo-500/10 px-2 py-0.5 text-[11px] font-medium text-indigo-700 dark:text-indigo-300" x-text="mod"></span>
                                    </template>
                                </div>
                            </div>

                            {{-- Invoices --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Invoices') }}</h4>
                                <ul class="space-y-1.5">
                                    <template x-for="inv in detail.invoices ?? []" :key="inv.id">
                                        <li class="flex justify-between rounded-lg border border-slate-200/80 px-3 py-2 text-xs dark:border-slate-700">
                                            <span class="font-mono" x-text="inv.id"></span>
                                            <span x-text="inv.amount"></span>
                                            <span class="font-semibold uppercase" :class="inv.status === 'overdue' ? 'text-rose-600' : 'text-emerald-600'" x-text="inv.status"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Deployments --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Deployment history') }}</h4>
                                <ul class="space-y-1">
                                    <template x-for="dep in detail.deployments ?? []" :key="dep.version">
                                        <li class="flex justify-between text-xs">
                                            <span class="font-mono font-semibold" x-text="dep.version"></span>
                                            <span class="text-slate-400" x-text="dep.date"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Controls --}}
                            <div>
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Multi-tenant controls') }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-if="selectedTenant?.can_suspend">
                                        <form method="post" :action="selectedTenant.suspend_url" class="contents" @submit="if (!confirm(@js(__('Suspend this tenant? Their apps will be blocked at the next license check.')))) { $event.preventDefault(); }">
                                            @csrf
                                            <button type="submit" class="w-full rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-800 dark:border-amber-500/30 dark:bg-amber-950/30 dark:text-amber-200">{{ __('Suspend') }}</button>
                                        </form>
                                    </template>
                                    <a
                                        :href="selectedTenant.impersonate_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-center text-[11px] font-semibold dark:border-slate-700"
                                    >{{ __('Impersonate') }}</a>
                                    <template x-if="selectedTenant?.can_backup">
                                        <form method="post" :action="selectedTenant.backup_url" class="contents" @submit="if (!confirm(@js(__('Queue an on-demand backup for this tenant?')))) { $event.preventDefault(); }">
                                            @csrf
                                            <button type="submit" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-semibold dark:border-slate-700">{{ __('Force backup') }}</button>
                                        </form>
                                    </template>
                                    <a
                                        x-show="selectedTenant?.can_update"
                                        :href="selectedTenant.infrastructure_url"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-center text-[11px] font-semibold dark:border-slate-700"
                                    >{{ __('Migrate server') }}</a>
                                    <template x-if="selectedTenant?.can_update">
                                        <form method="post" :action="selectedTenant.reset_license_url" class="contents" @submit="if (!confirm(@js(__('Reset license to active for this tenant?')))) { $event.preventDefault(); }">
                                            @csrf
                                            <button type="submit" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-semibold dark:border-slate-700">{{ __('Reset license') }}</button>
                                        </form>
                                    </template>
                                    <template x-if="selectedTenant?.can_update">
                                        <form method="post" :action="selectedTenant.restart_services_url" class="contents" @submit="if (!confirm(@js(__('Send a service restart signal via the tenant integration?')))) { $event.preventDefault(); }">
                                            @csrf
                                            <button type="submit" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-semibold dark:border-slate-700">{{ __('Restart services') }}</button>
                                        </form>
                                    </template>
                                </div>
                                <p x-show="!selectedTenant?.can_update && !selectedTenant?.can_suspend" class="mt-2 text-[11px] text-slate-400">{{ __('You do not have permission to run operational actions on this tenant.') }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2 border-t border-slate-200/80 p-4 dark:border-slate-800">
                            <a :href="selectedTenant.show_url" class="flex-1 rounded-xl bg-indigo-600 py-2.5 text-center text-xs font-semibold text-white hover:brightness-110">{{ __('Full command center') }}</a>
                            <a x-show="selectedTenant.edit_url" :href="selectedTenant.edit_url" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold dark:border-slate-700">{{ __('Edit') }}</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-dashboard-layout>
