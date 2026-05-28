<x-dashboard-layout :heading="__('Redis & Queues')" :subheading="__('Queue health, failed jobs, and worker guidance')">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    @php
        $health = $snapshot['health'] ?? [];
        $redis = $snapshot['redis'] ?? [];
        $horizon = $snapshot['horizon'] ?? [];
        $guidance = $snapshot['guidance'] ?? [];
        $queues = $snapshot['queues'] ?? [];
        $failedJobs = $snapshot['recent_failed_jobs'] ?? collect();
        $checkedAt = $snapshot['checked_at'] ?? now();
        $liveness = $snapshot['liveness'] ?? [];
        $pendingHistory = $snapshot['pending_history'] ?? [];
        $overallStatus = $health['overall_status'] ?? 'healthy';
        $worker = $liveness['worker'] ?? [];
        $infrastructure = $liveness['infrastructure'] ?? [];
        $queuesClear = $health['queues_clear'] ?? false;
        $totalPending = (int) ($health['total_pending'] ?? 0);
        $failedCount = (int) ($health['failed_jobs_count'] ?? 0);

        $statusVariant = fn (string $status): string => match ($status) {
            'connected', 'running', 'idle', 'healthy', 'ok' => 'success',
            'active' => 'info',
            'stopped', 'backlog', 'degraded', 'warn' => 'warning',
            'critical', 'unavailable' => 'danger',
            default => 'neutral',
        };

        $statusRing = match ($overallStatus) {
            'critical' => 'ring-rose-500/40 bg-rose-500/10 text-rose-200',
            'degraded' => 'ring-amber-500/40 bg-amber-500/10 text-amber-200',
            default => 'ring-emerald-500/40 bg-emerald-500/10 text-emerald-200',
        };

        $historyPoints = collect($pendingHistory)->pluck('pending')->map(fn ($v) => (int) $v)->all();
        $historyMax = max($historyPoints ?: [0, 1]);
        $idleQueues = collect($queues)->where('is_idle', true)->count();
        $activeQueues = count($queues) - $idleQueues;
    @endphp

    <div
        x-data="{
            lastRefresh: @js($checkedAt->format('H:i:s')),
            autoRefresh: true,
            showCommands: false,
            refresh() { window.location.reload(); },
            init() {
                setInterval(() => {
                    if (this.autoRefresh) this.refresh();
                }, 30000);
            }
        }"
        class="space-y-5"
    >
        {{-- Status header --}}
        <section class="relative overflow-hidden rounded-2xl border border-slate-800 bg-black shadow-xl">
            <div class="relative p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ring-1', $statusRing])>
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    'bg-emerald-400 animate-pulse' => $overallStatus === 'healthy',
                                    'bg-amber-400 animate-pulse' => $overallStatus === 'degraded',
                                    'bg-rose-400 animate-pulse' => $overallStatus === 'critical',
                                ])></span>
                                {{ strtoupper($overallStatus) }}
                            </span>
                            <span class="text-[10px] font-medium uppercase tracking-widest text-slate-500">{{ __('Queue operations') }}</span>
                            <span class="font-mono text-[10px] text-slate-500" x-text="'{{ __('Updated') }} ' + lastRefresh"></span>
                        </div>
                        <h2 class="mt-2 truncate text-xl font-semibold text-white sm:text-2xl">{{ $health['overall_label'] ?? __('Operational status') }}</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $health['overall_detail'] ?? '' }}</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('monitoring.index') }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                            {{ __('Observability') }}
                        </a>
                        @if ($snapshot['horizon_enabled'] ?? false)
                            <a href="{{ url('/'.trim((string) ($snapshot['horizon_path'] ?? 'horizon'), '/')) }}" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                {{ __('Horizon') }}
                            </a>
                        @endif
                        <button type="button" @click="refresh()" class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                            {{ __('Refresh') }}
                        </button>
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-white/10 px-2.5 py-2 text-[11px] font-medium text-slate-300">
                            <input type="checkbox" x-model="autoRefresh" class="rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500">
                            {{ __('Auto 30s') }}
                        </label>
                    </div>
                </div>

                {{-- Inline telemetry strip --}}
                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ([
                        ['label' => __('Ping'), 'value' => isset($redis['latency_ms']) ? $redis['latency_ms'].' ms' : '—', 'hint' => __('Redis round-trip')],
                        ['label' => __('Memory'), 'value' => isset($redis['memory_mb']) ? $redis['memory_mb'].' MB' : '—', 'hint' => __('Server usage')],
                        ['label' => __('Clients'), 'value' => isset($redis['connected_clients']) ? (string) $redis['connected_clients'] : '—', 'hint' => __('Active connections')],
                        ['label' => __('Cache keys'), 'value' => isset($redis['cache_keys']) ? number_format($redis['cache_keys']) : '—', 'hint' => __('DB :db', ['db' => config('database.redis.cache.database', 1)])],
                        ['label' => __('Worker'), 'value' => $worker['label'] ?? '—', 'hint' => $worker['detail'] ?? ''],
                        ['label' => __('Host'), 'value' => $redis['host'] ?? '—', 'hint' => $redis['client'] ?? 'redis'],
                    ] as $stat)
                        <div class="rounded-xl border border-white/5 bg-white/5 px-3 py-2.5 backdrop-blur-sm">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-0.5 truncate text-sm font-semibold text-white">{{ $stat['value'] }}</p>
                            <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $stat['hint'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Primary metrics --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $metricTiles = [
                    [
                        'title' => __('Redis'),
                        'value' => $redis['label'] ?? __('Unknown'),
                        'sub' => isset($redis['latency_ms']) ? $redis['latency_ms'].' ms · '.$redis['host'] : ($redis['host'] ?? ''),
                        'tone' => ($redis['available'] ?? false) ? 'emerald' : 'rose',
                        'icon' => 'M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 0 1 3.75 12V5.25a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21.75 12v6.75A2.25 2.25 0 0 1 19.5 21H5.25Z',
                    ],
                    [
                        'title' => __('Worker'),
                        'value' => $worker['label'] ?? __('Not configured'),
                        'sub' => $worker['detail'] ?? ($horizon['detail'] ?? ''),
                        'tone' => match ($worker['variant'] ?? 'neutral') {
                            'success' => 'emerald',
                            'warning' => 'amber',
                            default => 'sky',
                        },
                        'icon' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z',
                    ],
                    [
                        'title' => __('In flight'),
                        'value' => $queuesClear ? __('Idle') : number_format($totalPending),
                        'sub' => $queuesClear ? __('Queues are clear') : __(':count queue(s) with pending jobs', ['count' => $activeQueues]),
                        'tone' => $queuesClear ? 'emerald' : 'amber',
                        'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
                    ],
                    [
                        'title' => __('Failed'),
                        'value' => (string) $failedCount,
                        'sub' => $failedCount > 0 ? __('Review retry center below') : __('No failed jobs'),
                        'tone' => $failedCount > 0 ? 'rose' : 'emerald',
                        'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
                    ],
                ];
            @endphp

            @foreach ($metricTiles as $tile)
                @php
                    $accent = match ($tile['tone']) {
                        'emerald' => 'border-emerald-500/20 from-emerald-500/5 to-white dark:to-slate-900/60',
                        'amber' => 'border-amber-500/20 from-amber-500/5 to-white dark:to-slate-900/60',
                        'rose' => 'border-rose-500/20 from-rose-500/5 to-white dark:to-slate-900/60',
                        default => 'border-sky-500/20 from-sky-500/5 to-white dark:to-slate-900/60',
                    };
                    $iconBg = match ($tile['tone']) {
                        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                        'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                        'rose' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                        default => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                    };
                @endphp
                <div @class(['flex items-center gap-3 rounded-xl border bg-gradient-to-br p-4 shadow-sm', $accent])>
                    <div @class(['flex h-10 w-10 shrink-0 items-center justify-center rounded-lg', $iconBg])>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $tile['title'] }}</p>
                        <p class="truncate text-lg font-semibold text-slate-900 dark:text-white">{{ $tile['value'] }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $tile['sub'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Infrastructure + trend --}}
        <div class="grid gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 lg:col-span-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Redis stack') }}</p>
                <div class="mt-3 space-y-2">
                    @foreach ($infrastructure as $row)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $row['label'] }}</p>
                                <p class="truncate text-[11px] text-slate-500">{{ $row['detail'] ?? '' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-md bg-slate-200/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $row['driver'] }}</span>
                                <span @class([
                                    'h-2 w-2 rounded-full',
                                    'bg-emerald-500' => ($row['status'] ?? '') === 'ok',
                                    'bg-amber-500' => ($row['status'] ?? '') === 'warn',
                                    'bg-slate-400' => ($row['status'] ?? '') === 'neutral',
                                ])></span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Operational cache') }}</p>
                    <p class="mt-0.5 text-xs font-medium text-emerald-300">
                        {{ ($snapshot['operational_cache_enabled'] ?? false) ? __('Enabled — dashboard summaries cached in Redis') : __('Bypassed — live DB queries') }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 lg:col-span-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Pending trend') }}</p>
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">
                            @if ($queuesClear)
                                {{ __('Flat line = idle baseline. Queues wake up when jobs are dispatched.') }}
                            @else
                                {{ __(':count jobs currently in flight across all queues.', ['count' => number_format($totalPending)]) }}
                            @endif
                        </p>
                    </div>
                    @if (count($historyPoints) >= 2)
                        <span class="shrink-0 rounded-full bg-cyan-500/10 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 dark:text-cyan-300">{{ count($historyPoints) }} {{ __('samples') }}</span>
                    @endif
                </div>

                <div class="relative mt-4 h-28 w-full">
                    @if (count($historyPoints) >= 2)
                        @php
                            $w = 400;
                            $h = 100;
                            $linePts = collect($historyPoints)->values()->map(function ($v, $i) use ($historyPoints, $historyMax, $w, $h) {
                                $x = ($i / max(1, count($historyPoints) - 1)) * $w;
                                $y = $h - 8 - (($v / max(1, $historyMax)) * ($h - 16));

                                return round($x, 1).','.round($y, 1);
                            });
                            $line = $linePts->implode(' ');
                            $area = '0,'.$h.' '.$line.' '.$w.','.$h;
                        @endphp
                        <svg class="h-full w-full" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="queueTrendFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="rgb(6,182,212)" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="rgb(6,182,212)" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <polygon points="{{ $area }}" fill="url(#queueTrendFill)" />
                            <polyline points="{{ $line }}" fill="none" stroke="rgb(6,182,212)" stroke-width="2.5" vector-effect="non-scaling-stroke" />
                        </svg>
                    @else
                        <div class="flex h-full flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 dark:border-slate-700">
                            <svg class="h-full w-full opacity-40" viewBox="0 0 400 100" preserveAspectRatio="none" aria-hidden="true">
                                <line x1="0" y1="85" x2="400" y2="85" stroke="rgb(6,182,212)" stroke-width="1.5" stroke-dasharray="6 4" />
                            </svg>
                            <p class="absolute text-xs text-slate-500">{{ __('Refresh a few times to build history') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="grid gap-5 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                @if ($queuesClear)
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-3 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ __('All queues idle — nothing waiting') }}</p>
                            <p class="text-xs text-emerald-800/80 dark:text-emerald-200/80">{{ __(':ready of :total queues ready. Redis is connected — workers pick up jobs instantly when dispatched.', ['ready' => $idleQueues, 'total' => count($queues)]) }}</p>
                        </div>
                    </div>
                @endif

                <x-ui.table-panel :title="__('Queue breakdown')">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                            <tr>
                                <th class="px-4 py-3">{{ __('Queue') }}</th>
                                <th class="hidden px-4 py-3 md:table-cell">{{ __('Purpose') }}</th>
                                <th class="px-4 py-3">{{ __('Priority') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Pending') }}</th>
                                <th class="hidden px-4 py-3 lg:table-cell">{{ __('Load') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($queues as $queue)
                                @php
                                    $qStatus = $queue['status'] ?? 'idle';
                                    $isIdle = $queue['is_idle'] ?? (($queue['pending'] ?? 0) === 0);
                                @endphp
                                <tr @class([
                                    'transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30',
                                    'bg-amber-50/50 dark:bg-amber-950/10' => $queue['is_high_pending'] ?? false,
                                ])>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if ($isIdle)
                                                <span class="relative flex h-1.5 w-1.5">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                                                    <span class="relative h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                </span>
                                            @endif
                                            <span class="font-mono text-xs font-semibold text-slate-900 dark:text-white">{{ $queue['queue'] }}</span>
                                        </div>
                                    </td>
                                    <td class="hidden max-w-[12rem] truncate px-4 py-3 text-xs text-slate-500 md:table-cell">{{ $queue['label'] }}</td>
                                    <td class="px-4 py-3">
                                        <x-ui.status-badge :variant="match ($queue['priority'] ?? 'normal') {
                                            'critical' => 'danger',
                                            'high' => 'warning',
                                            'low' => 'neutral',
                                            default => 'info',
                                        }">{{ $queue['priority_label'] ?? __('Normal') }}</x-ui.status-badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-ui.status-badge :variant="$statusVariant($qStatus)">{{ $queue['status_label'] ?? __('Idle') }}</x-ui.status-badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($isIdle)
                                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Ready') }}</span>
                                        @else
                                            <span class="font-mono text-sm font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($queue['pending'] ?? 0) }}</span>
                                        @endif
                                    </td>
                                    <td class="hidden px-4 py-3 lg:table-cell">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                <div @class([
                                                    'h-full rounded-full',
                                                    'bg-emerald-500' => $isIdle,
                                                    'bg-cyan-500' => ! $isIdle && ! ($queue['is_high_pending'] ?? false),
                                                    'bg-amber-500' => $queue['is_high_pending'] ?? false,
                                                ]) style="width: {{ max(6, $queue['load_pct'] ?? 0) }}%"></div>
                                            </div>
                                            <span class="text-[10px] tabular-nums text-slate-500">{{ $queue['load_pct'] ?? 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <x-slot name="footer">
                        <button type="button" @click="showCommands = !showCommands" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            <span x-text="showCommands ? '{{ __('Hide worker commands') }}' : '{{ __('Show worker commands') }}'"></span>
                        </button>
                        <div x-show="showCommands" x-cloak class="mt-3 space-y-2">
                            @foreach ($queues as $queue)
                                <div class="rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                                    <p class="font-mono text-[10px] font-semibold uppercase text-slate-500">{{ $queue['queue'] }}</p>
                                    <code class="mt-1 block break-all font-mono text-[11px] leading-relaxed text-emerald-300">{{ $queue['worker_command'] }}</code>
                                </div>
                            @endforeach
                        </div>
                    </x-slot>
                </x-ui.table-panel>
            </div>

            {{-- Pipeline sidebar --}}
            <aside class="xl:col-span-4">
                <div class="sticky top-4 space-y-4">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">{{ __('Live pipeline') }}</p>
                        <h3 class="mt-1 text-base font-semibold text-slate-900 dark:text-white">{{ __('Recent async activity') }}</h3>

                        @if (count($liveness['recent_activity'] ?? []) > 0)
                            <ul class="mt-3 max-h-80 space-y-2 overflow-y-auto prady-scrollbar">
                                @foreach ($liveness['recent_activity'] as $activity)
                                    <li class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2 dark:border-slate-800 dark:bg-slate-800/30">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="truncate font-mono text-[10px] font-semibold uppercase text-indigo-600 dark:text-indigo-400">{{ $activity['action'] }}</span>
                                            <span class="shrink-0 text-[10px] text-slate-500">{{ $activity['ago'] }}</span>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-slate-300">{{ $activity['message'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="mt-4 rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center dark:border-slate-700">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('No pipeline events in the last 24h') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('Run billing, send an invoice, or record a payment to populate this feed.') }}</p>
                                <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                                    <code class="rounded bg-slate-100 px-2 py-0.5 text-[10px] dark:bg-slate-800">billing:process-recurring</code>
                                    <code class="rounded bg-slate-100 px-2 py-0.5 text-[10px] dark:bg-slate-800">redis:health</code>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">{{ __('Operational guidance') }}</p>
                        <h3 class="mt-1 text-base font-semibold text-slate-900 dark:text-white">{{ __('How to run workers') }}</h3>
                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Local Windows') }}</p>
                        <div class="mt-1.5 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                            <code class="block break-all font-mono text-[10px] leading-relaxed text-emerald-300">{{ $guidance['local_windows'] ?? '' }}</code>
                        </div>
                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Production Linux') }}</p>
                        <div class="mt-1.5 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                            <code class="block break-all font-mono text-[10px] leading-relaxed text-sky-300">{{ $guidance['production_linux'] ?? '' }}</code>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Failed jobs --}}
        <x-ui.table-panel :title="__('Retry center')">
            @if ($failedJobs->isEmpty())
                <div class="flex items-center gap-4 px-5 py-8">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ __('No failed jobs — retry center is clean') }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('When a job fails, it appears here with retry and forget actions.') }}</p>
                    </div>
                </div>
            @else
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                        <tr>
                            <th class="px-4 py-3">{{ __('Job') }}</th>
                            <th class="px-4 py-3">{{ __('Queue') }}</th>
                            <th class="px-4 py-3">{{ __('Error') }}</th>
                            <th class="px-4 py-3">{{ __('When') }}</th>
                            @permission('monitoring.sync')
                                <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                            @endpermission
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($failedJobs as $job)
                            <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/30" x-data="{ open: false, details: null }">
                                <td class="px-4 py-3">
                                    <p class="font-mono text-xs text-slate-500">#{{ $job->id }}</p>
                                    <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $job->job_name }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.status-badge variant="danger">{{ $job->queue }}</x-ui.status-badge>
                                </td>
                                <td class="max-w-md px-4 py-3">
                                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $job->exception_summary }}</p>
                                    @permission('monitoring.sync')
                                        <button type="button" @click="open = !open; if (open && !details) { fetch(@js(route('monitoring.failed-jobs.details', $job->uuid))).then(r => r.json()).then(data => { details = data.exception; }).catch(() => { details = @js(__('Unable to load technical details.')); }); }" class="mt-1 text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                            <span x-text="open ? '{{ __('Hide details') }}' : '{{ __('View details') }}'"></span>
                                        </button>
                                        <pre x-show="open" x-cloak class="mt-2 max-h-36 overflow-auto rounded-lg bg-slate-950 p-2 text-[10px] leading-relaxed text-slate-300" x-text="details || '{{ __('Loading…') }}'"></pre>
                                    @endpermission
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ $job->failed_at_human }}</td>
                                @permission('monitoring.sync')
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <form method="post" action="{{ route('monitoring.failed-jobs.retry', $job->uuid) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Retry') }}</button>
                                            </form>
                                            <form method="post" action="{{ route('monitoring.failed-jobs.forget', $job->uuid) }}" onsubmit="return confirm(@js(__('Remove this failed job record?')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-900/50 dark:text-rose-300">{{ __('Forget') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                @endpermission
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-ui.table-panel>
    </div>
</x-dashboard-layout>
