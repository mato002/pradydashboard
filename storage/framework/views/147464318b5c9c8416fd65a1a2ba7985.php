<?php
    $alertRing = fn (string $s): string => match ($s) {
        'critical' => 'ring-rose-500/40 bg-rose-500/10 border-rose-500/25',
        'warning' => 'ring-amber-500/35 bg-amber-500/10 border-amber-500/25',
        'info' => 'ring-sky-500/30 bg-sky-500/10 border-sky-500/20',
        default => 'ring-slate-500/25 bg-slate-500/5 border-slate-500/20',
    };

    $statusDot = fn (string $s): string => match ($s) {
        'healthy', 'pass', 'alive', 'ok' => 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]',
        'degraded', 'warning', 'slow' => 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.7)]',
        'critical', 'error', 'dead', 'failed' => 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]',
        default => 'bg-slate-400',
    };

    $uptimeMax = 100;
    $latencyMax = max(collect($latencySeries)->max('p99') ?? 0, 1);
    $errorMax = max(collect($errorRateSeries)->max('rate') ?? 0, 0.5);

    $areaChart = function (array $points, string $stroke, string $fill, int $w = 280, int $h = 72): string {
        $pts = collect($points)->values()->map(fn ($v) => is_array($v) ? (float) ($v['pct'] ?? $v['rate'] ?? 0) : (float) $v)->all();
        if (count($pts) < 2) {
            return '';
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
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Monitoring'),'subheading' => __('Enterprise observability, alerting & incident response')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitoring')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enterprise observability, alerting & incident response'))]); ?>
    <div
        x-data="observabilityCenter(<?php echo \Illuminate\Support\Js::from($alerts->values()->all())->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($escalationPolicies)->toHtml() ?>)"
        class="space-y-6"
    >
        
        <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6 shadow-2xl ring-1 ring-white/5">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(6,182,212,0.12),_transparent_50%)]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.9)]"></span>
                        </span>
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400"><?php echo e(__('Live observability')); ?></p>
                        <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-500/30"><?php echo e(__('NOC ACTIVE')); ?></span>
                        <span class="font-mono text-[10px] text-slate-500" x-text="lastRefresh"></span>
                    </div>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white"><?php echo e(__('Observability & Alerting Center')); ?></h2>
                    <p class="mt-1 max-w-2xl text-sm text-slate-400">
                        <?php echo e(__('Uptime, APM, API health, deployments, tenants, infrastructure — enterprise incident response in one command center.')); ?>

                    </p>
                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-400">
                        <span><?php echo e(__(':servers nodes', ['servers' => $fleetSummary['servers']])); ?></span>
                        <span class="text-slate-600">·</span>
                        <span><?php echo e(__(':tenants tenants', ['tenants' => $fleetSummary['tenants']])); ?></span>
                        <span class="text-slate-600">·</span>
                        <span><?php echo e(__(':projects apps', ['projects' => $fleetSummary['projects']])); ?></span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e(route('monitoring.queues')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 backdrop-blur transition hover:bg-white/10">
                        <?php echo e(__('Redis & Queues')); ?>

                    </a>
                    <a href="<?php echo e(route('server-health.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 backdrop-blur transition hover:bg-white/10">
                        <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>
                        <?php echo e(__('Infrastructure')); ?>

                    </a>
                    <a href="<?php echo e(route('activity-logs.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 backdrop-blur transition hover:bg-white/10">
                        <?php echo e(__('Audit trail')); ?>

                    </a>
                    <button type="button" @click="refresh()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 via-cyan-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:brightness-110">
                        <svg class="h-4 w-4" :class="refreshing && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                        <?php echo e(__('Refresh telemetry')); ?>

                    </button>
                </div>
            </div>
        </div>

        <?php if (! ($hasLiveTelemetry ?? false)): ?>
            <div class="rounded-xl border border-amber-200/80 bg-amber-50/80 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100">
                <?php echo e(__('No live metrics available yet. Run server telemetry sync or configure WHM API tokens for live data.')); ?>

            </div>
        <?php endif; ?>

        
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('System Uptime'),'value' => $kpis['system_uptime'] !== null ? $kpis['system_uptime'].'%' : __('Pending sync'),'animate' => false,'sublabel' => __('Synced fleet reachability'),'points' => $spark('mon-uptime'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System Uptime')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['system_uptime'] !== null ? $kpis['system_uptime'].'%' : __('Pending sync')),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Synced fleet reachability')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mon-uptime')),'tone' => 'emerald']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active Alerts'),'value' => $kpis['active_alerts'],'trend' => $kpis['active_alerts'] > 0 ? '!' : '✓','sublabel' => __('Open incidents'),'points' => $spark('mon-alerts'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active Alerts')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_alerts']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_alerts'] > 0 ? '!' : '✓'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Open incidents')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mon-alerts')),'tone' => 'rose']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.75a9 9 0 1118 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Error Rate'),'value' => $kpis['error_rate'] !== null ? $kpis['error_rate'].'%' : __('Unknown'),'animate' => false,'sublabel' => __('5xx + timeouts — not instrumented'),'points' => [],'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Error Rate')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['error_rate'] !== null ? $kpis['error_rate'].'%' : __('Unknown')),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('5xx + timeouts — not instrumented')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([]),'tone' => 'amber']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Avg Response Time'),'value' => $kpis['avg_response_ms'] !== null ? $kpis['avg_response_ms'].'ms' : __('Unknown'),'animate' => false,'sublabel' => __('From synced server load'),'points' => $spark('mon-latency'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Avg Response Time')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_response_ms'] !== null ? $kpis['avg_response_ms'].'ms' : __('Unknown')),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('From synced server load')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mon-latency')),'tone' => 'sky']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('API Availability'),'value' => $kpis['api_availability'] !== null ? $kpis['api_availability'].'%' : __('Not configured'),'animate' => false,'sublabel' => __('Public endpoints — not instrumented'),'points' => $spark('mon-api'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('API Availability')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['api_availability'] !== null ? $kpis['api_availability'].'%' : __('Not configured')),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Public endpoints — not instrumented')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mon-api')),'tone' => 'indigo']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Critical Incidents'),'value' => $kpis['critical_incidents'],'trend' => $kpis['critical_incidents'] > 0 ? 'P1' : '—','sublabel' => __('Requires response'),'points' => $spark('mon-incidents'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Critical Incidents')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents'] > 0 ? 'P1' : '—'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Requires response')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mon-incidents')),'tone' => 'violet']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg> <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $attributes = $__attributesOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__attributesOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6f65973f5a6918a6180e1799325c972)): ?>
<?php $component = $__componentOriginalb6f65973f5a6918a6180e1799325c972; ?>
<?php unset($__componentOriginalb6f65973f5a6918a6180e1799325c972); ?>
<?php endif; ?>
        </div>

        
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="space-y-5 lg:col-span-8">
                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/50">
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400"><?php echo e(__('Monitoring dashboard')); ?></p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Live telemetry (24h)')); ?></h3>
                    </div>
                    <div class="grid gap-4 p-5 lg:grid-cols-2">
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('System uptime')); ?></p>
                            <div class="h-20 rounded-xl bg-gradient-to-b from-emerald-500/5 to-transparent p-1 ring-1 ring-emerald-500/10">
                                <?php echo $areaChart(collect($uptimeSeries)->pluck('pct')->all(), 'stroke-emerald-500', 'fill-emerald-500/15'); ?>

                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Error rate')); ?></p>
                            <div class="h-20 rounded-xl bg-gradient-to-b from-amber-500/5 to-transparent p-1 ring-1 ring-amber-500/10">
                                <?php echo $areaChart($errorRateSeries, 'stroke-amber-500', 'fill-amber-500/15'); ?>

                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <p class="mb-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <span><?php echo e(__('Latency percentiles (ms)')); ?></span>
                                <span class="font-mono text-cyan-600 dark:text-cyan-400">P50 · P95 · P99</span>
                            </p>
                            <div class="flex h-28 items-end gap-0.5 rounded-xl bg-slate-950/5 p-3 dark:bg-black/20">
                                <?php $__currentLoopData = array_slice($latencySeries, -18); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="group flex flex-1 flex-col items-center gap-1">
                                        <div class="flex w-full items-end justify-center gap-px" style="height: 5.5rem">
                                            <div class="w-1/3 rounded-t bg-gradient-to-t from-sky-600 to-sky-400 opacity-80" style="height: <?php echo e(min(100, ($pt['p50'] / $latencyMax) * 100)); ?>%"></div>
                                            <div class="w-1/3 rounded-t bg-gradient-to-t from-indigo-600 to-violet-400 opacity-85" style="height: <?php echo e(min(100, ($pt['p95'] / $latencyMax) * 100)); ?>%"></div>
                                            <div class="w-1/3 rounded-t bg-gradient-to-t from-rose-600 to-orange-400 opacity-90" style="height: <?php echo e(min(100, ($pt['p99'] / $latencyMax) * 100)); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('API monitoring')); ?></p>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Endpoint health & latency')); ?></h3>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[32rem] text-left text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/30">
                                <tr>
                                    <th class="px-5 py-3"><?php echo e(__('Endpoint')); ?></th>
                                    <th class="px-3 py-3"><?php echo e(__('Status')); ?></th>
                                    <th class="px-3 py-3"><?php echo e(__('Latency')); ?></th>
                                    <th class="px-3 py-3"><?php echo e(__('Availability')); ?></th>
                                    <th class="px-5 py-3 text-right"><?php echo e(__('Requests/h')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php $__currentLoopData = $apiEndpoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                        <td class="px-5 py-3">
                                            <span class="mr-2 rounded bg-slate-900/5 px-1.5 py-0.5 font-mono text-[10px] font-bold text-indigo-600 dark:bg-white/5 dark:text-indigo-300"><?php echo e($ep['method']); ?></span>
                                            <span class="font-mono text-xs text-slate-700 dark:text-slate-200"><?php echo e($ep['path']); ?></span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase <?php echo e($ep['status'] === 'healthy' ? 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300' : ($ep['status'] === 'degraded' ? 'bg-amber-500/12 text-amber-800 dark:text-amber-200' : 'bg-rose-500/12 text-rose-700 dark:text-rose-300')); ?>">
                                                <span class="h-1.5 w-1.5 rounded-full <?php echo e($statusDot($ep['status'])); ?> animate-pulse"></span>
                                                <?php echo e($ep['status']); ?>

                                            </span>
                                        </td>
                                        <td class="px-3 py-3 tabular-nums font-semibold text-slate-800 dark:text-slate-100"><?php echo e($ep['latency']); ?>ms</td>
                                        <td class="px-3 py-3 tabular-nums text-slate-600 dark:text-slate-300"><?php echo e($ep['availability']); ?>%</td>
                                        <td class="px-5 py-3 text-right tabular-nums text-slate-500"><?php echo e(number_format($ep['requests'])); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400"><?php echo e(__('Synthetic checks')); ?></p>
                        <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Global probes')); ?></h3>
                        <ul class="mt-4 space-y-2">
                            <?php $__currentLoopData = $syntheticChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2 dark:border-slate-800 dark:bg-slate-800/30">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-white"><?php echo e($check['name']); ?></p>
                                        <p class="text-[10px] text-slate-500"><?php echo e($check['region']); ?> · <?php echo e($check['last']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase <?php echo e($check['status'] === 'pass' ? 'text-emerald-600' : 'text-amber-600'); ?>">
                                            <span class="h-1.5 w-1.5 rounded-full <?php echo e($statusDot($check['status'])); ?>"></span>
                                            <?php echo e($check['status']); ?>

                                        </span>
                                        <p class="tabular-nums text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e($check['latency']); ?>ms</p>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400"><?php echo e(__('Heartbeat monitors')); ?></p>
                        <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Agent pulse')); ?></h3>
                        <ul class="mt-4 space-y-2">
                            <?php $__currentLoopData = $heartbeats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full <?php echo e($statusDot($hb['status'])); ?> <?php echo e($hb['status'] === 'alive' ? 'animate-pulse' : ''); ?>"></span>
                                        <span class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e($hb['name']); ?></span>
                                    </div>
                                    <div class="text-right text-[10px] text-slate-500">
                                        <span class="font-mono"><?php echo e($hb['interval']); ?></span>
                                        <p><?php echo e($hb['last']); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>

            
            <div class="space-y-5 lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-card backdrop-blur dark:border-slate-800/80 dark:bg-slate-900/50">
                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400"><?php echo e(__('Alert center')); ?></p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Active incidents & warnings')); ?></h3>
                    </div>
                    <div class="flex gap-1 border-b border-slate-100 px-3 py-2 dark:border-slate-800">
                        <button type="button" @click="alertFilter = 'all'" class="rounded-lg px-2 py-1 text-[10px] font-semibold uppercase" :class="alertFilter === 'all' ? 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-300' : 'text-slate-500'"><?php echo e(__('All')); ?></button>
                        <button type="button" @click="alertFilter = 'critical'" class="rounded-lg px-2 py-1 text-[10px] font-semibold uppercase" :class="alertFilter === 'critical' ? 'bg-rose-500/15 text-rose-600' : 'text-slate-500'"><?php echo e(__('Critical')); ?></button>
                        <button type="button" @click="alertFilter = 'warning'" class="rounded-lg px-2 py-1 text-[10px] font-semibold uppercase" :class="alertFilter === 'warning' ? 'bg-amber-500/15 text-amber-700' : 'text-slate-500'"><?php echo e(__('Warnings')); ?></button>
                    </div>
                    <div class="max-h-[26rem] space-y-2 overflow-y-auto p-3">
                        <template x-for="alert in filteredAlerts" :key="alert.title + alert.time">
                            <div class="rounded-xl border px-3 py-2.5 transition" :class="{
                                'ring-rose-500/40 bg-rose-500/10 border-rose-500/25': alert.severity === 'critical',
                                'ring-amber-500/35 bg-amber-500/10 border-amber-500/25': alert.severity === 'warning',
                                'ring-sky-500/30 bg-sky-500/10 border-sky-500/20': alert.severity === 'info',
                                'ring-slate-500/25 bg-slate-500/5 border-slate-500/20': !['critical','warning','info'].includes(alert.severity)
                            }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider" x-text="alert.severity"></span>
                                    <span class="rounded bg-slate-900/5 px-1.5 py-0.5 text-[9px] font-mono uppercase text-slate-500 dark:bg-white/5" x-text="alert.category"></span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white" x-text="alert.title"></p>
                                <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400" x-text="alert.body"></p>
                                <div class="mt-2 flex items-center justify-between text-[10px]">
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400" x-text="alert.source"></span>
                                    <span class="text-slate-500" x-text="alert.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400"><?php echo e(__('Incident timeline')); ?></p>
                    <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Recent events')); ?></h3>
                    <ol class="relative mt-4 space-y-4 border-l border-slate-200 pl-4 dark:border-slate-700">
                        <?php $__currentLoopData = $incidentTimeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="relative">
                                <span class="absolute -left-[1.35rem] top-1 h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-slate-900 <?php echo e($statusDot($inc['severity'] === 'critical' ? 'critical' : ($inc['severity'] === 'warning' ? 'warning' : 'ok'))); ?>"></span>
                                <p class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e($inc['title']); ?></p>
                                <p class="text-[10px] text-slate-500"><?php echo e($inc['time']); ?> · <span class="capitalize <?php echo e($inc['status'] === 'active' ? 'text-rose-600' : 'text-emerald-600'); ?>"><?php echo e($inc['status']); ?></span></p>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ol>
                </div>
            </div>
        </div>

        
        <div>
            <div class="mb-3">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Application observability')); ?></p>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Tracing · endpoints · database · queues · cache')); ?></h3>
            </div>
            <div class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Request tracing')); ?></p>
                    <div class="mt-3 space-y-2">
                        <?php $__currentLoopData = $traces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-100 px-3 py-2 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[10px] text-cyan-600 dark:text-cyan-400"><?php echo e($trace['trace_id']); ?></span>
                                    <span class="text-[10px] font-bold uppercase <?php echo e($trace['status'] === 'ok' ? 'text-emerald-600' : ($trace['status'] === 'slow' ? 'text-amber-600' : 'text-rose-600')); ?>"><?php echo e($trace['status']); ?></span>
                                </div>
                                <p class="mt-1 text-xs font-medium text-slate-800 dark:text-slate-100"><?php echo e($trace['endpoint']); ?></p>
                                <div class="mt-1 flex justify-between text-[10px] text-slate-500">
                                    <span><?php echo e($trace['service']); ?></span>
                                    <span class="tabular-nums font-semibold"><?php echo e($trace['duration']); ?>ms</span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Database response times')); ?></p>
                        <ul class="mt-3 space-y-2">
                            <?php $__currentLoopData = $dbMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $db): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/40">
                                    <div class="flex justify-between text-[10px]">
                                        <span class="font-semibold uppercase <?php echo e($db['status'] === 'slow' ? 'text-amber-600' : 'text-emerald-600'); ?>"><?php echo e($db['status']); ?></span>
                                        <span class="tabular-nums text-slate-600 dark:text-slate-300"><?php echo e($db['avg_ms']); ?>ms · <?php echo e(number_format($db['calls'])); ?> calls</span>
                                    </div>
                                    <p class="mt-1 truncate font-mono text-[10px] text-slate-600 dark:text-slate-400"><?php echo e($db['query']); ?></p>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Cache analytics')); ?></p>
                        <?php if($cacheMetrics): ?>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-gradient-to-br from-violet-500/10 to-indigo-500/5 p-3 ring-1 ring-violet-500/15">
                                <p class="text-[10px] text-slate-500"><?php echo e(__('Hit rate')); ?></p>
                                <p class="text-xl font-bold tabular-nums text-violet-700 dark:text-violet-300"><?php echo e($cacheMetrics['hit_rate']); ?>%</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                                <p class="text-[10px] text-slate-500"><?php echo e(__('Memory')); ?></p>
                                <p class="text-lg font-bold tabular-nums"><?php echo e($cacheMetrics['memory_mb']); ?> MB</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                                <p class="text-[10px] text-slate-500"><?php echo e(__('Keys')); ?></p>
                                <p class="text-lg font-bold tabular-nums"><?php echo e(number_format($cacheMetrics['keys'])); ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                                <p class="text-[10px] text-slate-500"><?php echo e(__('Evictions')); ?></p>
                                <p class="text-lg font-bold tabular-nums"><?php echo e($cacheMetrics['evictions']); ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                            <p class="mt-3 text-xs text-slate-500"><?php echo e(__('Cache metrics are not connected. Enable Redis or Memcached instrumentation to populate this panel.')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Queue monitoring')); ?></p>
                    <?php if($queueMetrics): ?>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-amber-500/10 p-2 ring-1 ring-amber-500/20">
                                <p class="text-lg font-bold text-amber-700 dark:text-amber-300"><?php echo e($queueMetrics['pending']); ?></p>
                                <p class="text-[9px] uppercase text-slate-500"><?php echo e(__('Pending')); ?></p>
                            </div>
                            <div class="rounded-lg bg-sky-500/10 p-2 ring-1 ring-sky-500/20">
                                <p class="text-lg font-bold text-sky-700 dark:text-sky-300"><?php echo e($queueMetrics['processing']); ?></p>
                                <p class="text-[9px] uppercase text-slate-500"><?php echo e(__('Active')); ?></p>
                            </div>
                            <div class="rounded-lg bg-rose-500/10 p-2 ring-1 ring-rose-500/20">
                                <p class="text-lg font-bold text-rose-700 dark:text-rose-300"><?php echo e($queueMetrics['failed']); ?></p>
                                <p class="text-[9px] uppercase text-slate-500"><?php echo e(__('Failed')); ?></p>
                            </div>
                        </div>
                        <p class="mt-3 text-center text-xs text-slate-500"><?php echo e(__('Throughput')); ?>: <span class="font-bold tabular-nums text-slate-800 dark:text-white"><?php echo e($queueMetrics['throughput']); ?>/min</span></p>
                        <ul class="mt-3 space-y-1.5">
                            <?php $__currentLoopData = $queueMetrics['workers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between rounded-lg px-2 py-1.5 text-xs ring-1 ring-slate-100 dark:ring-slate-800">
                                    <span class="font-medium"><?php echo e($w['name']); ?></span>
                                    <span class="flex items-center gap-2">
                                        <span class="tabular-nums text-slate-500"><?php echo e($w['jobs']); ?> jobs</span>
                                        <span class="h-1.5 w-1.5 rounded-full <?php echo e($statusDot($w['status'] === 'healthy' ? 'ok' : ($w['status'] === 'busy' ? 'warning' : 'degraded'))); ?>"></span>
                                    </span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <p class="mt-3 text-xs text-slate-500"><?php echo e(__('Queue metrics are not connected. Configure Horizon or your queue driver to populate this panel.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-900 to-slate-950 p-5 text-white shadow-2xl ring-1 ring-white/10 dark:border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-400"><?php echo e(__('Escalation policies')); ?></p>
                <h3 class="mt-1 text-sm font-semibold"><?php echo e(__('Alert routing & incident ownership')); ?></h3>
                <div class="mt-4 space-y-4">
                    <template x-for="policy in policies" :key="policy.name">
                        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold" x-text="policy.name"></p>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase" :class="policy.enabled ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-400'" x-text="policy.enabled ? '<?php echo e(__('Enabled')); ?>' : '<?php echo e(__('Disabled')); ?>'"></span>
                            </div>
                            <div class="mt-3 space-y-2">
                                <template x-for="lvl in policy.levels" :key="lvl.level">
                                    <div class="flex items-center gap-3 rounded-lg bg-black/20 px-3 py-2 text-xs">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-cyan-500/20 font-bold text-cyan-300" x-text="'L' + lvl.level"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-slate-200"><span x-text="lvl.channel"></span> · <span class="text-slate-400" x-text="lvl.delay"></span></p>
                                            <p class="text-[10px] text-slate-500" x-text="lvl.owner"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400"><?php echo e(__('Tenant monitoring')); ?></p>
                    <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Per-tenant health')); ?></h3>
                    <ul class="mt-3 space-y-2">
                        <?php $__currentLoopData = $tenantHealth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e($t['name']); ?></p>
                                    <p class="text-[10px] text-slate-500"><?php echo e($t['uptime'] !== null ? $t['uptime'].'% '.__('uptime') : __('Uptime pending sync')); ?></p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase <?php echo e($t['status'] === 'healthy' ? 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/12 text-amber-800'); ?>">
                                    <?php if($t['alerts'] > 0): ?>
                                        <?php echo e($t['alerts']); ?> <?php echo e(__('alert')); ?>

                                    <?php else: ?>
                                        <?php echo e(__('OK')); ?>

                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Deployments')); ?></p>
                    <h3 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Recent rollout events')); ?></h3>
                    <?php if(count($deploymentEvents)): ?>
                        <ul class="mt-3 space-y-2">
                            <?php $__currentLoopData = $deploymentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="rounded-lg border border-slate-100 px-3 py-2 text-xs dark:border-slate-800">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-slate-900 dark:text-white"><?php echo e($dep['project']); ?></span>
                                        <span class="font-mono text-indigo-600 dark:text-indigo-400">v<?php echo e($dep['version']); ?></span>
                                    </div>
                                    <div class="mt-1 flex justify-between text-[10px] text-slate-500">
                                        <span class="capitalize <?php echo e($dep['status'] === 'failed' ? 'text-rose-600' : 'text-emerald-600'); ?>"><?php echo e($dep['status']); ?></span>
                                        <span><?php echo e($dep['at']); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <p class="mt-4 text-xs text-slate-500"><?php echo e(__('No deployment events recorded yet.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/monitoring/index.blade.php ENDPATH**/ ?>