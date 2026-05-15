@php
    $growthMax = max(collect($storageGrowth)->max('value') ?? 0, 1);
    $statusVariant = fn (string $s): string => match ($s) {
        'successful' => 'success',
        'running' => 'info',
        'failed' => 'danger',
        'queued' => 'neutral',
        'warning' => 'warning',
        default => 'neutral',
    };
    $alertRing = fn (string $t): string => match ($t) {
        'critical', 'danger' => 'ring-rose-500/30 bg-rose-500/10',
        'warning' => 'ring-amber-500/30 bg-amber-500/10',
        'success' => 'ring-emerald-500/30 bg-emerald-500/10',
        default => 'ring-sky-500/30 bg-sky-500/10',
    };
@endphp

<x-dashboard-layout :heading="__('Backups')" :subheading="__('Disaster recovery & snapshot operations')">
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
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('Infrastructure') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Backup Management Center') }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Server snapshots, database archives, tenant vaults, schedules, and disaster recovery — unified operations console.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('backups.run') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347a1.125 1.125 0 0 1-1.667-.985V5.653Z" /></svg>
                                {{ __('Run Backup') }}
                            </button>
                        </form>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                            {{ __('Restore Backup') }}
                        </button>
                        <a href="#schedules" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            {{ __('Configure Schedule') }}
                        </a>
                    </div>
                </div>

                @if ($runningBackups > 0)
                    <div class="flex items-center gap-3 rounded-2xl border border-cyan-500/25 bg-gradient-to-r from-cyan-500/10 via-indigo-500/5 to-violet-500/10 px-5 py-3 ring-1 ring-cyan-500/20">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-cyan-500"></span>
                        </span>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ __(':count backup job(s) currently running across the fleet.', ['count' => $runningBackups]) }}
                        </p>
                    </div>
                @endif

                {{-- KPIs --}}
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <x-ui.kpi-card :title="__('Total Backups')" :value="$kpis['total']" :trend="'+12%'" :sublabel="__('All job types')" :points="$spark('bk-total')" tone="indigo">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Successful')" :value="$kpis['successful']" :trend="$kpis['successRate'].'%'" :sublabel="__('Success rate')" :points="$spark('bk-ok')" tone="emerald">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Failed')" :value="$kpis['failed']" :trend="$kpis['failed'] > 0 ? '-2' : '0'" :sublabel="__('Requires attention')" :points="$spark('bk-fail')" tone="rose">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Storage Consumed')" :value="$kpis['storage']" :animate="false" :trend="'+8.4%'" :sublabel="__('Object + block storage')" :points="$spark('bk-storage')" tone="violet">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Restore Points')" :value="$kpis['restorePoints']" :trend="'+3'" :sublabel="__('Verified snapshots')" :points="$spark('bk-rp')" tone="sky">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Last Runtime')" :value="$kpis['lastRuntime']" :animate="false" :trend="'−6%'" :sublabel="__('Longest recent job')" :points="$spark('bk-time')" tone="amber">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                </div>

                {{-- Jobs + Alerts --}}
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <x-ui.table-panel :title="__('Backup Jobs')">
                            <table class="prady-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Backup Name') }}</th>
                                        <th>{{ __('Server') }}</th>
                                        <th>{{ __('Tenant') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="text-right">{{ __('Size') }}</th>
                                        <th>{{ __('Last Run') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    @forelse ($backups as $backup)
                                        <tr class="group">
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                                                            </span>
                                                            <div>
                                                                <p class="font-semibold text-slate-900 dark:text-white">{{ $backup->name }}</p>
                                                                @if ($backup->is_restore_point)
                                                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600 dark:text-cyan-400">{{ __('Restore point') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $backup->server?->name ?? '—' }}</td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $backup->tenant?->company_name ?? '—' }}</td>
                                            <td>
                                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $backup->backup_type }}</span>
                                            </td>
                                            <td class="text-right font-mono text-xs tabular-nums text-slate-600 dark:text-slate-300">{{ $backup->size_bytes ? $backup->formattedSize() : '—' }}</td>
                                            <td class="text-xs text-slate-500 dark:text-slate-400">{{ $backup->started_at?->diffForHumans() ?? '—' }}</td>
                                            <td class="font-mono text-xs tabular-nums text-slate-500">{{ $backup->formattedDuration() }}</td>
                                            <td>
                                                <x-ui.status-badge :variant="$statusVariant($backup->status)">
                                                    @if ($backup->status === 'running')
                                                        <span class="relative mr-0.5 flex h-1.5 w-1.5">
                                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span>
                                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                        </span>
                                                    @endif
                                                    {{ ucfirst($backup->status) }}
                                                </x-ui.status-badge>
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex gap-1 opacity-70 transition group-hover:opacity-100">
                                                            <button type="button" title="{{ __('Download Archive') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                            </button>
                                                            <button type="button" title="{{ __('Verify Integrity') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-slate-800 dark:hover:text-emerald-400">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                            </button>
                                                            <button type="button" title="{{ __('View Logs') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-violet-600 dark:hover:bg-slate-800 dark:hover:text-violet-400">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-12 text-center text-sm text-slate-500">{{ __('No backup jobs recorded yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <x-slot name="footer">{{ $backups->links() }}</x-slot>
                        </x-ui.table-panel>
                    </div>

                    <div class="space-y-5 lg:col-span-4">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Failed Backup Alerts') }}</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Critical operational signals') }}</p>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($alerts as $alert)
                                    <li class="flex gap-3 px-4 py-3.5">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $alertRing($alert['type']) }}">
                                            @if ($alert['type'] === 'danger' || $alert['type'] === 'critical')
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

                        {{-- DR Panel --}}
                        <div class="overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-50/80 via-white to-violet-50/50 shadow-card dark:border-indigo-900/40 dark:from-indigo-950/40 dark:via-slate-900 dark:to-violet-950/30">
                            <div class="border-b border-indigo-200/50 px-4 py-3 dark:border-indigo-900/50">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Disaster Recovery') }}</h2>
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Restore drill') }}</span>
                                    <x-ui.status-badge :variant="$drMetrics['restore_drill_status'] === 'passed' ? 'success' : 'warning'">
                                        {{ $drMetrics['restore_drill_status'] === 'passed' ? __('Passed') : __('Attention') }}
                                    </x-ui.status-badge>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                                <p class="text-slate-500 dark:text-slate-400">{{ __('Last restore test') }}</p>
                                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $drMetrics['last_restore_test'] }}</p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                                <p class="text-slate-500 dark:text-slate-400">{{ __('Integrity check') }}</p>
                                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $drMetrics['last_integrity_check'] }}</p>
                                            </div>
                                            <div class="rounded-xl border border-cyan-200/60 bg-cyan-50/50 px-3 py-2.5 dark:border-cyan-900/40 dark:bg-cyan-950/30">
                                                <p class="text-cyan-700 dark:text-cyan-300">{{ __('RPO') }}</p>
                                                <p class="mt-1 font-mono text-sm font-bold text-cyan-900 dark:text-cyan-100">{{ $drMetrics['rpo'] }}</p>
                                            </div>
                                            <div class="rounded-xl border border-violet-200/60 bg-violet-50/50 px-3 py-2.5 dark:border-violet-900/40 dark:bg-violet-950/30">
                                                <p class="text-violet-700 dark:text-violet-300">{{ __('RTO') }}</p>
                                                <p class="mt-1 font-mono text-sm font-bold text-violet-900 dark:text-violet-100">{{ $drMetrics['rto'] }}</p>
                                            </div>
                                        </div>
                                <div>
                                    <div class="mb-1 flex justify-between text-xs">
                                        <span class="text-slate-500 dark:text-slate-400">{{ __('Integrity pass rate') }}</span>
                                        <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $drMetrics['integrity_pass_rate'] }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all" style="width: {{ min(100, $drMetrics['integrity_pass_rate']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Schedules --}}
                <div id="schedules" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Backup Schedules') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Cron timing, retention, and next execution') }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="prady-table min-w-[720px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Schedule') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Cron') }}</th>
                                    <th>{{ __('Next run') }}</th>
                                    <th>{{ __('Retention') }}</th>
                                    <th>{{ __('Target') }}</th>
                                    <th class="text-center">{{ __('Enabled') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($schedules as $schedule)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">{{ $schedule->name }}</td>
                                        <td><span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $schedule->typeLabel() }}</span></td>
                                        <td class="font-mono text-xs text-slate-500">{{ $schedule->cron_expression }}</td>
                                        <td class="text-xs text-slate-600 dark:text-slate-300">{{ $schedule->next_run_at?->format('M j, H:i') ?? '—' }}</td>
                                        <td class="text-xs text-slate-500">{{ $schedule->retention_policy }}</td>
                                        <td class="text-xs text-slate-500">
                                            {{ $schedule->server?->name ?? $schedule->tenant?->company_name ?? __('Fleet-wide') }}
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('backups.schedules.toggle', $schedule) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    role="switch"
                                                    aria-checked="{{ $schedule->enabled ? 'true' : 'false' }}"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $schedule->enabled ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600' }}"
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

                {{-- Storage visualization --}}
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Backup Storage Growth') }}</h2>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('6-month archive footprint trend') }}</p>
                                    <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-semibold text-violet-700 ring-1 ring-violet-500/20 dark:text-violet-300">+8.4%</span>
                            </div>
                            <div class="flex h-40 items-end gap-2" aria-hidden="true">
                                @foreach ($storageGrowth as $point)
                                    @php $h = max(12, (int) round(($point['value'] / $growthMax) * 140)); @endphp
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div
                                                            class="w-full rounded-t-md bg-gradient-to-t from-indigo-600/70 to-cyan-400/90 transition-all hover:from-indigo-500 hover:to-cyan-300"
                                                            style="height: {{ $h }}px"
                                                            title="{{ \App\Models\Backup::formatBytes((int) $point['value']) }}"
                                                        ></div>
                                                    <span class="text-[10px] font-medium text-slate-500">{{ $point['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5 lg:col-span-5">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Server Usage') }}</h2>
                            <ul class="mt-4 space-y-3">
                                @foreach ($serverStorage as $row)
                                    <li>
                                        <div class="mb-1 flex justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $row['name'] }}</span>
                                            <span class="tabular-nums text-slate-500">{{ \App\Models\Backup::formatBytes($row['bytes']) }} · {{ $row['pct'] }}%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ $row['pct'] }}%"></div>
                                                </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @if ($tenantStorage->isNotEmpty())
                            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant Distribution') }}</h2>
                                <ul class="mt-4 space-y-3">
                                    @foreach ($tenantStorage as $row)
                                        <li>
                                            <div class="mb-1 flex justify-between text-xs">
                                                <span class="font-medium text-slate-700 dark:text-slate-200">{{ $row['name'] }}</span>
                                                <span class="tabular-nums text-slate-500">{{ $row['pct'] }}%</span>
                                            </div>
                                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500" style="width: {{ $row['pct'] }}%"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-dashboard-layout>

