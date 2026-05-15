@php
    $statusVariant = fn (string $s): string => match ($s) {
        'active', 'live' => 'success',
        'maintenance', 'building', 'deploying' => 'warning',
        'suspended', 'failed' => 'danger',
        default => 'info',
    };

    $deployLabel = fn (string $s): string => match ($s) {
        'live' => __('Live'),
        'building' => __('Building'),
        'deploying' => __('Deploying'),
        'failed' => __('Failed'),
        default => ucfirst($s),
    };

    $ciColor = fn (string $s): string => match ($s) {
        'passed' => 'text-emerald-500',
        'running' => 'text-cyan-400 animate-pulse',
        'failed' => 'text-rose-500',
        default => 'text-amber-500',
    };
@endphp

<x-dashboard-layout :heading="__('Hosted Projects')" :subheading="__('Cloud product operations & deployment control')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('CI/CD connected') }}</p>
            </div>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Product & Deployment Center') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Manage SaaS products, environments, deployments, and infrastructure allocation') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" title="{{ __('Deploy all pending') }}">
                <svg class="h-4 w-4 text-cyan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                {{ __('Deploy') }}
            </button>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('Add Project') }}
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <x-ui.kpi-card :title="__('Total Projects')" :value="$kpis['total_projects']" :points="$spark('projects')" tone="indigo">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15a2.25 2.25 0 012.25 2.25v.75m-18 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-18 0v-1.5A2.25 2.25 0 014.5 9h15a2.25 2.25 0 012.25 2.25v1.5" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Production Apps')" :value="$kpis['production_apps']" :sublabel="__('Live environments')" :points="$spark('prod')" tone="emerald">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Active Deployments')" :value="$kpis['active_deployments']" :sublabel="__('Build &amp; deploy in flight')" :points="$spark('deploy')" tone="sky">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Failed Deployments')" :value="$kpis['failed_deployments']" :sublabel="__('Requires rollback review')" :points="$spark('failed')" tone="rose">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Active Tenants')" :value="$kpis['active_tenants']" :sublabel="__('Mapped to products')" :points="$spark('tenants')" tone="violet">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg></x-slot>
        </x-ui.kpi-card>
        <x-ui.kpi-card :title="__('Average Uptime')" :value="$kpis['avg_uptime'].'%'" :animate="false" :sublabel="__('Rolling 30-day SLO')" :points="$spark('uptime')" tone="amber">
            <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></x-slot>
        </x-ui.kpi-card>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        {{-- Main table --}}
        <div class="space-y-4 lg:col-span-8">
            <form method="GET" class="flex flex-wrap gap-2 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search projects…') }}" class="min-w-[12rem] flex-1 rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                <select name="environment" class="rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">{{ __('All environments') }}</option>
                    <option value="production" @selected($filters['environment'] === 'production')>{{ __('Production') }}</option>
                    <option value="staging" @selected($filters['environment'] === 'staging')>{{ __('Staging') }}</option>
                </select>
                <select name="status" class="rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="active" @selected($filters['status'] === 'active')>{{ __('Active') }}</option>
                    <option value="maintenance" @selected($filters['status'] === 'maintenance')>{{ __('Maintenance') }}</option>
                    <option value="suspended" @selected($filters['status'] === 'suspended')>{{ __('Suspended') }}</option>
                </select>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">{{ __('Filter') }}</button>
            </form>

            <x-ui.table-panel :title="__('Hosted products')" :action-href="route('projects.create')" :action-label="__('Add')">
                <table class="prady-table min-w-[1200px]">
                    <thead>
                        <tr>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Domain') }}</th>
                            <th>{{ __('Environment') }}</th>
                            <th>{{ __('Hosting Server') }}</th>
                            <th class="text-right">{{ __('Tenants') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Uptime') }}</th>
                            <th>{{ __('Last Deployment') }}</th>
                            <th>{{ __('Version') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse ($enrichedRows as $row)
                            @php $project = $row['project']; @endphp
                            <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                <td>
                                    <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2 font-semibold text-indigo-600 dark:text-indigo-400">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 text-xs font-bold text-indigo-700 dark:text-indigo-200">{{ mb_strtoupper(mb_substr($project->name, 0, 2)) }}</span>
                                        {{ $project->name }}
                                    </a>
                                </td>
                                <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $project->domain }}</td>
                                <td>
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1',
                                        'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300' => $row['environment'] === 'production',
                                        'bg-amber-500/10 text-amber-800 ring-amber-500/20 dark:text-amber-200' => $row['environment'] === 'staging',
                                    ])>{{ $row['environment'] }}</span>
                                </td>
                                <td class="text-sm text-slate-600 dark:text-slate-300">{{ $project->server?->name ?? '—' }}</td>
                                <td class="text-right tabular-nums font-medium">{{ $project->tenants_count }}</td>
                                <td>
                                    <x-ui.status-badge :variant="$statusVariant($project->status)">{{ $project->status }}</x-ui.status-badge>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2 min-w-[5rem]">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width: {{ min(100, $row['uptime_pct']) }}%"></div>
                                        </div>
                                        <span class="text-[11px] tabular-nums font-semibold text-slate-600 dark:text-slate-300">{{ number_format($row['uptime_pct'], 1) }}%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-500">
                                    <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $row['last_deployment']['deployed_at']->diffForHumans() }}</span>
                                    <span class="{{ $ciColor($row['ci_status']) }}">{{ $deployLabel($row['deploy_status']) }}</span>
                                </td>
                                <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $row['version'] }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1 opacity-80 group-hover:opacity-100">
                                        <a href="{{ route('projects.show', $project) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" title="{{ __('View') }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </a>
                                        <a href="{{ route('projects.edit', $project) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="{{ __('Edit') }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-sm text-slate-500">{{ __('No hosted projects yet. Add your first SaaS product.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <x-slot name="footer">{{ $projects->links() }}</x-slot>
            </x-ui.table-panel>
        </div>

        {{-- Deployment center sidebar --}}
        <div class="space-y-4 lg:col-span-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-900 to-slate-950 shadow-card dark:border-slate-700">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold text-white">{{ __('Deployment center') }}</h3>
                    <p class="text-[11px] text-slate-400">{{ __('Recent pipeline activity') }}</p>
                </div>
                <ul class="max-h-80 divide-y divide-white/5 overflow-y-auto">
                    @foreach ($recentDeployments as $dep)
                        <li class="px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold text-white">
                                        <a href="{{ route('projects.show', $dep['project_id']) }}" class="hover:text-cyan-300">{{ $dep['project'] }}</a>
                                    </p>
                                    <p class="mt-0.5 font-mono text-[10px] text-cyan-400/90">{{ $dep['version'] }}</p>
                                </div>
                                <x-ui.status-badge :variant="$dep['status'] === 'success' ? 'success' : 'danger'" class="!text-[9px]">{{ $dep['status'] }}</x-ui.status-badge>
                            </div>
                            <p class="mt-1.5 text-[10px] text-slate-500">{{ $dep['triggered_by'] }} · {{ $dep['deployed_at']->diffForHumans() }} · {{ $dep['duration_sec'] }}s</p>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-white/10 px-4 py-3">
                    <div class="flex gap-2">
                        <button type="button" class="flex-1 rounded-lg bg-cyan-600/20 py-2 text-[11px] font-semibold text-cyan-300 ring-1 ring-cyan-500/30">{{ __('Rollback') }}</button>
                        <button type="button" class="flex-1 rounded-lg bg-white/5 py-2 text-[11px] font-semibold text-slate-300 ring-1 ring-white/10">{{ __('View logs') }}</button>
                    </div>
                </div>
            </div>

            {{-- Project health snapshot --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Fleet health') }}</h3>
                <ul class="mt-3 space-y-3 text-xs">
                    @foreach ($enrichedRows->take(4) as $row)
                        <li>
                            <div class="flex justify-between font-semibold text-slate-700 dark:text-slate-200">
                                <span class="truncate">{{ $row['project']->name }}</span>
                                <span class="tabular-nums text-slate-500">{{ $row['response_ms'] }}ms</span>
                            </div>
                            <div class="mt-1 flex gap-2 text-[10px] text-slate-500">
                                <span>{{ __('Errors') }} {{ $row['error_rate'] }}%</span>
                                <span>·</span>
                                <span>{{ __('SSL') }} {{ $row['ssl_health'] }}</span>
                                <span>·</span>
                                <span>{{ $row['bandwidth_gb'] }} GB</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Infrastructure mapping --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Infrastructure mapping') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Server allocation · staging vs production · scaling') }}</p>
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($infrastructure as $node)
                <div class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $node['server'] }}</span>
                        <span class="h-2 w-2 rounded-full {{ $node['status'] === 'online' ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.6)]' : 'bg-rose-500' }}"></span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ __(':count projects', ['count' => $node['projects']]) }} · {{ $node['production'] }} prod / {{ $node['staging'] }} stg</p>
                    <div class="mt-3 space-y-2">
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500"><span>CPU</span><span>{{ $node['cpu_pct'] }}%</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-cyan-500" style="width: {{ $node['cpu_pct'] }}%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500"><span>{{ __('Storage') }}</span><span>{{ $node['storage_pct'] }}%</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $node['storage_pct'] }}%"></div></div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full py-6 text-center text-sm text-slate-500">{{ __('Add servers to map infrastructure to projects.') }}</p>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
