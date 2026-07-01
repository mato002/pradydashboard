<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Redis & Queues'),'subheading' => __('Queue health, failed jobs, and worker guidance')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Redis & Queues')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Queue health, failed jobs, and worker guidance'))]); ?>
    <?php if(session('status')): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php
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
    ?>

    <div
        x-data="{
            lastRefresh: <?php echo \Illuminate\Support\Js::from($checkedAt->format('H:i:s'))->toHtml() ?>,
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
        
        <section class="relative overflow-hidden rounded-2xl border border-slate-800 bg-black shadow-xl">
            <div class="relative p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ring-1', $statusRing]); ?>">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'h-1.5 w-1.5 rounded-full',
                                    'bg-emerald-400 animate-pulse' => $overallStatus === 'healthy',
                                    'bg-amber-400 animate-pulse' => $overallStatus === 'degraded',
                                    'bg-rose-400 animate-pulse' => $overallStatus === 'critical',
                                ]); ?>"></span>
                                <?php echo e(strtoupper($overallStatus)); ?>

                            </span>
                            <span class="text-[10px] font-medium uppercase tracking-widest text-slate-500"><?php echo e(__('Queue operations')); ?></span>
                            <span class="font-mono text-[10px] text-slate-500" x-text="'<?php echo e(__('Updated')); ?> ' + lastRefresh"></span>
                        </div>
                        <h2 class="mt-2 truncate text-xl font-semibold text-white sm:text-2xl"><?php echo e($health['overall_label'] ?? __('Operational status')); ?></h2>
                        <p class="mt-1 text-sm text-slate-400"><?php echo e($health['overall_detail'] ?? ''); ?></p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="<?php echo e(route('monitoring.index')); ?>" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                            <?php echo e(__('Observability')); ?>

                        </a>
                        <?php if($snapshot['horizon_enabled'] ?? false): ?>
                            <a href="<?php echo e(url('/'.trim((string) ($snapshot['horizon_path'] ?? 'horizon'), '/'))); ?>" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                <?php echo e(__('Horizon')); ?>

                            </a>
                        <?php endif; ?>
                        <button type="button" @click="refresh()" class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                            <?php echo e(__('Refresh')); ?>

                        </button>
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-white/10 px-2.5 py-2 text-[11px] font-medium text-slate-300">
                            <input type="checkbox" x-model="autoRefresh" class="rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500">
                            <?php echo e(__('Auto 30s')); ?>

                        </label>
                    </div>
                </div>

                
                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-6">
                    <?php $__currentLoopData = [
                        ['label' => __('Ping'), 'value' => isset($redis['latency_ms']) ? $redis['latency_ms'].' ms' : '—', 'hint' => __('Redis round-trip')],
                        ['label' => __('Memory'), 'value' => isset($redis['memory_mb']) ? $redis['memory_mb'].' MB' : '—', 'hint' => __('Server usage')],
                        ['label' => __('Clients'), 'value' => isset($redis['connected_clients']) ? (string) $redis['connected_clients'] : '—', 'hint' => __('Active connections')],
                        ['label' => __('Cache keys'), 'value' => isset($redis['cache_keys']) ? number_format($redis['cache_keys']) : '—', 'hint' => __('DB :db', ['db' => config('database.redis.cache.database', 1)])],
                        ['label' => __('Worker'), 'value' => $worker['label'] ?? '—', 'hint' => $worker['detail'] ?? ''],
                        ['label' => __('Host'), 'value' => $redis['host'] ?? '—', 'hint' => $redis['client'] ?? 'redis'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-xl border border-white/5 bg-white/5 px-3 py-2.5 backdrop-blur-sm">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e($stat['label']); ?></p>
                            <p class="mt-0.5 truncate text-sm font-semibold text-white"><?php echo e($stat['value']); ?></p>
                            <p class="mt-0.5 truncate text-[10px] text-slate-500"><?php echo e($stat['hint']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php
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
            ?>

            <?php $__currentLoopData = $metricTiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
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
                ?>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex items-center gap-3 rounded-xl border bg-gradient-to-br p-4 shadow-sm', $accent]); ?>">
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex h-10 w-10 shrink-0 items-center justify-center rounded-lg', $iconBg]); ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($tile['icon']); ?>" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e($tile['title']); ?></p>
                        <p class="truncate text-lg font-semibold text-slate-900 dark:text-white"><?php echo e($tile['value']); ?></p>
                        <p class="truncate text-xs text-slate-500"><?php echo e($tile['sub']); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="grid gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 lg:col-span-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Redis stack')); ?></p>
                <div class="mt-3 space-y-2">
                    <?php $__currentLoopData = $infrastructure; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 dark:text-white"><?php echo e($row['label']); ?></p>
                                <p class="truncate text-[11px] text-slate-500"><?php echo e($row['detail'] ?? ''); ?></p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-md bg-slate-200/80 px-2 py-0.5 font-mono text-[10px] font-semibold uppercase text-slate-700 dark:bg-slate-700 dark:text-slate-200"><?php echo e($row['driver']); ?></span>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'h-2 w-2 rounded-full',
                                    'bg-emerald-500' => ($row['status'] ?? '') === 'ok',
                                    'bg-amber-500' => ($row['status'] ?? '') === 'warn',
                                    'bg-slate-400' => ($row['status'] ?? '') === 'neutral',
                                ]); ?>"></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-3 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Operational cache')); ?></p>
                    <p class="mt-0.5 text-xs font-medium text-emerald-300">
                        <?php echo e(($snapshot['operational_cache_enabled'] ?? false) ? __('Enabled — dashboard summaries cached in Redis') : __('Bypassed — live DB queries')); ?>

                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 lg:col-span-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Pending trend')); ?></p>
                        <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">
                            <?php if($queuesClear): ?>
                                <?php echo e(__('Flat line = idle baseline. Queues wake up when jobs are dispatched.')); ?>

                            <?php else: ?>
                                <?php echo e(__(':count jobs currently in flight across all queues.', ['count' => number_format($totalPending)])); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if(count($historyPoints) >= 2): ?>
                        <span class="shrink-0 rounded-full bg-cyan-500/10 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 dark:text-cyan-300"><?php echo e(count($historyPoints)); ?> <?php echo e(__('samples')); ?></span>
                    <?php endif; ?>
                </div>

                <div class="relative mt-4 h-28 w-full">
                    <?php if(count($historyPoints) >= 2): ?>
                        <?php
                            $w = 400;
                            $h = 100;
                            $linePts = collect($historyPoints)->values()->map(function ($v, $i) use ($historyPoints, $historyMax, $w, $h) {
                                $x = ($i / max(1, count($historyPoints) - 1)) * $w;
                                $y = $h - 8 - (($v / max(1, $historyMax)) * ($h - 16));

                                return round($x, 1).','.round($y, 1);
                            });
                            $line = $linePts->implode(' ');
                            $area = '0,'.$h.' '.$line.' '.$w.','.$h;
                        ?>
                        <svg class="h-full w-full" viewBox="0 0 <?php echo e($w); ?> <?php echo e($h); ?>" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="queueTrendFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="rgb(6,182,212)" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="rgb(6,182,212)" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <polygon points="<?php echo e($area); ?>" fill="url(#queueTrendFill)" />
                            <polyline points="<?php echo e($line); ?>" fill="none" stroke="rgb(6,182,212)" stroke-width="2.5" vector-effect="non-scaling-stroke" />
                        </svg>
                    <?php else: ?>
                        <div class="flex h-full flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 dark:border-slate-700">
                            <svg class="h-full w-full opacity-40" viewBox="0 0 400 100" preserveAspectRatio="none" aria-hidden="true">
                                <line x1="0" y1="85" x2="400" y2="85" stroke="rgb(6,182,212)" stroke-width="1.5" stroke-dasharray="6 4" />
                            </svg>
                            <p class="absolute text-xs text-slate-500"><?php echo e(__('Refresh a few times to build history')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="grid gap-5 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <?php if($queuesClear): ?>
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-3 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100"><?php echo e(__('All queues idle — nothing waiting')); ?></p>
                            <p class="text-xs text-emerald-800/80 dark:text-emerald-200/80"><?php echo e(__(':ready of :total queues ready. Redis is connected — workers pick up jobs instantly when dispatched.', ['ready' => $idleQueues, 'total' => count($queues)])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Queue breakdown')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Queue breakdown'))]); ?>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                            <tr>
                                <th class="px-4 py-3"><?php echo e(__('Queue')); ?></th>
                                <th class="hidden px-4 py-3 md:table-cell"><?php echo e(__('Purpose')); ?></th>
                                <th class="px-4 py-3"><?php echo e(__('Priority')); ?></th>
                                <th class="px-4 py-3"><?php echo e(__('Status')); ?></th>
                                <th class="px-4 py-3 text-right"><?php echo e(__('Pending')); ?></th>
                                <th class="hidden px-4 py-3 lg:table-cell"><?php echo e(__('Load')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $qStatus = $queue['status'] ?? 'idle';
                                    $isIdle = $queue['is_idle'] ?? (($queue['pending'] ?? 0) === 0);
                                ?>
                                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30',
                                    'bg-amber-50/50 dark:bg-amber-950/10' => $queue['is_high_pending'] ?? false,
                                ]); ?>">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <?php if($isIdle): ?>
                                                <span class="relative flex h-1.5 w-1.5">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                                                    <span class="relative h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                </span>
                                            <?php endif; ?>
                                            <span class="font-mono text-xs font-semibold text-slate-900 dark:text-white"><?php echo e($queue['queue']); ?></span>
                                        </div>
                                    </td>
                                    <td class="hidden max-w-[12rem] truncate px-4 py-3 text-xs text-slate-500 md:table-cell"><?php echo e($queue['label']); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => match ($queue['priority'] ?? 'normal') {
                                            'critical' => 'danger',
                                            'high' => 'warning',
                                            'low' => 'neutral',
                                            default => 'info',
                                        }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($queue['priority'] ?? 'normal') {
                                            'critical' => 'danger',
                                            'high' => 'warning',
                                            'low' => 'neutral',
                                            default => 'info',
                                        })]); ?><?php echo e($queue['priority_label'] ?? __('Normal')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($qStatus)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($qStatus))]); ?><?php echo e($queue['status_label'] ?? __('Idle')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <?php if($isIdle): ?>
                                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"><?php echo e(__('Ready')); ?></span>
                                        <?php else: ?>
                                            <span class="font-mono text-sm font-semibold tabular-nums text-slate-900 dark:text-white"><?php echo e(number_format($queue['pending'] ?? 0)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="hidden px-4 py-3 lg:table-cell">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'h-full rounded-full',
                                                    'bg-emerald-500' => $isIdle,
                                                    'bg-cyan-500' => ! $isIdle && ! ($queue['is_high_pending'] ?? false),
                                                    'bg-amber-500' => $queue['is_high_pending'] ?? false,
                                                ]); ?>" style="width: <?php echo e(max(6, $queue['load_pct'] ?? 0)); ?>%"></div>
                                            </div>
                                            <span class="text-[10px] tabular-nums text-slate-500"><?php echo e($queue['load_pct'] ?? 0); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>

                     <?php $__env->slot('footer', null, []); ?> 
                        <button type="button" @click="showCommands = !showCommands" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            <span x-text="showCommands ? '<?php echo e(__('Hide worker commands')); ?>' : '<?php echo e(__('Show worker commands')); ?>'"></span>
                        </button>
                        <div x-show="showCommands" x-cloak class="mt-3 space-y-2">
                            <?php $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                                    <p class="font-mono text-[10px] font-semibold uppercase text-slate-500"><?php echo e($queue['queue']); ?></p>
                                    <code class="mt-1 block break-all font-mono text-[11px] leading-relaxed text-emerald-300"><?php echo e($queue['worker_command']); ?></code>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                     <?php $__env->endSlot(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $attributes = $__attributesOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__attributesOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $component = $__componentOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__componentOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
            </div>

            
            <aside class="xl:col-span-4">
                <div class="sticky top-4 space-y-4">
                    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400"><?php echo e(__('Live pipeline')); ?></p>
                        <h3 class="mt-1 text-base font-semibold text-slate-900 dark:text-white"><?php echo e(__('Recent async activity')); ?></h3>

                        <?php if(count($liveness['recent_activity'] ?? []) > 0): ?>
                            <ul class="mt-3 max-h-80 space-y-2 overflow-y-auto prady-scrollbar">
                                <?php $__currentLoopData = $liveness['recent_activity']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2 dark:border-slate-800 dark:bg-slate-800/30">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="truncate font-mono text-[10px] font-semibold uppercase text-indigo-600 dark:text-indigo-400"><?php echo e($activity['action']); ?></span>
                                            <span class="shrink-0 text-[10px] text-slate-500"><?php echo e($activity['ago']); ?></span>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-slate-300"><?php echo e($activity['message']); ?></p>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <div class="mt-4 rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center dark:border-slate-700">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300"><?php echo e(__('No pipeline events in the last 24h')); ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Run billing, send an invoice, or record a payment to populate this feed.')); ?></p>
                                <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                                    <code class="rounded bg-slate-100 px-2 py-0.5 text-[10px] dark:bg-slate-800">billing:process-recurring</code>
                                    <code class="rounded bg-slate-100 px-2 py-0.5 text-[10px] dark:bg-slate-800">redis:health</code>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-400"><?php echo e(__('Operational guidance')); ?></p>
                        <h3 class="mt-1 text-base font-semibold text-slate-900 dark:text-white"><?php echo e(__('How to run workers')); ?></h3>
                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Local Windows')); ?></p>
                        <div class="mt-1.5 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                            <code class="block break-all font-mono text-[10px] leading-relaxed text-emerald-300"><?php echo e($guidance['local_windows'] ?? ''); ?></code>
                        </div>
                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Production Linux')); ?></p>
                        <div class="mt-1.5 rounded-lg bg-slate-950 px-3 py-2 dark:ring-1 dark:ring-white/10">
                            <code class="block break-all font-mono text-[10px] leading-relaxed text-sky-300"><?php echo e($guidance['production_linux'] ?? ''); ?></code>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Retry center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Retry center'))]); ?>
            <?php if($failedJobs->isEmpty()): ?>
                <div class="flex items-center gap-4 px-5 py-8">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100"><?php echo e(__('No failed jobs — retry center is clean')); ?></p>
                        <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('When a job fails, it appears here with retry and forget actions.')); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                        <tr>
                            <th class="px-4 py-3"><?php echo e(__('Job')); ?></th>
                            <th class="px-4 py-3"><?php echo e(__('Queue')); ?></th>
                            <th class="px-4 py-3"><?php echo e(__('Error')); ?></th>
                            <th class="px-4 py-3"><?php echo e(__('When')); ?></th>
                            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.sync')): ?>
                                <th class="px-4 py-3 text-right"><?php echo e(__('Actions')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__currentLoopData = $failedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/30" x-data="{ open: false, details: null }">
                                <td class="px-4 py-3">
                                    <p class="font-mono text-xs text-slate-500">#<?php echo e($job->id); ?></p>
                                    <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($job->job_name); ?></p>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger']); ?><?php echo e($job->queue); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                </td>
                                <td class="max-w-md px-4 py-3">
                                    <p class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($job->exception_summary); ?></p>
                                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.sync')): ?>
                                        <button type="button" @click="open = !open; if (open && !details) { fetch(<?php echo \Illuminate\Support\Js::from(route('monitoring.failed-jobs.details', $job->uuid))->toHtml() ?>).then(r => r.json()).then(data => { details = data.exception; }).catch(() => { details = <?php echo \Illuminate\Support\Js::from(__('Unable to load technical details.'))->toHtml() ?>; }); }" class="mt-1 text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                            <span x-text="open ? '<?php echo e(__('Hide details')); ?>' : '<?php echo e(__('View details')); ?>'"></span>
                                        </button>
                                        <pre x-show="open" x-cloak class="mt-2 max-h-36 overflow-auto rounded-lg bg-slate-950 p-2 text-[10px] leading-relaxed text-slate-300" x-text="details || '<?php echo e(__('Loading…')); ?>'"></pre>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500"><?php echo e($job->failed_at_human); ?></td>
                                <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.sync')): ?>
                                    <td class="px-4 py-3 text-right" @click.stop>
                                        <?php if (isset($component)) { $__componentOriginal110b8ff0bc0114fb450fefaa85301d27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-actions-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-actions-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                            <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('monitoring.failed-jobs.retry', $job->uuid),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.failed-jobs.retry', $job->uuid)),'method' => 'POST']); ?><?php echo e(__('Retry')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                            <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('monitoring.failed-jobs.forget', $job->uuid),'method' => 'DELETE','confirm' => __('Remove this failed job record?'),'danger' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.failed-jobs.forget', $job->uuid)),'method' => 'DELETE','confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Remove this failed job record?')),'danger' => true]); ?><?php echo e(__('Forget')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $attributes = $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $component = $__componentOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $attributes = $__attributesOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__attributesOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $component = $__componentOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__componentOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $attributes = $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $component = $__componentOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/monitoring/queues.blade.php ENDPATH**/ ?>