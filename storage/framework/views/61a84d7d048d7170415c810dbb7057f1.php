<?php
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
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Backups'),'subheading' => __('Disaster recovery & snapshot operations')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Backups')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Disaster recovery & snapshot operations'))]); ?>
    <div
                x-data="{ toast: <?php echo \Illuminate\Support\Js::from(session('status'))->toHtml() ?> }"
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

                
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400"><?php echo e(__('Infrastructure')); ?></p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Backup Management Center')); ?></h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            <?php echo e(__('Server snapshots, database archives, tenant vaults, schedules, and disaster recovery — unified operations console.')); ?>

                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="<?php echo e(route('backups.run')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347a1.125 1.125 0 0 1-1.667-.985V5.653Z" /></svg>
                                <?php echo e(__('Run Backup')); ?>

                            </button>
                        </form>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                            <?php echo e(__('Restore Backup')); ?>

                        </button>
                        <a href="#schedules" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            <?php echo e(__('Configure Schedule')); ?>

                        </a>
                    </div>
                </div>

                <?php if($runningBackups > 0): ?>
                    <div class="flex items-center gap-3 rounded-2xl border border-cyan-500/25 bg-gradient-to-r from-cyan-500/10 via-indigo-500/5 to-violet-500/10 px-5 py-3 ring-1 ring-cyan-500/20">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-cyan-500"></span>
                        </span>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            <?php echo e(__(':count backup job(s) currently running across the fleet.', ['count' => $runningBackups])); ?>

                        </p>
                    </div>
                <?php endif; ?>

                
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Total Backups'),'value' => $kpis['total'],'trend' => '+12%','sublabel' => __('All job types'),'points' => $spark('bk-total'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total Backups')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['total']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+12%'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('All job types')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-total')),'tone' => 'indigo']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Successful'),'value' => $kpis['successful'],'trend' => $kpis['successRate'].'%','sublabel' => __('Success rate'),'points' => $spark('bk-ok'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Successful')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['successful']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['successRate'].'%'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Success rate')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-ok')),'tone' => 'emerald']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Failed'),'value' => $kpis['failed'],'trend' => $kpis['failed'] > 0 ? '-2' : '0','sublabel' => __('Requires attention'),'points' => $spark('bk-fail'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed'] > 0 ? '-2' : '0'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Requires attention')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-fail')),'tone' => 'rose']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Storage Consumed'),'value' => $kpis['storage'],'animate' => false,'trend' => '+8.4%','sublabel' => __('Object + block storage'),'points' => $spark('bk-storage'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Storage Consumed')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['storage']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+8.4%'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Object + block storage')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-storage')),'tone' => 'violet']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Restore Points'),'value' => $kpis['restorePoints'],'trend' => '+3','sublabel' => __('Verified snapshots'),'points' => $spark('bk-rp'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Restore Points')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['restorePoints']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+3'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Verified snapshots')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-rp')),'tone' => 'sky']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                         <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Last Runtime'),'value' => $kpis['lastRuntime'],'animate' => false,'trend' => '−6%','sublabel' => __('Longest recent job'),'points' => $spark('bk-time'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Last Runtime')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['lastRuntime']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('−6%'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Longest recent job')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('bk-time')),'tone' => 'amber']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                         <?php $__env->endSlot(); ?>
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
                    <div class="lg:col-span-8">
                        <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Backup Jobs')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Backup Jobs'))]); ?>
                            <table class="prady-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Backup Name')); ?></th>
                                        <th><?php echo e(__('Server')); ?></th>
                                        <th><?php echo e(__('Tenant')); ?></th>
                                        <th><?php echo e(__('Type')); ?></th>
                                        <th class="text-right"><?php echo e(__('Size')); ?></th>
                                        <th><?php echo e(__('Last Run')); ?></th>
                                        <th><?php echo e(__('Duration')); ?></th>
                                        <th><?php echo e(__('Status')); ?></th>
                                        <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="group">
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" /></svg>
                                                            </span>
                                                            <div>
                                                                <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($backup->name); ?></p>
                                                                <?php if($backup->is_restore_point): ?>
                                                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-cyan-600 dark:text-cyan-400"><?php echo e(__('Restore point')); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($backup->server?->name ?? '—'); ?></td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($backup->tenant?->company_name ?? '—'); ?></td>
                                            <td>
                                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?php echo e($backup->backup_type); ?></span>
                                            </td>
                                            <td class="text-right font-mono text-xs tabular-nums text-slate-600 dark:text-slate-300"><?php echo e($backup->size_bytes ? $backup->formattedSize() : '—'); ?></td>
                                            <td class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($backup->started_at?->diffForHumans() ?? '—'); ?></td>
                                            <td class="font-mono text-xs tabular-nums text-slate-500"><?php echo e($backup->formattedDuration()); ?></td>
                                            <td>
                                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($backup->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($backup->status))]); ?>
                                                    <?php if($backup->status === 'running'): ?>
                                                        <span class="relative mr-0.5 flex h-1.5 w-1.5">
                                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span>
                                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php echo e(ucfirst($backup->status)); ?>

                                                 <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Download Archive')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Verify Integrity')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('View Logs')); ?> <?php echo $__env->renderComponent(); ?>
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="9" class="py-12 text-center text-sm text-slate-500"><?php echo e(__('No backup jobs recorded yet.')); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                             <?php $__env->slot('footer', null, []); ?> <?php echo e($backups->links()); ?> <?php $__env->endSlot(); ?>
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
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Failed Backup Alerts')); ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Critical operational signals')); ?></p>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex gap-3 px-4 py-3.5">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 <?php echo e($alertRing($alert['type'])); ?>">
                                            <?php if($alert['type'] === 'danger' || $alert['type'] === 'critical'): ?>
                                                <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            <?php elseif($alert['type'] === 'success'): ?>
                                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            <?php else: ?>
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-3.75 9h7.5M12 3v.75" /></svg>
                                            <?php endif; ?>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($alert['title']); ?></p>
                                            <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400"><?php echo e($alert['body']); ?></p>
                                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e($alert['time']); ?></p>
                                        </div>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        
                        <div class="overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-50/80 via-white to-violet-50/50 shadow-card dark:border-indigo-900/40 dark:from-indigo-950/40 dark:via-slate-900 dark:to-violet-950/30">
                            <div class="border-b border-indigo-200/50 px-4 py-3 dark:border-indigo-900/50">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Disaster Recovery')); ?></h2>
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Restore drill')); ?></span>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $drMetrics['restore_drill_status'] === 'passed' ? 'success' : 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($drMetrics['restore_drill_status'] === 'passed' ? 'success' : 'warning')]); ?>
                                        <?php echo e($drMetrics['restore_drill_status'] === 'passed' ? __('Passed') : __('Attention')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                                <p class="text-slate-500 dark:text-slate-400"><?php echo e(__('Last restore test')); ?></p>
                                                <p class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo e($drMetrics['last_restore_test']); ?></p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                                <p class="text-slate-500 dark:text-slate-400"><?php echo e(__('Integrity check')); ?></p>
                                                <p class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo e($drMetrics['last_integrity_check']); ?></p>
                                            </div>
                                            <div class="rounded-xl border border-cyan-200/60 bg-cyan-50/50 px-3 py-2.5 dark:border-cyan-900/40 dark:bg-cyan-950/30">
                                                <p class="text-cyan-700 dark:text-cyan-300"><?php echo e(__('RPO')); ?></p>
                                                <p class="mt-1 font-mono text-sm font-bold text-cyan-900 dark:text-cyan-100"><?php echo e($drMetrics['rpo']); ?></p>
                                            </div>
                                            <div class="rounded-xl border border-violet-200/60 bg-violet-50/50 px-3 py-2.5 dark:border-violet-900/40 dark:bg-violet-950/30">
                                                <p class="text-violet-700 dark:text-violet-300"><?php echo e(__('RTO')); ?></p>
                                                <p class="mt-1 font-mono text-sm font-bold text-violet-900 dark:text-violet-100"><?php echo e($drMetrics['rto']); ?></p>
                                            </div>
                                        </div>
                                <div>
                                    <div class="mb-1 flex justify-between text-xs">
                                        <span class="text-slate-500 dark:text-slate-400"><?php echo e(__('Integrity pass rate')); ?></span>
                                        <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200"><?php echo e($drMetrics['integrity_pass_rate']); ?>%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all" style="width: <?php echo e(min(100, $drMetrics['integrity_pass_rate'])); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="schedules" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Backup Schedules')); ?></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Cron timing, retention, and next execution')); ?></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="prady-table min-w-[720px]">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Schedule')); ?></th>
                                    <th><?php echo e(__('Type')); ?></th>
                                    <th><?php echo e(__('Cron')); ?></th>
                                    <th><?php echo e(__('Next run')); ?></th>
                                    <th><?php echo e(__('Retention')); ?></th>
                                    <th><?php echo e(__('Target')); ?></th>
                                    <th class="text-center"><?php echo e(__('Enabled')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white"><?php echo e($schedule->name); ?></td>
                                        <td><span class="text-xs font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($schedule->typeLabel()); ?></span></td>
                                        <td class="font-mono text-xs text-slate-500"><?php echo e($schedule->cron_expression); ?></td>
                                        <td class="text-xs text-slate-600 dark:text-slate-300"><?php echo e($schedule->next_run_at?->format('M j, H:i') ?? '—'); ?></td>
                                        <td class="text-xs text-slate-500"><?php echo e($schedule->retention_policy); ?></td>
                                        <td class="text-xs text-slate-500">
                                            <?php echo e($schedule->server?->name ?? $schedule->tenant?->company_name ?? __('Fleet-wide')); ?>

                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="<?php echo e(route('backups.schedules.toggle', $schedule)); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button
                                                    type="submit"
                                                    role="switch"
                                                    aria-checked="<?php echo e($schedule->enabled ? 'true' : 'false'); ?>"
                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 <?php echo e($schedule->enabled ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'); ?>"
                                                >
                                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition <?php echo e($schedule->enabled ? 'translate-x-5' : 'translate-x-0'); ?>"></span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Backup Storage Growth')); ?></h2>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('6-month archive footprint trend')); ?></p>
                                    <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-semibold text-violet-700 ring-1 ring-violet-500/20 dark:text-violet-300">+8.4%</span>
                            </div>
                            <div class="flex h-40 items-end gap-2" aria-hidden="true">
                                <?php $__currentLoopData = $storageGrowth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(12, (int) round(($point['value'] / $growthMax) * 140)); ?>
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div
                                                            class="w-full rounded-t-md bg-gradient-to-t from-indigo-600/70 to-cyan-400/90 transition-all hover:from-indigo-500 hover:to-cyan-300"
                                                            style="height: <?php echo e($h); ?>px"
                                                            title="<?php echo e(\App\Models\Backup::formatBytes((int) $point['value'])); ?>"
                                                        ></div>
                                                    <span class="text-[10px] font-medium text-slate-500"><?php echo e($point['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5 lg:col-span-5">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Server Usage')); ?></h2>
                            <ul class="mt-4 space-y-3">
                                <?php $__currentLoopData = $serverStorage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <div class="mb-1 flex justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo e($row['name']); ?></span>
                                            <span class="tabular-nums text-slate-500"><?php echo e(\App\Models\Backup::formatBytes($row['bytes'])); ?> · <?php echo e($row['pct']); ?>%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: <?php echo e($row['pct']); ?>%"></div>
                                                </div>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <?php if($tenantStorage->isNotEmpty()): ?>
                            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Tenant Distribution')); ?></h2>
                                <ul class="mt-4 space-y-3">
                                    <?php $__currentLoopData = $tenantStorage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li>
                                            <div class="mb-1 flex justify-between text-xs">
                                                <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo e($row['name']); ?></span>
                                                <span class="tabular-nums text-slate-500"><?php echo e($row['pct']); ?>%</span>
                                            </div>
                                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500" style="width: <?php echo e($row['pct']); ?>%"></div>
                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
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

<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/backups/index.blade.php ENDPATH**/ ?>