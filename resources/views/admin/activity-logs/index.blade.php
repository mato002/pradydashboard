@php
    $severityBadge = fn (string $s): string => match ($s) {
        'success' => 'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300',
        'warning' => 'bg-amber-500/12 text-amber-800 ring-amber-500/20 dark:text-amber-200',
        'error' => 'bg-rose-500/12 text-rose-700 ring-rose-500/20 dark:text-rose-300',
        'critical' => 'bg-violet-500/15 text-violet-800 ring-violet-500/30 shadow-[0_0_12px_rgba(139,92,246,0.35)] dark:text-violet-200',
        default => 'bg-sky-500/12 text-sky-800 ring-sky-500/20 dark:text-sky-200',
    };

    $eventIcon = fn (string $type): string => match ($type) {
        'Login' => 'M15.75 9V5.25A3.75 3.75 0 0012 2.25S8.25 2.25 8.25 5.25V9m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        'Deployment' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3',
        'Payment' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        'Backup' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'API' => 'M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5',
        'Server Event' => 'M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5',
        'Security Alert' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        default => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    };

    $statusVariant = fn (string $s): string => match ($s) {
        'failed' => 'danger',
        'pending' => 'warning',
        default => 'success',
    };

    $heatmapMax = max(1, collect($heatmap)->flatten()->max());
    $timelineMax = max(collect($timeline)->max('value') ?? 0, 1);
    $dayLabels = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('D'))->all();

    $queryExceptPage = request()->except('page');
@endphp

