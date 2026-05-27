@php
    $statusVariant = fn (string $s): string => match ($s) {
        'success', 'done', 'live' => 'success',
        'failed', 'cancelled' => 'danger',
        'in_progress', 'building', 'deploying', 'active', 'running' => 'info',
        'queued', 'pending', 'warning' => 'warning',
        'rolled_back' => 'purple',
        default => 'neutral',
    };

    $stageRing = fn (string $s): string => match ($s) {
        'done' => 'border-emerald-500/50 bg-emerald-500/15 text-emerald-600 dark:text-emerald-300',
        'active' => 'border-cyan-500/50 bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 ring-2 ring-cyan-500/30',
        'failed' => 'border-rose-500/50 bg-rose-500/15 text-rose-600 dark:text-rose-300',
        default => 'border-slate-500/30 bg-slate-500/10 text-slate-500',
    };

    $alertRing = fn (string $s): string => match ($s) {
        'CRITICAL' => 'border-rose-500/30 bg-rose-500/10',
        'WARNING' => 'border-amber-500/30 bg-amber-500/10',
        default => 'border-sky-500/25 bg-sky-500/10',
    };

    $freqMax = max(collect($metrics['frequency'])->max('count') ?? 0, 1);
@endphp

<x-dashboard-layout :heading="__('Deployments')" :subheading="__('DevOps release management')">
    <div
        x-data="devopsReleaseCenter(@js($detailPayload))"
        class="space-y-6"
    >
        @if (session('status'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.9)]"></span>
                    </span>
                    <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('CI/CD operational') }}</p>
                </div>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Release Management Center') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('Pipelines, environments, rollbacks, build logs, and deployment governance across your SaaS fleet.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showDeployModal = true" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 via-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 hover:brightness-110">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('New Deployment') }}
                </button>
                <a href="{{ route('hosted-projects.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Hosted Projects') }}</a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Total Deployments')" :value="$kpis['total']" :trend="$kpis['total_trend']" :points="$spark('dep-total')" tone="indigo">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Successful')" :value="$kpis['successful']" :trend="$kpis['success_rate'].'%'" :sublabel="__('Success rate')" :points="$spark('dep-ok')" tone="emerald">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Failed')" :value="$kpis['failed']" :sublabel="__('Needs investigation')" :points="$spark('dep-fail')" tone="rose">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.75a9 9 0 1 1 18 0 9 9 0 0 1-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Rollbacks')" :value="$kpis['rollbacks']" :points="$spark('dep-rb')" tone="amber">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Active Pipelines')" :value="$kpis['active_pipelines']" :sublabel="__('In flight')" :points="$spark('dep-pipe')" tone="sky">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347a1.125 1.125 0 0 1-1.667-.985V5.653Z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg Deploy Time')" :value="$kpis['avg_duration']" :animate="false" :sublabel="__('Build to live')" :points="$spark('dep-time')" tone="violet">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Pipeline --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-5 text-white shadow-2xl ring-1 ring-white/10 dark:border-slate-700">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-cyan-400">{{ __('Deployment pipeline') }}</p>
                    <h3 class="text-sm font-semibold" x-text="selected ? selected.project + ' · ' + selected.version : '{{ __('Fleet release train') }}'"></h3>
                </div>
                <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-mono uppercase" x-show="selected" x-text="selected?.strategy?.replace('_', '-')"></span>
            </div>
            <div class="flex flex-wrap items-stretch gap-2 overflow-x-auto pb-2">
                @foreach ($pipeline as $i => $stage)
                    <div class="flex min-w-[7rem] flex-1 flex-col items-center">
                        <div class="w-full rounded-xl border px-2 py-3 text-center {{ $stageRing($stage['status']) }}">
                            <p class="text-[10px] font-bold uppercase tracking-wider">{{ $stage['label'] }}</p>
                            <p class="mt-1 text-[10px] capitalize opacity-80">{{ $stage['status'] }}</p>
                            <p class="mt-1 font-mono text-[10px] text-slate-400">{{ $stage['duration_sec'] }}s</p>
                        </div>
                        @if ($i < count($pipeline) - 1)
                            <div class="hidden h-0.5 w-full max-w-[2rem] bg-gradient-to-r from-cyan-500/50 to-indigo-500/50 sm:block"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-12">
            <div class="space-y-5 lg:col-span-8">
                {{-- History table --}}
                <x-ui.table-panel :title="__('Deployment history')">
                    <table class="prady-table text-xs">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Environment') }}</th>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Triggered By') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Deployed') }}</th>
                                <th class="text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($deploymentHistory as $dep)
                                <tr class="cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/40" @click="select(@js($dep['id']))" :class="selectedId === @js($dep['id']) && 'bg-cyan-500/5'">
                                    <td class="font-mono text-[11px] text-indigo-600 dark:text-indigo-400">{{ $dep['deployment_id'] }}</td>
                                    <td class="font-semibold text-slate-900 dark:text-white">{{ $dep['project'] }}</td>
                                    <td class="capitalize">{{ $dep['environment'] }}</td>
                                    <td class="font-mono">{{ $dep['version'] }}</td>
                                    <td class="font-mono text-slate-500">{{ $dep['branch'] }}</td>
                                    <td><x-ui.status-badge :variant="$statusVariant($dep['status'])">{{ str_replace('_', ' ', $dep['status']) }}</x-ui.status-badge></td>
                                    <td class="text-slate-500">{{ $dep['triggered_by'] }}</td>
                                    <td class="tabular-nums">{{ $dep['duration'] }}</td>
                                    <td class="text-slate-500">{{ $dep['deployed_at_human'] }}</td>
                                    <td class="text-right" @click.stop>
                                        @if (!empty($dep['record_id']))
                                            <div class="flex justify-end gap-1">
                                                <form method="POST" action="{{ route('deployments.rollback', $dep['record_id']) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-500/30">{{ __('Rollback') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('deployments.redeploy', $dep['record_id']) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded px-1.5 py-0.5 text-[10px] font-semibold text-cyan-700 ring-1 ring-cyan-500/30">{{ __('Redeploy') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-panel>

                {{-- Environments --}}
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Environment management') }}</p>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($environments as $env)
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ $env['label'] }}</h4>
                                    <span @class([
                                        'h-2 w-2 rounded-full',
                                        'bg-emerald-500 animate-pulse' => $env['health'] === 'healthy',
                                        'bg-rose-500' => $env['health'] === 'critical',
                                        'bg-slate-400' => $env['health'] === 'idle',
                                    ])></span>
                                </div>
                                <p class="mt-2 font-mono text-sm text-cyan-600 dark:text-cyan-400">{{ $env['version'] }}</p>
                                <dl class="mt-3 space-y-1 text-[11px] text-slate-500">
                                    <div class="flex justify-between"><dt>{{ __('Uptime') }}</dt><dd class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $env['uptime'] }}%</dd></div>
                                    <div class="flex justify-between"><dt>{{ __('Last deploy') }}</dt><dd>{{ $env['last_deploy'] }}</dd></div>
                                    <div class="flex justify-between"><dt>{{ __('Projects') }}</dt><dd>{{ $env['projects'] }}</dd></div>
                                </dl>
                                @if ($env['rollback_available'])
                                    <p class="mt-2 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Rollback available') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Metrics --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">{{ __('Deployment frequency') }}</p>
                        <div class="mt-3 flex h-24 items-end gap-2">
                            @foreach ($metrics['frequency'] as $day)
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div class="w-full rounded-t bg-gradient-to-t from-violet-600 to-indigo-400" style="height: {{ max(8, ($day['count'] / $freqMax) * 100) }}%"></div>
                                    <span class="text-[10px] text-slate-500">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('Infrastructure impact') }}</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                            <li class="flex justify-between"><span>{{ __('Container deploys') }}</span><span class="font-semibold tabular-nums">{{ $metrics['container_deploys'] }}</span></li>
                            <li class="flex justify-between"><span>{{ __('Infra changes') }}</span><span class="font-semibold tabular-nums">{{ $metrics['infra_changes'] }}</span></li>
                            <li class="flex justify-between"><span>{{ __('Scaling ops') }}</span><span class="font-semibold tabular-nums">{{ $metrics['scaling_ops'] }}</span></li>
                            <li class="flex justify-between"><span>{{ __('Success rate') }}</span><span class="font-semibold text-emerald-600">{{ $metrics['success_rate'] }}%</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="space-y-5 lg:col-span-4">
                {{-- Alerts --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">{{ __('Alerts & incidents') }}</p>
                    <div class="mt-3 max-h-48 space-y-2 overflow-y-auto">
                        @foreach ($alerts as $alert)
                            <div class="rounded-xl border px-3 py-2 {{ $alertRing($alert['severity']) }}">
                                <div class="flex justify-between text-[10px] font-bold">{{ $alert['severity'] }}<span class="font-normal text-slate-500">{{ $alert['time'] }}</span></div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                <p class="text-xs text-slate-500">{{ $alert['body'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Rollbacks --}}
                <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 dark:border-amber-500/30">
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-700 dark:text-amber-300">{{ __('Rollback management') }}</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($rollbacks as $rb)
                            <li class="flex items-center justify-between rounded-lg bg-white/60 px-2 py-2 text-xs dark:bg-slate-900/40">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $rb['project'] }}</p>
                                    <p class="font-mono text-slate-500">{{ $rb['version'] }}</p>
                                </div>
                                @if (!empty($rb['record_id']))
                                    <form method="POST" action="{{ route('deployments.rollback', $rb['record_id']) }}">@csrf<button type="submit" class="rounded-lg bg-amber-600 px-2 py-1 text-[10px] font-semibold text-white">{{ __('Rollback') }}</button></form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Build logs --}}
                <div x-show="selected" x-cloak class="rounded-2xl border border-slate-800 bg-slate-950 p-4 font-mono text-xs text-emerald-400 ring-1 ring-emerald-500/20">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ __('Build logs') }}</p>
                    <template x-if="selected?.build_logs?.length">
                        <template x-for="(line, idx) in selected.build_logs" :key="idx">
                            <p class="leading-relaxed" x-text="line"></p>
                        </template>
                    </template>
                    <p x-show="!selected?.build_logs?.length" class="text-slate-500">{{ __('Select a deployment to view logs.') }}</p>
                    <div x-show="selected" class="mt-3 border-t border-slate-800 pt-2 text-slate-400">
                        <p x-text="'commit ' + (selected?.commit || '')"></p>
                        <p x-text="'risk score: ' + (selected?.risk_score || 0)"></p>
                    </div>
                </div>

                {{-- Integrations --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Integrations') }}</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($integrations as $int)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <div>
                                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $int['name'] }}</span>
                                    <p class="text-[10px] text-slate-500">{{ $int['repos'] }} {{ __('repos') }} · {{ $int['webhooks'] }} {{ __('hooks') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-emerald-700 dark:text-emerald-300">{{ $int['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div x-show="selected" x-cloak class="flex flex-wrap gap-2">
                    <template x-if="selected?.record_id">
                        <form method="POST" :action="`{{ url('deployments') }}/${selected.record_id}/approve`">@csrf<button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white">{{ __('Approve Release') }}</button></form>
                        <form method="POST" :action="`{{ url('deployments') }}/${selected.record_id}/cancel`">@csrf<button type="submit" class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300">{{ __('Cancel') }}</button></form>
                    </template>
                </div>
            </div>
        </div>

        {{-- Deploy modal --}}
        <div x-show="showDeployModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @keydown.escape.window="showDeployModal = false">
            <div @click.outside="showDeployModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('New deployment') }}</h3>
                <form method="POST" action="{{ route('deployments.deploy') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Project') }}</label>
                        <select name="project_id" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Environment') }}</label>
                        <select name="environment" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            <option value="production">{{ __('Production') }}</option>
                            <option value="staging">{{ __('Staging') }}</option>
                            <option value="development">{{ __('Development') }}</option>
                            <option value="qa">{{ __('QA') }}</option>
                            <option value="sandbox">{{ __('Sandbox') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Version') }}</label>
                        <input type="text" name="version" placeholder="v2.5.0" class="mt-1 w-full rounded-xl border-slate-300 font-mono text-sm dark:border-slate-600 dark:bg-slate-800" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showDeployModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600">{{ __('Cancel') }}</button>
                        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Deploy') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-layout>


