@php
    $healthStyles = fn (string $h): array => match ($h) {
        'healthy' => ['ring' => 'ring-emerald-500/40', 'dot' => 'bg-emerald-500', 'glow' => 'shadow-[0_0_20px_rgba(16,185,129,0.35)]', 'badge' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300', 'label' => __('Healthy')],
        'warning' => ['ring' => 'ring-amber-500/40', 'dot' => 'bg-amber-500', 'glow' => 'shadow-[0_0_20px_rgba(245,158,11,0.35)]', 'badge' => 'bg-amber-500/15 text-amber-800 dark:text-amber-200', 'label' => __('Warning')],
        'critical' => ['ring' => 'ring-rose-500/50', 'dot' => 'bg-rose-500', 'glow' => 'shadow-[0_0_24px_rgba(244,63,94,0.45)]', 'badge' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300', 'label' => __('Critical')],
        'offline' => ['ring' => 'ring-slate-500/30', 'dot' => 'bg-slate-400', 'glow' => '', 'badge' => 'bg-slate-500/15 text-slate-600 dark:text-slate-300', 'label' => __('Offline')],
        default => ['ring' => 'ring-sky-500/30', 'dot' => 'bg-sky-500', 'glow' => '', 'badge' => 'bg-sky-500/15 text-sky-800 dark:text-sky-200', 'label' => __('Unknown')],
    };

    $alertRing = fn (string $s): string => match ($s) {
        'CRITICAL' => 'ring-rose-500/35 bg-rose-500/10 border-rose-500/25',
        'WARNING' => 'ring-amber-500/35 bg-amber-500/10 border-amber-500/25',
        'INFO' => 'ring-sky-500/30 bg-sky-500/10 border-sky-500/20',
        default => 'ring-slate-500/25 bg-slate-500/5 border-slate-500/20',
    };

    $netMax = max(collect($networkSeries)->max('in') ?? 0, collect($networkSeries)->max('out') ?? 0, 1);
    $incidentMax = max(collect($incidentTrends)->max('count') ?? 0, 1);
    $uptimeMax = 100;

    $areaChart = function (array $points, string $stroke, string $fill, int $w = 280, int $h = 72): string {
        $pts = collect($points)->values()->take(24)->map(fn ($v) => (float) $v)->all();
        if (count($pts) < 2) {
            $pts = [40, 55, 48, 62, 58, 70, 66, 74];
        }
        $min = min($pts);
        $max = max($pts);
        $range = max(1e-6, $max - $min);
        $linePts = [];
        foreach ($pts as $i => $v) {
            $x = ($i / (count($pts) - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 6) - 3;
            $linePts[] = round($x, 1).','.round($y, 1);
        }
        $line = implode(' ', $linePts);

        return '<svg class="w-full h-full" viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="none" aria-hidden="true"><polygon points="0,'.$h.' '.$line.' '.$w.','.$h.'" class="'.$fill.'"/><polyline points="'.$line.'" class="'.$stroke.' fill-none" stroke-width="2" vector-effect="non-scaling-stroke"/></svg>';
    };
@endphp

<x-dashboard-layout :heading="__('Server Health')" :subheading="__('Real-time cloud infrastructure observability')">
    <div
        x-data="infraObservability(@js($detailPayload), @js($fleetCards->values()->all()))"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-70"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.9)]"></span>
                    </span>
                    <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('Live telemetry') }}</p>
                    <span class="rounded-full bg-slate-900/5 px-2 py-0.5 text-[10px] font-mono text-slate-500 dark:bg-white/5 dark:text-slate-400" x-text="lastRefresh"></span>
                </div>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Infrastructure Monitoring Center') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Fleet-wide VPS observability — CPU, memory, disk, network, uptime, services, and incident response in one NOC-grade console.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('servers.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>
                    {{ __('Manage servers') }}
                </a>
                <button type="button" @click="simulateRefresh()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 via-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" :class="refreshing && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    {{ __('Refresh metrics') }}
                </button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Total Servers')" :value="$kpis['total']" :trend="'+2'" :sublabel="__('Registered VPS nodes')" :points="$spark('sh-total')" tone="indigo">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Online Servers')" :value="$kpis['online']" :trend="round(($kpis['online'] / max(1, $kpis['total'])) * 100).'%'" :sublabel="__('Heartbeat OK')" :points="$spark('sh-online')" tone="emerald">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg CPU')" :value="$kpis['avgCpu'].'%'" :animate="false" :trend="'+4.2%'" :sublabel="__('Fleet aggregate')" :points="$fleetCpuSeries" tone="sky">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg RAM')" :value="$kpis['avgRam'].'%'" :animate="false" :trend="'+1.8%'" :sublabel="__('Memory pressure')" :points="$fleetRamSeries" tone="violet">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Avg Disk')" :value="$kpis['avgDisk'].'%'" :animate="false" :trend="'−0.6%'" :sublabel="__('Block storage')" :points="$utilizationTrends['disk']" tone="amber">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Active Alerts')" :value="$kpis['activeAlerts']" :trend="$kpis['activeAlerts'] > 0 ? '!' : '✓'" :sublabel="__('WARNING + CRITICAL')" :points="$spark('sh-alerts')" tone="rose">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.75a9 9 0 1118 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Live charts + Alerts --}}
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="space-y-5 lg:col-span-8">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/50">
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400">{{ __('Real-time monitoring') }}</p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Fleet resource telemetry (24h)') }}</h3>
                    </div>
                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('CPU utilization') }}</p>
                            <div class="h-20 rounded-xl bg-gradient-to-b from-indigo-500/5 to-transparent p-1 ring-1 ring-indigo-500/10">
                                {!! $areaChart($fleetCpuSeries, 'stroke-indigo-500', 'fill-indigo-500/15') !!}
                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('RAM utilization') }}</p>
                            <div class="h-20 rounded-xl bg-gradient-to-b from-violet-500/5 to-transparent p-1 ring-1 ring-violet-500/10">
                                {!! $areaChart($fleetRamSeries, 'stroke-violet-500', 'fill-violet-500/15') !!}
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <span>{{ __('Network throughput (Mbps)') }}</span>
                                <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ __('IN') }} / {{ __('OUT') }}</span>
                            </p>
                            <div class="flex h-24 items-end gap-0.5 rounded-xl bg-slate-950/5 p-3 dark:bg-black/20">
                                @foreach (array_slice($networkSeries, -18) as $pt)
                                    <div class="group flex flex-1 flex-col items-center gap-1">
                                        <div class="flex w-full items-end justify-center gap-px" style="height: 4.5rem">
                                            <div class="w-1/2 rounded-t bg-gradient-to-t from-cyan-600 to-cyan-400 opacity-90 transition group-hover:opacity-100" style="height: {{ min(100, ($pt['in'] / $netMax) * 100) }}%"></div>
                                            <div class="w-1/2 rounded-t bg-gradient-to-t from-indigo-600 to-violet-400 opacity-70 transition group-hover:opacity-100" style="height: {{ min(100, ($pt['out'] / $netMax) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Server grid --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Fleet nodes') }}</p>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Server health grid') }}</h3>
                        </div>
                        <span class="text-xs text-slate-500">{{ __('Click a node for deep inspection') }}</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($fleetCards as $card)
                            @php $hs = $healthStyles($card['health']); @endphp
                            <button
                                type="button"
                                @click="selectServer({{ $card['id'] }})"
                                class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 text-left shadow-card transition hover:border-indigo-500/30 hover:shadow-card-hover dark:border-slate-800/80 dark:bg-slate-900/60 dark:hover:border-cyan-500/30"
                                :class="selectedId === {{ $card['id'] }} && 'ring-2 ring-cyan-500/50'"
                            >
                                <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-to-br from-cyan-500/10 to-indigo-500/5 blur-2xl"></div>
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $card['name'] }}</p>
                                        <p class="font-mono text-[11px] text-slate-500">{{ $card['ip'] }}</p>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $hs['badge'] }} ring-1 {{ $hs['ring'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $hs['dot'] }} {{ $card['health'] !== 'offline' ? 'animate-pulse' : '' }}"></span>
                                        {{ $hs['label'] }}
                                    </span>
                                </div>

                                <div class="mt-4 space-y-2">
                                    @foreach ([['CPU', $card['cpu'], 'from-indigo-500 to-violet-500'], ['RAM', $card['ram'], 'from-sky-500 to-cyan-500'], ['Disk', $card['disk'], 'from-amber-500 to-orange-500']] as [$label, $pct, $grad])
                                        <div>
                                            <div class="mb-0.5 flex justify-between text-[10px] text-slate-500">
                                                <span>{{ $label }}</span>
                                                <span class="tabular-nums font-semibold text-slate-700 dark:text-slate-200">{{ $pct !== null ? $pct.'%' : '—' }}</span>
                                            </div>
                                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                @if ($pct !== null)
                                                    <div class="h-full rounded-full bg-gradient-to-r {{ $grad }}" style="width: {{ min(100, $pct) }}%"></div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2 text-[11px]">
                                    <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/50">
                                        <span class="text-slate-500">{{ __('Uptime') }}</span>
                                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $card['uptime'] }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/50">
                                        <span class="text-slate-500">{{ __('Load') }}</span>
                                        <p class="font-semibold tabular-nums text-slate-800 dark:text-slate-100">{{ $card['load_avg'] ?? '—' }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/50">
                                        <span class="text-slate-500">{{ __('Tenants') }}</span>
                                        <p class="font-semibold tabular-nums text-slate-800 dark:text-slate-100">{{ $card['tenants'] }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/50">
                                        <span class="text-slate-500">{{ __('Projects') }}</span>
                                        <p class="font-semibold tabular-nums text-slate-800 dark:text-slate-100">{{ $card['projects'] }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 h-8 opacity-80">
                                    <x-ui.sparkline :points="$card['cpu_series']" stroke-class="stroke-cyan-500" fill-class="fill-cyan-500/10" />
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Alerts + Detail panel --}}
            <div class="space-y-5 lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/50">
                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">{{ __('Infrastructure alerts') }}</p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Incident feed') }}</h3>
                    </div>
                    <div class="max-h-[22rem] space-y-2 overflow-y-auto p-3">
                        @foreach ($alerts as $alert)
                            <div class="rounded-xl border px-3 py-2.5 {{ $alertRing($alert['severity']) }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-bold tracking-wider text-slate-600 dark:text-slate-300">{{ $alert['severity'] }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $alert['time'] }}</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400">{{ $alert['body'] }}</p>
                                <p class="mt-1 font-mono text-[10px] text-indigo-600 dark:text-indigo-400">{{ $alert['server'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Server detail slide panel --}}
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-900 to-slate-950 text-white shadow-2xl ring-1 ring-white/10 dark:border-slate-700"
                    x-show="selected"
                    x-cloak
                >
                    <div class="border-b border-white/10 px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-cyan-400">{{ __('Node inspector') }}</p>
                                <h3 class="text-base font-semibold" x-text="selected?.name"></h3>
                                <p class="font-mono text-xs text-slate-400" x-text="selected?.ip"></p>
                            </div>
                            <button type="button" @click="closePanel()" class="rounded-lg p-1 text-slate-400 hover:bg-white/10 hover:text-white">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="max-h-[28rem] space-y-4 overflow-y-auto p-4 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="m in [['CPU', selected?.cpu], ['RAM', selected?.ram], ['Disk', selected?.disk], ['Ping', selected?.ping_ms ? selected.ping_ms + 'ms' : '—']]" :key="m[0]">
                                <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10">
                                    <p class="text-[10px] uppercase text-slate-400" x-text="m[0]"></p>
                                    <p class="font-semibold tabular-nums" x-text="m[1] != null ? (typeof m[1] === 'number' ? m[1] + '%' : m[1]) : '—'"></p>
                                </div>
                            </template>
                        </div>

                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Running services') }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="svc in selected?.services ?? []" :key="svc.name">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium ring-1"
                                        :class="svc.status === 'running' ? 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30' : svc.status === 'degraded' ? 'bg-amber-500/15 text-amber-200 ring-amber-500/30' : 'bg-slate-500/15 text-slate-400 ring-slate-500/30'">
                                        <span class="h-1 w-1 rounded-full" :class="svc.status === 'running' ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
                                        <span x-text="svc.name"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Tenant allocation') }}</p>
                            <template x-if="!(selected?.tenants_list?.length)">
                                <p class="text-xs text-slate-500">{{ __('No tenants on this node.') }}</p>
                            </template>
                            <ul class="space-y-1">
                                <template x-for="t in selected?.tenants_list ?? []" :key="t">
                                    <li class="truncate rounded bg-white/5 px-2 py-1 text-xs" x-text="t"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-lg bg-white/5 p-2 ring-1 ring-white/10">
                                <span class="text-slate-400">{{ __('SSL') }}</span>
                                <p class="font-semibold capitalize" x-text="selected?.ssl_status"></p>
                            </div>
                            <div class="rounded-lg bg-white/5 p-2 ring-1 ring-white/10">
                                <span class="text-slate-400">{{ __('Backup') }}</span>
                                <p class="font-semibold capitalize" x-text="selected?.backup_status"></p>
                            </div>
                            <div class="rounded-lg bg-white/5 p-2 ring-1 ring-white/10">
                                <span class="text-slate-400">{{ __('Bandwidth IN') }}</span>
                                <p class="font-semibold tabular-nums" x-text="selected?.bandwidth_in + ' Mbps'"></p>
                            </div>
                            <div class="rounded-lg bg-white/5 p-2 ring-1 ring-white/10">
                                <span class="text-slate-400">{{ __('Bandwidth OUT') }}</span>
                                <p class="font-semibold tabular-nums" x-text="selected?.bandwidth_out + ' Mbps'"></p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Recent logs') }}</p>
                            <template x-for="log in selected?.logs ?? []" :key="log.message">
                                <div class="mb-1.5 rounded-lg border border-white/5 bg-black/20 px-2 py-1.5 text-xs">
                                    <span class="font-bold uppercase text-[10px]" :class="log.level === 'critical' ? 'text-rose-400' : log.level === 'warning' ? 'text-amber-400' : 'text-sky-400'" x-text="log.level"></span>
                                    <p class="text-slate-200" x-text="log.message"></p>
                                    <p class="text-[10px] text-slate-500" x-text="log.at"></p>
                                </div>
                            </template>
                        </div>

                        <a :href="selected?.show_url" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-lg">
                            {{ __('Open server record') }}
                        </a>
                    </div>
                </div>

                <div x-show="!selected" class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/50 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-900/30">
                    <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>
                    <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Select a server node') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Deep inspection: services, tenants, logs, SSL & backup status') }}</p>
                </div>
            </div>
        </div>

        {{-- Analytics --}}
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ __('Uptime history') }}</p>
                <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('7-day availability') }}</h3>
                <div class="mt-4 flex h-28 items-end gap-2">
                    @foreach ($uptimeHistory as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-gradient-to-t from-emerald-600 to-emerald-400 opacity-90" style="height: {{ ($day['pct'] / $uptimeMax) * 100 }}%"></div>
                            <span class="text-[10px] text-slate-500">{{ $day['label'] }}</span>
                            <span class="text-[10px] font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $day['pct'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">{{ __('Incident trends') }}</p>
                <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Events per day') }}</h3>
                <div class="mt-4 flex h-28 items-end gap-2">
                    @foreach ($incidentTrends as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-gradient-to-t from-rose-600 to-orange-400" style="height: {{ max(8, ($day['count'] / $incidentMax) * 100) }}%"></div>
                            <span class="text-[10px] text-slate-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Utilization trends') }}</p>
                <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('CPU · RAM · Disk') }}</h3>
                <div class="mt-3 space-y-3">
                    @foreach (['cpu' => ['CPU', 'indigo'], 'ram' => ['RAM', 'violet'], 'disk' => ['Disk', 'amber']] as $key => [$lbl, $tone])
                        @php
                            $series = $utilizationTrends[$key];
                            $avg = count($series) ? round(array_sum($series) / count($series), 1) : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex justify-between text-[11px]">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $lbl }}</span>
                                <span class="tabular-nums text-slate-500">{{ $avg }}% avg</span>
                            </div>
                            <div class="h-10 rounded-lg bg-slate-50 p-0.5 dark:bg-slate-800/50">
                                {!! $areaChart($series, "stroke-{$tone}-500", "fill-{$tone}-500/15", 200, 36) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
