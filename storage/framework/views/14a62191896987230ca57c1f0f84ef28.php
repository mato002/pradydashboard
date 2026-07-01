<?php
    $collectionMax = max(collect($collectionSeries)->max('value') ?? 0, 1);
    $gatewayMax = max($gatewayAnalytics->max('volume') ?? 1, 1);
    $heatmapMax = 1;
    foreach ($heatmap as $row) {
        $heatmapMax = max($heatmapMax, max($row['hours']));
    }
    $statusVariant = fn (?string $s): string => match ($s) {
        'successful' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        'refunded' => 'purple',
        'reversed' => 'neutral',
        default => 'neutral',
    };
    $alertRing = fn (string $t): string => match ($t) {
        'critical', 'danger' => 'ring-rose-500/30 bg-rose-500/10',
        'warning' => 'ring-amber-500/30 bg-amber-500/10',
        'success' => 'ring-emerald-500/30 bg-emerald-500/10',
        default => 'ring-sky-500/30 bg-sky-500/10',
    };
    $gwTone = fn (string $c): string => match ($c) {
        'emerald' => 'from-emerald-500 to-teal-600',
        'indigo' => 'from-indigo-500 to-violet-600',
        'sky' => 'from-sky-500 to-blue-600',
        'violet' => 'from-violet-500 to-fuchsia-600',
        'amber' => 'from-amber-500 to-orange-600',
        default => 'from-slate-500 to-slate-600',
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Payments'),'subheading' => __('Treasury & collection operations')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Treasury & collection operations'))]); ?>
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
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400"><?php echo e(__('Financials')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Payment Operations Center')); ?></h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(__('Tenant collections, gateway orchestration, reconciliations, allocations, and treasury monitoring — enterprise-grade payment rails.')); ?>

                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="<?php echo e(route('payments.reconcile')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                        <?php echo e(__('Run Reconciliation')); ?>

                    </button>
                </form>
                <form method="POST" action="<?php echo e(route('payments.retry-failed')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                        <?php echo e(__('Retry Failed')); ?>

                    </button>
                </form>
                <a href="#gateways" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                    <?php echo e(__('Gateway Fleet')); ?>

                </a>
            </div>
        </div>

        <?php if($kpis['failed'] > 0 || $kpis['pending'] > 0): ?>
            <div class="flex items-center gap-3 rounded-2xl border border-amber-500/25 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-rose-500/10 px-5 py-3 ring-1 ring-amber-500/20">
                <span class="relative flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-60"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                </span>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    <?php echo e(__(':failed failed and :pending pending transaction(s) require treasury review.', ['failed' => $kpis['failed'], 'pending' => $kpis['pending']])); ?>

                </p>
            </div>
        <?php endif; ?>

        
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Payments Collected'),'value' => $kpis['collected'],'animate' => false,'sublabel' => __('Successful settlements'),'points' => $spark('pay-collected'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments Collected')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['collected']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Successful settlements')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-collected')),'tone' => 'emerald']); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Failed Transactions'),'value' => $kpis['failed'],'trend' => $kpis['failed'] > 0 ? '-3' : '0','sublabel' => __('Declined / timeout'),'points' => $spark('pay-failed'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed Transactions')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed'] > 0 ? '-3' : '0'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Declined / timeout')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-failed')),'tone' => 'rose']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Pending Payments'),'value' => $kpis['pending'],'trend' => '+2','sublabel' => __('Awaiting confirmation'),'points' => $spark('pay-pending'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Pending Payments')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['pending']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+2'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Awaiting confirmation')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-pending')),'tone' => 'amber']); ?>
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
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Refunds'),'value' => $kpis['refunds'],'animate' => false,'sublabel' => __('Processed reversals'),'points' => $spark('pay-refund'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Refunds')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['refunds']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Processed reversals')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-refund')),'tone' => 'violet']); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 010 12h-3" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Collection Rate'),'value' => $kpis['collectionRate'].'%','animate' => false,'sublabel' => __('Success vs attempts'),'points' => $spark('pay-rate'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Collection Rate')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['collectionRate'].'%'),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Success vs attempts')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-rate')),'tone' => 'sky']); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Gateway Health'),'value' => $kpis['gatewayHealth'].'%','animate' => false,'sublabel' => __('Fleet uptime avg'),'points' => $spark('pay-gw'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Gateway Health')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['gatewayHealth'].'%'),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Fleet uptime avg')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('pay-gw')),'tone' => 'indigo']); ?>
                 <?php $__env->slot('icon', null, []); ?> 
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.374 3.374 0 001.22 3.68 3.375 3.375 0 003.68-1.22A3.375 3.375 0 0018 12.75a3.375 3.375 0 00-3.68-1.22 3.374 3.374 0 00-1.22-3.68A3.375 3.375 0 0012 8.25c1.268 0 2.39.63 3.068 1.593a3.374 3.374 0 003.68 1.22 3.375 3.375 0 001.22 3.68A3.375 3.375 0 0015.75 21a3.375 3.375 0 003.68-1.22 3.374 3.374 0 001.22-3.68A3.375 3.375 0 0021 12.75a3.375 3.375 0 00-3.68-1.22 3.374 3.374 0 00-1.22-3.68A3.375 3.375 0 0012 8.25z" /></svg>
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
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Payment Ledger')); ?></h2>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = ['' => __('All'), 'successful' => __('Successful'), 'pending' => __('Pending'), 'failed' => __('Failed'), 'refunded' => __('Refunded')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a
                                    href="<?php echo e(route('payments.index', array_filter(['status' => $val ?: null, 'gateway' => request('gateway')]))); ?>"
                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'rounded-lg px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide transition',
                                        'bg-emerald-500/15 text-emerald-700 ring-1 ring-emerald-500/25 dark:text-emerald-300' => request('status', '') === $val,
                                        'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' => request('status', '') !== $val,
                                    ]); ?>"
                                ><?php echo e($label); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Transaction ID')); ?></th>
                                    <th><?php echo e(__('Tenant')); ?></th>
                                    <th class="text-right"><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Method')); ?></th>
                                    <th><?php echo e(__('Invoice')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Gateway')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="group">
                                        <td>
                                            <p class="font-mono text-xs font-semibold text-slate-900 dark:text-white"><?php echo e($payment->displayId()); ?></p>
                                            <p class="text-[10px] text-slate-400"><?php echo e($payment->reference); ?></p>
                                        </td>
                                        <td class="text-sm font-medium text-slate-700 dark:text-slate-200"><?php echo e($payment->tenant?->company_name ?? '—'); ?></td>
                                        <td class="text-right font-mono text-sm tabular-nums font-semibold text-slate-900 dark:text-white"><?php echo e($payment->formattedAmount()); ?></td>
                                        <td class="text-xs text-slate-600 dark:text-slate-300"><?php echo e($payment->method ?? '—'); ?></td>
                                        <td class="font-mono text-xs text-indigo-600 dark:text-indigo-400"><?php echo e($payment->invoice?->invoice_number ?? '—'); ?></td>
                                        <td>
                                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($payment->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($payment->status))]); ?>
                                                <?php echo e(ucfirst($payment->status ?? 'unknown')); ?>

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
                                        <td>
                                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?php echo e($payment->gatewayLabel()); ?></span>
                                        </td>
                                        <td class="text-xs text-slate-500"><?php echo e($payment->paid_at?->format('M j, Y H:i') ?? $payment->created_at?->format('M j, Y')); ?></td>
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
                                                <?php if($payment->invoice): ?>
                                                    <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.preview', $payment->invoice),'fullNav' => true,'target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.preview', $payment->invoice)),'fullNav' => true,'target' => '_blank']); ?><?php echo e(__('View Receipt')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                                <?php else: ?>
                                                    <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.index', ['tab' => 'payments']).'#payment-'.$payment->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.index', ['tab' => 'payments']).'#payment-'.$payment->id)]); ?><?php echo e(__('View Receipt')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.index', ['tab' => 'payments']).'#payment-'.$payment->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.index', ['tab' => 'payments']).'#payment-'.$payment->id)]); ?><?php echo e(__('Allocate')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                                <?php if($payment->status === 'successful'): ?>
                                                    <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.payments.reverse', $payment),'method' => 'POST','confirm' => __('Reverse this payment and unallocate linked invoices?'),'danger' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.payments.reverse', $payment)),'method' => 'POST','confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Reverse this payment and unallocate linked invoices?')),'danger' => true]); ?><?php echo e(__('Refund')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
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
                                        <td colspan="9" class="py-12 text-center text-sm text-slate-500"><?php echo e(__('No payment transactions recorded yet.')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200/80 px-4 py-3 text-sm dark:border-slate-800/80">
                        <?php echo e($payments->links()); ?>

                    </div>
                </div>
            </div>

            <div class="space-y-5 lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Treasury Alerts')); ?></h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Failed collections & settlement exceptions')); ?></p>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex gap-3 px-4 py-3.5">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 <?php echo e($alertRing($alert['type'])); ?>">
                                    <?php if($alert['type'] === 'danger'): ?>
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

                <div class="overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 shadow-card dark:border-emerald-900/40 dark:from-emerald-950/40 dark:via-slate-900 dark:to-teal-950/30">
                    <div class="border-b border-emerald-200/50 px-4 py-3 dark:border-emerald-900/50">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Reconciliation Summary')); ?></h2>
                    </div>
                    <div class="space-y-4 p-4">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                <p class="text-slate-500"><?php echo e(__('Matched')); ?></p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-emerald-700 dark:text-emerald-300"><?php echo e($reconciliation['matched']); ?></p>
                            </div>
                            <div class="rounded-xl border border-slate-200/80 bg-white/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950/50">
                                <p class="text-slate-500"><?php echo e(__('Pending')); ?></p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-amber-700 dark:text-amber-300"><?php echo e($reconciliation['pending']); ?></p>
                            </div>
                            <div class="rounded-xl border border-rose-200/60 bg-rose-50/50 px-3 py-2.5 dark:border-rose-900/40 dark:bg-rose-950/30">
                                <p class="text-rose-700 dark:text-rose-300"><?php echo e(__('Exceptions')); ?></p>
                                <p class="mt-1 font-mono text-sm font-bold text-rose-900 dark:text-rose-100"><?php echo e($reconciliation['exceptions']); ?></p>
                            </div>
                            <div class="rounded-xl border border-violet-200/60 bg-violet-50/50 px-3 py-2.5 dark:border-violet-900/40 dark:bg-violet-950/30">
                                <p class="text-violet-700 dark:text-violet-300"><?php echo e(__('Unallocated')); ?></p>
                                <p class="mt-1 font-mono text-sm font-bold text-violet-900 dark:text-violet-100"><?php echo e($reconciliation['unallocated']); ?></p>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs">
                                <span class="text-slate-500"><?php echo e(__('Match rate')); ?></span>
                                <span class="font-semibold tabular-nums"><?php echo e($reconciliation['match_rate']); ?>%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: <?php echo e(min(100, $reconciliation['match_rate'])); ?>%"></div>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400"><?php echo e(__('Last run :time · :window', ['time' => $reconciliation['last_run'], 'window' => $reconciliation['settlement_window']])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Recurring Collections')); ?></h2>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Active mandates')); ?></span><span class="font-semibold tabular-nums"><?php echo e($recurring['active_mandates']); ?></span></li>
                        <li class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Next auto-collect')); ?></span><span class="font-medium"><?php echo e($recurring['next_run']); ?></span></li>
                        <li class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Retry queue')); ?></span><span class="font-semibold text-rose-600"><?php echo e($recurring['retry_queue']); ?></span></li>
                        <li class="flex justify-between"><span class="text-slate-500"><?php echo e(__('Auto-collect rate')); ?></span><span class="font-semibold text-emerald-600"><?php echo e($recurring['auto_collect_rate']); ?>%</span></li>
                    </ul>
                </div>
            </div>
        </div>

        
        <div id="gateways" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Gateway Management')); ?></h2>
                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('M-Pesa, Stripe, PayPal, Flutterwave, and bank transfer rails')); ?></p>
            </div>
            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5">
                <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e(route('payments.index', ['gateway' => $gw['key']])); ?>"
                        class="group relative overflow-hidden rounded-xl border border-slate-200/80 p-4 transition hover:border-slate-300 hover:shadow-md dark:border-slate-700 dark:hover:border-slate-600"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br <?php echo e($gwTone($gw['color'])); ?> text-xs font-bold text-white shadow">
                                <?php echo e(strtoupper(substr($gw['name'], 0, 2))); ?>

                            </span>
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $gw['status'] === 'operational' ? 'success' : 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gw['status'] === 'operational' ? 'success' : 'warning')]); ?>
                                <?php echo e($gw['status'] === 'operational' ? __('Live') : __('Degraded')); ?>

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
                        <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($gw['name']); ?></p>
                        <p class="mt-0.5 text-xs text-slate-500"><?php echo e($gw['volume']); ?> <?php echo e(__('volume')); ?></p>
                        <div class="mt-3 space-y-2">
                            <div>
                                <div class="mb-0.5 flex justify-between text-[10px]">
                                    <span class="text-slate-500"><?php echo e(__('Uptime')); ?></span>
                                    <span class="font-semibold tabular-nums"><?php echo e($gw['uptime']); ?>%</span>
                                </div>
                                <div class="h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: <?php echo e($gw['uptime']); ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-0.5 flex justify-between text-[10px]">
                                    <span class="text-slate-500"><?php echo e(__('Success rate')); ?></span>
                                    <span class="font-semibold tabular-nums"><?php echo e($gw['success']); ?>%</span>
                                </div>
                                <div class="h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: <?php echo e($gw['success']); ?>%"></div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400"><?php echo e(__('Latency')); ?>: <span class="font-mono font-semibold text-slate-600 dark:text-slate-300"><?php echo e($gw['latency']); ?>ms</span></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Collection Trends')); ?></h2>
                            <p class="text-xs text-slate-500"><?php echo e(__('6-month payment volume')); ?></p>
                        </div>
                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">+18.6%</span>
                    </div>
                    <div class="flex h-44 items-end gap-2" aria-hidden="true">
                        <?php $__currentLoopData = $collectionSeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $h = max(12, (int) round(($point['value'] / $collectionMax) * 160));
                                $failH = max(4, (int) round(($point['failed'] / $collectionMax) * 160));
                            ?>
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <div class="flex w-full flex-col items-center justify-end gap-0.5" style="height: 168px">
                                    <div class="w-full max-w-[28px] rounded-t-sm bg-gradient-to-t from-emerald-600/80 to-teal-400/90" style="height: <?php echo e(max(4, $h - $failH)); ?>px" title="<?php echo e(__('Collected')); ?>"></div>
                                    <div class="w-full max-w-[28px] rounded-t-sm bg-rose-400/70" style="height: <?php echo e($failH); ?>px" title="<?php echo e(__('Failed')); ?>"></div>
                                </div>
                                <span class="text-[10px] font-medium text-slate-500"><?php echo e($point['label']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-3 flex gap-4 text-[10px] text-slate-500">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-emerald-500"></span> <?php echo e(__('Collected')); ?></span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-rose-400"></span> <?php echo e(__('Failed')); ?></span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-5">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Gateway Analytics')); ?></h2>
                    <p class="text-xs text-slate-500"><?php echo e(__('Volume by payment rail')); ?></p>
                    <ul class="mt-4 space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $gatewayAnalytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li>
                                <div class="mb-1 flex justify-between text-xs">
                                    <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo e($row['label']); ?></span>
                                    <span class="tabular-nums text-slate-500">KES <?php echo e(number_format($row['volume'], 0)); ?> · <?php echo e($row['success']); ?>%</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500" style="width: <?php echo e(round(($row['volume'] / $gatewayMax) * 100)); ?>%"></div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="text-xs text-slate-500"><?php echo e(__('No gateway data yet.')); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Transaction Heatmap')); ?></h2>
                    <p class="text-xs text-slate-500"><?php echo e(__('Volume intensity by day and hour (UTC)')); ?></p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[640px]">
                    <div class="mb-1 grid grid-cols-[40px_repeat(24,minmax(0,1fr))] gap-0.5 text-[9px] text-slate-400">
                        <div></div>
                        <?php for($h = 0; $h < 24; $h += 2): ?>
                            <div class="col-span-2 text-center"><?php echo e(str_pad((string) $h, 2, '0', STR_PAD_LEFT)); ?></div>
                        <?php endfor; ?>
                    </div>
                    <?php $__currentLoopData = $heatmap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-0.5 grid grid-cols-[40px_repeat(24,minmax(0,1fr))] gap-0.5">
                            <div class="flex items-center text-[10px] font-medium text-slate-500"><?php echo e($row['day']); ?></div>
                            <?php $__currentLoopData = $row['hours']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intensity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pct = ($intensity / $heatmapMax) * 100;
                                    $opacity = 0.15 + ($pct / 100) * 0.85;
                                ?>
                                <div
                                    class="aspect-square rounded-sm bg-emerald-500"
                                    style="opacity: <?php echo e($opacity); ?>"
                                    title="<?php echo e($intensity); ?> <?php echo e(__('events')); ?>"
                                ></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/payments/index.blade.php ENDPATH**/ ?>