<x-dashboard-layout :heading="__('Audit & Observability')" :subheading="__('Enterprise activity trail, security events, and infrastructure telemetry')">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ __('Live ingestion') }}</p>
            </div>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Audit & Observability Center') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Cross-tenant SIEM view — logins, deployments, API, billing, and infrastructure events') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('activity-logs.export', $queryExceptPage) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                {{ __('Export CSV') }}
            </a>
            <button type="button" x-data @click="$dispatch('open-retention')" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                <svg class="h-4 w-4 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                {{ __('Retention') }}
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <x-ui.kpi-card :title="__('Total Events Today')" :value="$kpis['total_today']" :trend="'+'.number_format(max(0, $kpis['total_today'] - 3)).' vs avg'" :points="$spark('events')" tone="indigo">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m.5 1.5l-.5 1.5m8.5-1.5l.5 1.5" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Security Events')" :value="$kpis['security_events']" :sublabel="__('Auth &amp; access anomalies')" :points="$spark('security')" tone="rose">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Failed Logins')" :value="$kpis['failed_logins']" :sublabel="__('Last 24h window')" :points="$spark('failed_logins')" tone="amber">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('API Requests')" :value="$kpis['api_requests']" :sublabel="__('License &amp; webhook traffic')" :points="$spark('api')" tone="sky">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Tenant Activities')" :value="$kpis['tenant_activities']" :sublabel="__('Provisioning &amp; module changes')" :points="$spark('tenant')" tone="emerald">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Critical Alerts')" :value="$kpis['critical_alerts']" :sublabel="__('Requires immediate review')" :points="$spark('critical')" tone="violet">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.75a9 9 0 1118 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
        </x-ui.kpi-card>
    </div>

    {{-- Main grid: filters + table + live stream --}}
    <div class="mt-6 grid gap-5 lg:grid-cols-12" x-data="{ filtersOpen: {{ request()->hasAny(['q','event_type','severity','tenant','user','module','server','from','to']) ? 'true' : 'false' }} }">
        <div class="space-y-4 lg:col-span-8">
            {{-- Filters --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <button type="button" @click="filtersOpen = !filtersOpen" class="flex w-full items-center justify-between px-4 py-3 text-left">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Advanced filters & search') }}</span>
                        @if ($totalFiltered !== collect($events)->count() || request()->hasAny(['q','event_type','severity','tenant','user','module','server','from','to']))
                            <span class="rounded-full bg-indigo-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">{{ __('Active') }}</span>
                        @endif
                    </div>
                    <svg class="h-4 w-4 text-slate-400 transition" :class="filtersOpen && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <form method="GET" action="{{ route('activity-logs.index') }}" x-show="filtersOpen" x-cloak class="border-t border-slate-200/80 px-4 py-4 dark:border-slate-800">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Full-text search') }}</label>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search events, users, IPs, descriptions…') }}" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Event type') }}</label>
                            <select name="event_type" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All types') }}</option>
                                @foreach ($eventTypes as $type)
                                    <option value="{{ $type }}" @selected($filters['event_type'] === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Severity') }}</label>
                            <select name="severity" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All severities') }}</option>
                                @foreach ($severities as $sev)
                                    <option value="{{ $sev }}" @selected($filters['severity'] === $sev)>{{ strtoupper($sev) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Tenant') }}</label>
                            <select name="tenant" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All tenants') }}</option>
                                @foreach ($filterOptions['tenants'] as $t)
                                    <option value="{{ $t }}" @selected($filters['tenant'] === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('User') }}</label>
                            <select name="user" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All users') }}</option>
                                @foreach ($filterOptions['users'] as $u)
                                    <option value="{{ $u }}" @selected($filters['user'] === $u)>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Module') }}</label>
                            <select name="module" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All modules') }}</option>
                                @foreach ($filterOptions['modules'] as $m)
                                    <option value="{{ $m }}" @selected($filters['module'] === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Server') }}</label>
                            <select name="server" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">{{ __('All servers') }}</option>
                                @foreach ($filterOptions['servers'] as $s)
                                    <option value="{{ $s }}" @selected($filters['server'] === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('From') }}</label>
                            <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('To') }}</label>
                            <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25">{{ __('Apply filters') }}</button>
                        <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center rounded-xl border border-slate-200/80 px-4 py-2 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            {{-- Log table --}}
            <x-ui.table-panel :title="__('Audit log stream')" :action-label="__(':count events', ['count' => number_format($totalFiltered)])">
                <div class="overflow-x-auto">
                    <table class="prady-table min-w-[1100px]">
                        <thead>
                            <tr>
                                <th>{{ __('Timestamp') }}</th>
                                <th>{{ __('Event Type') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Tenant') }}</th>
                                <th>{{ __('IP') }}</th>
                                <th>{{ __('Module') }}</th>
                                <th>{{ __('Severity') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @forelse ($events as $event)
                                <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                    <td class="whitespace-nowrap font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                        <span class="block text-slate-700 dark:text-slate-300">{{ $event['timestamp']->format('M j, Y') }}</span>
                                        <span>{{ $event['timestamp']->format('H:i:s') }}</span>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                                            <svg class="h-3.5 w-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eventIcon($event['event_type']) }}" /></svg>
                                            {{ $event['event_type'] }}
                                        </span>
                                    </td>
                                    <td class="font-medium text-slate-800 dark:text-slate-100">{{ $event['user'] }}</td>
                                    <td class="max-w-[8rem] truncate text-slate-600 dark:text-slate-300">{{ $event['tenant'] ?? '—' }}</td>
                                    <td class="font-mono text-[11px] text-slate-500">{{ $event['ip'] }}</td>
                                    <td class="text-xs text-slate-600 dark:text-slate-400">{{ $event['module'] }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset {{ $severityBadge($event['severity']) }}">
                                            {{ $event['severity'] }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs truncate text-sm text-slate-600 group-hover:whitespace-normal group-hover:overflow-visible dark:text-slate-300" title="{{ $event['description'] }}">
                                        {{ $event['description'] }}
                                    </td>
                                    <td>
                                        <x-ui.status-badge :variant="$statusVariant($event['status'])">{{ $event['status'] }}</x-ui.status-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No events match your filters.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($totalPages > 1)
                    <div class="flex items-center justify-between border-t border-slate-200/80 px-4 py-3 dark:border-slate-800">
                        <p class="text-xs text-slate-500">{{ __('Page :page of :total', ['page' => $page, 'total' => $totalPages]) }}</p>
                        <div class="flex gap-1">
                            @if ($page > 1)
                                <a href="{{ route('activity-logs.index', array_merge($queryExceptPage, ['page' => $page - 1])) }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">{{ __('Prev') }}</a>
                            @endif
                            @if ($page < $totalPages)
                                <a href="{{ route('activity-logs.index', array_merge($queryExceptPage, ['page' => $page + 1])) }}" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">{{ __('Next') }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </x-ui.table-panel>
        </div>

        {{-- Live stream panel --}}
        <div class="lg:col-span-4">
            <div class="sticky top-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-900 to-slate-950 shadow-card dark:border-slate-700/80" x-data="{ tick: 0 }" x-init="setInterval(() => tick++, 4000)">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                            <span class="relative h-2 w-2 rounded-full bg-cyan-400"></span>
                        </span>
                        <h3 class="text-sm font-semibold text-white">{{ __('Live event stream') }}</h3>
                    </div>
                    <span class="rounded-md bg-white/10 px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-widest text-cyan-300">{{ __('Real-time') }}</span>
                </div>
                <ul class="max-h-[32rem] divide-y divide-white/5 overflow-y-auto">
                    @foreach ($liveStream as $i => $event)
                        <li class="px-4 py-3 transition" :class="tick % {{ count($liveStream) }} === {{ $i }} && 'bg-white/5 ring-1 ring-inset ring-cyan-500/20'">
                            <div class="flex items-start gap-3">
                                <span @class([
                                    'mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg',
                                    'bg-violet-500/20 text-violet-300' => $event['severity'] === 'critical',
                                    'bg-rose-500/20 text-rose-300' => $event['severity'] === 'error',
                                    'bg-amber-500/20 text-amber-300' => $event['severity'] === 'warning',
                                    'bg-emerald-500/20 text-emerald-300' => $event['severity'] === 'success',
                                    'bg-sky-500/20 text-sky-300' => $event['severity'] === 'info',
                                ])>
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eventIcon($event['event_type']) }}" /></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-white">{{ $event['event_type'] }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $event['description'] }}</p>
                                    <p class="mt-1.5 flex flex-wrap gap-2 text-[10px] text-slate-500">
                                        <span>{{ $event['user'] }}</span>
                                        <span>·</span>
                                        <span class="font-mono">{{ $event['ip'] }}</span>
                                        <span>·</span>
                                        <span>{{ $event['timestamp']->diffForHumans(short: true) }}</span>
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Analytics --}}
    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        {{-- Heatmap --}}
        <div class="lg:col-span-5">
            <div class="h-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Activity heatmap') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Event density by day & hour (7d)') }}</p>
                <div class="mt-4 overflow-x-auto">
                    <div class="inline-grid gap-0.5" style="grid-template-columns: 2.5rem repeat(24, 1fr);">
                        <div></div>
                        @for ($h = 0; $h < 24; $h += 4)
                            <div class="col-span-4 text-center text-[9px] font-semibold text-slate-400">{{ sprintf('%02d', $h) }}</div>
                        @endfor
                        @foreach ($dayLabels as $dIdx => $dayLabel)
                            <div class="flex items-center text-[10px] font-semibold text-slate-500">{{ $dayLabel }}</div>
                            @for ($h = 0; $h < 24; $h++)
                                @php $count = $heatmap[$dIdx][$h] ?? 0; $intensity = $count / $heatmapMax; @endphp
                                <div
                                    class="aspect-square min-h-[10px] min-w-[10px] rounded-sm"
                                    style="background-color: rgba(99, 102, 241, {{ max(0.06, $intensity * 0.9) }});"
                                    title="{{ $dayLabel }} {{ sprintf('%02d:00', $h) }} — {{ $count }} events"
                                ></div>
                            @endfor
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="lg:col-span-4">
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Event timeline') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('7-day volume trend') }}</p>
                <div class="mt-4 flex-1">
                    <svg class="h-36 w-full" viewBox="0 0 320 120" preserveAspectRatio="none">
                        @php
                            $w = 320; $h = 120; $pad = 8; $pts = []; $n = count($timeline);
                            foreach ($timeline as $i => $row) {
                                $x = $pad + ($i / max(1, $n - 1)) * ($w - $pad * 2);
                                $y = $h - $pad - (($row['value'] / $timelineMax) * ($h - $pad * 2));
                                $pts[] = round($x, 1).','.round($y, 1);
                            }
                            $line = implode(' ', $pts);
                            $poly = $pad.','.($h - $pad).' '.$line.' '.($w - $pad).','.($h - $pad);
                        @endphp
                        <defs>
                            <linearGradient id="auditGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(6,182,212)" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="rgb(6,182,212)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <polygon points="{{ $poly }}" fill="url(#auditGrad)" />
                        <polyline points="{{ $line }}" fill="none" stroke="rgb(6,182,212)" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                    <div class="mt-2 flex justify-between text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        @foreach ($timeline as $row)
                            <span>{{ $row['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Mini breakdowns --}}
        <div class="space-y-4 lg:col-span-3">
            @foreach ([
                ['title' => __('Login analytics'), 'rows' => $loginBreakdown, 'empty' => __('No login events')],
                ['title' => __('Deployment trends'), 'rows' => $deploymentBreakdown, 'empty' => __('No deployments')],
                ['title' => __('API usage'), 'rows' => $apiBreakdown, 'empty' => __('No API traffic')],
            ] as $panel)
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-3 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <h4 class="text-xs font-semibold text-slate-900 dark:text-white">{{ $panel['title'] }}</h4>
                    <ul class="mt-2 space-y-2">
                        @forelse ($panel['rows'] as $row)
                            <li>
                                <div class="flex justify-between text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                    <span class="truncate capitalize">{{ $row['label'] }}</span>
                                    <span class="tabular-nums text-slate-500">{{ $row['value'] }}</span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-indigo-500" style="width: {{ $row['pct'] }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="py-2 text-center text-[11px] text-slate-400">{{ $panel['empty'] }}</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Export & retention --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60" x-data="{ retentionOpen: false }" @open-retention.window="retentionOpen = true">
        <div class="grid gap-0 md:grid-cols-3 md:divide-x md:divide-slate-200/80 dark:md:divide-slate-800">
            <div class="p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                </div>
                <h4 class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Export logs') }}</h4>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Download filtered audit trail as CSV for SOC review or compliance.') }}</p>
                <a href="{{ route('activity-logs.export', $queryExceptPage) }}" class="mt-3 inline-flex text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Export now →') }}</a>
            </div>
            <div class="p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m-8.25 3v6.75m0 0l-3-3m8 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
                <h4 class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Archive logs') }}</h4>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Cold storage for events older than 90 days. Immutable WORM archives.') }}</p>
                <button type="button" class="mt-3 text-xs font-semibold text-amber-600 dark:text-amber-400">{{ __('Configure archive →') }}</button>
            </div>
            <div class="p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </div>
                <h4 class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Compliance exports') }}</h4>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('GDPR, SOC2, and ISO 27001 formatted bundles with retention attestations.') }}</p>
                <button type="button" @click="retentionOpen = true" class="mt-3 text-xs font-semibold text-violet-600 dark:text-violet-400">{{ __('Retention policy →') }}</button>
            </div>
        </div>

        {{-- Retention modal --}}
        <div x-show="retentionOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" @keydown.escape.window="retentionOpen = false">
            <div @click.outside="retentionOpen = false" class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Retention policy') }}</h3>
                </div>
                <div class="space-y-4 px-5 py-4 text-sm">
                    <label class="flex items-center justify-between gap-4">
                        <span class="text-slate-600 dark:text-slate-300">{{ __('Hot storage (searchable)') }}</span>
                        <select class="rounded-lg border-slate-200 text-sm dark:border-slate-700 dark:bg-slate-800"><option>30 days</option><option selected>90 days</option><option>180 days</option></select>
                    </label>
                    <label class="flex items-center justify-between gap-4">
                        <span class="text-slate-600 dark:text-slate-300">{{ __('Cold archive') }}</span>
                        <select class="rounded-lg border-slate-200 text-sm dark:border-slate-700 dark:bg-slate-800"><option>1 year</option><option selected>7 years</option></select>
                    </label>
                    <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                        <input type="checkbox" checked class="rounded border-slate-300 text-indigo-600" />
                        {{ __('Enable compliance export signing') }}
                    </label>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                    <button type="button" @click="retentionOpen = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Cancel') }}</button>
                    <button type="button" @click="retentionOpen = false" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save policy') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
