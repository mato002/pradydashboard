<?php
    $levelVariant = fn (?string $l): string => match ($l) {
        'suspended', 'terminated' => 'danger',
        'restricted' => 'warning',
        'warning' => 'info',
        'soft_reminder' => 'neutral',
        default => 'neutral',
    };

    $enforcementVariant = fn (string $s): string => match (true) {
        str_contains($s, 'Enforcing') => 'danger',
        str_contains($s, 'Active') => 'warning',
        str_contains($s, 'Monitoring') => 'info',
        default => 'neutral',
    };

    $severityRing = fn (string $s): string => match ($s) {
        'critical' => 'border-rose-500/30 bg-rose-500/10',
        'warning' => 'border-amber-500/30 bg-amber-500/10',
        default => 'border-sky-500/25 bg-sky-500/10',
    };

    $trendMax = max(collect($restrictionTrends)->max('count') ?? 0, 1);
    $failedMax = max(collect($securityAnalytics['failed_logins'])->max() ?? 0, 1);

    $areaChart = function (array $points, string $stroke, string $fill, int $w = 200, int $h = 48): string {
        $pts = collect($points)->values()->map(fn ($v) => (float) $v)->all();
        if (count($pts) < 2) {
            $pts = [30, 45, 40, 55, 50, 60];
        }
        $min = min($pts);
        $max = max($pts);
        $range = max(1e-6, $max - $min);
        $linePts = [];
        foreach ($pts as $i => $v) {
            $x = ($i / (count($pts) - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 4) - 2;
            $linePts[] = round($x, 1).','.round($y, 1);
        }
        $line = implode(' ', $linePts);

        return '<svg class="w-full h-full" viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="none" aria-hidden="true"><polygon points="0,'.$h.' '.$line.' '.$w.','.$h.'" class="'.$fill.'"/><polyline points="'.$line.'" class="'.$stroke.' fill-none" stroke-width="2"/></svg>';
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Access Controls'),'subheading' => __('Enterprise IAM & tenant enforcement')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access Controls')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enterprise IAM & tenant enforcement'))]); ?>
    <div
        x-data="accessGovernance(<?php echo \Illuminate\Support\Js::from($detailPayload)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($tenantOptions)->toHtml() ?>)"
        class="space-y-6"
    >
        <?php if(session('status')): ?>
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-violet-500 shadow-[0_0_10px_rgba(139,92,246,0.8)]"></span>
                    </span>
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400"><?php echo e(__('Policy engine active')); ?></p>
                </div>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Security & Enforcement Center')); ?></h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(__('Tenant access governance — grace periods, restrictions, module gating, automated enforcement, and audit trails.')); ?>

                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="showPolicyModal = true" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <?php echo e(__('Add Policy')); ?>

                </button>
                <a href="<?php echo e(route('activity-logs.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    <?php echo e(__('View Audit Trail')); ?>

                </a>
            </div>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active Policies'),'value' => $kpis['active_policies'],'trend' => '+3','sublabel' => __('Currently enforced'),'points' => $spark('ac-policies'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active Policies')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_policies']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+3'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Currently enforced')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('ac-policies')),'tone' => 'indigo']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Restricted Tenants'),'value' => $kpis['restricted_tenants'],'sublabel' => __('Feature or login limits'),'points' => $spark('ac-restricted'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Restricted Tenants')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['restricted_tenants']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Feature or login limits')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('ac-restricted')),'tone' => 'amber']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Suspended Accounts'),'value' => $kpis['suspended_accounts'],'sublabel' => __('Full lockdown'),'points' => $spark('ac-suspended'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Suspended Accounts')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['suspended_accounts']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Full lockdown')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('ac-suspended')),'tone' => 'rose']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Grace Period'),'value' => $kpis['grace_accounts'],'sublabel' => __('Escalation window'),'points' => $spark('ac-grace'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Grace Period')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['grace_accounts']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Escalation window')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('ac-grace')),'tone' => 'sky']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Enforcement Events'),'value' => $kpis['enforcement_events'],'trend' => '+8','sublabel' => __('Last 7 days'),'points' => $spark('ac-events'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enforcement Events')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['enforcement_events']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+8'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Last 7 days')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('ac-events')),'tone' => 'violet']); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-6 10.5 6M4.5 19.5h15" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Failed Access'),'value' => $kpis['failed_access'],'sublabel' => __('24h window'),'points' => $securityAnalytics['failed_logins'],'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed Access')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed_access']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('24h window')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($securityAnalytics['failed_logins']),'tone' => 'rose']); ?>
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
        </div>

        <div class="grid gap-5 lg:grid-cols-12">
            
            <div class="space-y-5 lg:col-span-8">
                <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Access policy registry'),'actionHref' => route('tenants.index'),'actionLabel' => __('All tenants')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access policy registry')),'action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tenants.index')),'action-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('All tenants'))]); ?>
                    <table class="prady-table">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Tenant')); ?></th>
                                <th><?php echo e(__('Policy Type')); ?></th>
                                <th><?php echo e(__('Restriction Level')); ?></th>
                                <th><?php echo e(__('Trigger')); ?></th>
                                <th><?php echo e(__('Enforcement')); ?></th>
                                <th><?php echo e(__('Expiry')); ?></th>
                                <th><?php echo e(__('Last Activity')); ?></th>
                                <th class="text-right"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <?php $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr
                                    class="cursor-pointer transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                    @click="selectPolicy(<?php echo e($policy['tenant_id']); ?>)"
                                    :class="selectedId === <?php echo e($policy['tenant_id']); ?> && 'bg-violet-500/5'"
                                >
                                    <td class="font-semibold text-slate-900 dark:text-white">
                                        <a href="<?php echo e($policy['tenant_url']); ?>" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" @click.stop><?php echo e($policy['tenant']); ?></a>
                                    </td>
                                    <td><?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $levelVariant($policy['level'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($levelVariant($policy['level']))]); ?><?php echo e($policy['policy_type']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?></td>
                                    <td class="text-xs text-slate-600 dark:text-slate-300"><?php echo e($policy['restriction_level']); ?></td>
                                    <td class="text-xs text-slate-500"><?php echo e($policy['trigger']); ?></td>
                                    <td><?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $enforcementVariant($policy['enforcement_status'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enforcementVariant($policy['enforcement_status']))]); ?><?php echo e($policy['enforcement_status']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?></td>
                                    <td class="text-xs tabular-nums text-slate-500"><?php echo e($policy['expiry']); ?></td>
                                    <td class="text-xs text-slate-500"><?php echo e($policy['last_activity']); ?></td>
                                    <td class="text-right" @click.stop>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('access-controls.restrict', $policy['tenant_id']),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('access-controls.restrict', $policy['tenant_id'])),'method' => 'POST']); ?><?php echo e(__('Restrict')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('access-controls.unlock', $policy['tenant_id']),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('access-controls.unlock', $policy['tenant_id'])),'method' => 'POST']); ?><?php echo e(__('Unlock')); ?> <?php echo $__env->renderComponent(); ?>
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
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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

                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-5 text-white shadow-2xl ring-1 ring-white/10 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-300"><?php echo e(__('Enforcement engine')); ?></p>
                    <h3 class="mt-1 text-sm font-semibold"><?php echo e(__('Automated controls & manual overrides')); ?></h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <?php $__currentLoopData = $enforcementControls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $control): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-white/10 bg-white/5 p-3 transition hover:border-violet-500/40 hover:bg-white/10">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold"><?php echo e($control['label']); ?></p>
                                    <span class="relative flex h-2 w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-50"></span>
                                        <span class="relative h-2 w-2 rounded-full bg-violet-400"></span>
                                    </span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400"><?php echo e($control['description']); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-white/10 pt-4">
                        <template x-if="selected">
                            <form method="POST" :action="`<?php echo e(url('access-controls/tenants')); ?>/${selectedId}/suspend`" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold hover:bg-rose-500"><?php echo e(__('Suspend Access')); ?></button>
                            </form>
                            <form method="POST" :action="`<?php echo e(url('access-controls/tenants')); ?>/${selectedId}/grace`" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold hover:bg-amber-500"><?php echo e(__('Enable Grace Period')); ?></button>
                            </form>
                            <form method="POST" :action="`<?php echo e(url('access-controls/tenants')); ?>/${selectedId}/unlock`" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold hover:bg-emerald-500"><?php echo e(__('Unlock Tenant')); ?></button>
                            </form>
                            <form method="POST" :action="`<?php echo e(url('access-controls/tenants')); ?>/${selectedId}/restrict`" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold hover:bg-violet-500"><?php echo e(__('Apply Restrictions')); ?></button>
                            </form>
                        </template>
                        <p x-show="!selected" class="text-xs text-slate-400"><?php echo e(__('Select a tenant from the policy table to apply enforcement actions.')); ?></p>
                    </div>
                </div>

                
                <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Role & module control')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Role & module control'))]); ?>
                    <table class="prady-table text-xs">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Tenant')); ?></th>
                                <?php $__currentLoopData = $moduleMatrix['keys']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="text-center"><?php echo e($moduleMatrix['labels'][$key] ?? $key); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <?php $__currentLoopData = $moduleMatrix['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="font-semibold text-slate-800 dark:text-slate-100"><?php echo e($row['tenant']); ?></td>
                                    <?php $__currentLoopData = $moduleMatrix['keys']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-center">
                                            <?php if($row['modules'][$key] ?? false): ?>
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/25 dark:text-emerald-300"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check','class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
                                            <?php else: ?>
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 ring-1 ring-rose-500/20">✕</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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

            
            <div class="space-y-5 lg:col-span-4">
                
                <div class="overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-b from-amber-500/5 to-transparent shadow-card dark:border-amber-500/25 dark:from-amber-500/10">
                    <div class="border-b border-amber-500/15 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-700 dark:text-amber-300"><?php echo e(__('Grace period management')); ?></p>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Overdue escalation')); ?></h3>
                    </div>
                    <div class="max-h-64 space-y-2 overflow-y-auto p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $graceAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grace): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="rounded-xl border px-3 py-2.5 <?php echo e($severityRing($grace['escalation'])); ?>">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($grace['tenant']); ?></p>
                                    <span class="font-mono text-xs font-bold tabular-nums text-amber-700 dark:text-amber-300"><?php echo e($grace['days_left']); ?>d</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500"><?php echo e(__('Renewal')); ?>: <?php echo e($grace['renewal']); ?> · <?php echo e($grace['currency']); ?> <?php echo e(number_format((float) $grace['amount'], 0)); ?></p>
                                <div class="mt-2 h-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="width: <?php echo e(min(100, max(8, ($grace['days_left'] / 14) * 100))); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-center text-xs text-slate-500 py-4"><?php echo e(__('No tenants currently in grace period.')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400"><?php echo e(__('Enforcement timeline')); ?></p>
                    <ul class="mt-3 space-y-3">
                        <?php $__currentLoopData = $enforcementTimeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="relative border-l-2 border-violet-500/30 pl-4">
                                <span class="absolute -left-[5px] top-1 h-2 w-2 rounded-full <?php echo e($event['severity'] === 'critical' ? 'bg-rose-500' : ($event['severity'] === 'warning' ? 'bg-amber-500' : 'bg-sky-500')); ?>"></span>
                                <p class="text-[10px] text-slate-500"><?php echo e($event['time']); ?></p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($event['title']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e($event['body']); ?></p>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                
                <div x-show="selected" x-cloak class="rounded-2xl border border-violet-500/25 bg-slate-950 p-4 text-white ring-1 ring-violet-500/20">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300"><?php echo e(__('Policy inspector')); ?></p>
                    <h3 class="mt-1 font-semibold" x-text="selected?.tenant"></h3>
                    <dl class="mt-3 space-y-2 text-xs">
                        <div class="flex justify-between"><dt class="text-slate-400"><?php echo e(__('Policy')); ?></dt><dd class="font-medium" x-text="selected?.policy_type"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400"><?php echo e(__('Trigger')); ?></dt><dd x-text="selected?.trigger"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400"><?php echo e(__('Status')); ?></dt><dd x-text="selected?.enforcement_status"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400"><?php echo e(__('Grace left')); ?></dt><dd class="tabular-nums" x-text="(selected?.grace_days_left ?? 0) + ' days'"></dd></div>
                    </dl>
                    <template x-if="selected?.disabled_modules?.length">
                        <div class="mt-3">
                            <p class="text-[10px] uppercase text-slate-400"><?php echo e(__('Disabled modules')); ?></p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <template x-for="m in selected.disabled_modules" :key="m">
                                    <span class="rounded-full bg-rose-500/20 px-2 py-0.5 text-[10px] text-rose-200" x-text="m"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                    <a :href="selected?.tenant_url" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-violet-600 py-2 text-xs font-semibold hover:bg-violet-500"><?php echo e(__('Open tenant record')); ?></a>
                </div>
            </div>
        </div>

        
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400"><?php echo e(__('Failed login attempts')); ?></p>
                <div class="mt-3 h-24"><?php echo $areaChart($securityAnalytics['failed_logins'], 'stroke-rose-500', 'fill-rose-500/15'); ?></div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400"><?php echo e(__('Policy violations')); ?></p>
                <div class="mt-3 h-24"><?php echo $areaChart($securityAnalytics['violations'], 'stroke-amber-500', 'fill-amber-500/15'); ?></div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400"><?php echo e(__('Restriction trends')); ?></p>
                <div class="mt-4 flex h-24 items-end gap-2">
                    <?php $__currentLoopData = $restrictionTrends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-gradient-to-t from-violet-600 to-indigo-400" style="height: <?php echo e(max(10, ($day['count'] / $trendMax) * 100)); ?>%"></div>
                            <span class="text-[10px] text-slate-500"><?php echo e($day['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Audit history')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Audit history'))]); ?>
            <table class="prady-table">
                <thead>
                    <tr>
                        <th><?php echo e(__('Tenant')); ?></th>
                        <th><?php echo e(__('Action')); ?></th>
                        <th><?php echo e(__('Actor')); ?></th>
                        <th><?php echo e(__('When')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php $__currentLoopData = $auditHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-medium text-slate-900 dark:text-white"><?php echo e($entry['tenant']); ?></td>
                            <td class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($entry['action']); ?></td>
                            <td class="text-xs text-slate-500"><?php echo e($entry['actor']); ?></td>
                            <td class="text-xs text-slate-500"><?php echo e($entry['at']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

        
        <div x-show="showPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @keydown.escape.window="showPolicyModal = false">
            <div @click.outside="showPolicyModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white"><?php echo e(__('Add access policy')); ?></h3>
                <form method="POST" action="<?php echo e(route('access-controls.policies.store')); ?>" class="mt-4 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300"><?php echo e(__('Tenant')); ?></label>
                        <select name="tenant_id" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tenant->id); ?>"><?php echo e($tenant->company_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300"><?php echo e(__('Restriction level')); ?></label>
                        <select name="level" required class="mt-1 w-full rounded-xl border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                            <option value="soft_reminder"><?php echo e(__('Soft reminder')); ?></option>
                            <option value="warning"><?php echo e(__('Warning / grace')); ?></option>
                            <option value="restricted"><?php echo e(__('Restricted')); ?></option>
                            <option value="suspended"><?php echo e(__('Suspended')); ?></option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="restrict_login" value="1" class="rounded border-slate-300 text-violet-600">
                        <?php echo e(__('Disable login')); ?>

                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showPolicyModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"><?php echo e(__('Cancel')); ?></button>
                        <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500"><?php echo e(__('Apply Policy')); ?></button>
                    </div>
                </form>
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


<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/access-controls/index.blade.php ENDPATH**/ ?>