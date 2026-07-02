<?php
    $mrrMax = max(collect($mrrSeries)->max('value') ?? 0, 1);
    $growthMax = max(collect($growthSeries)->max(fn ($b) => $b['new'] + $b['churned']) ?? 0, 1);
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Subscriptions'),'subheading' => __('SaaS billing & plan operations')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subscriptions')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SaaS billing & plan operations'))]); ?>
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
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400"><?php echo e(__('Revenue')); ?></p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Subscription & Billing Center')); ?></h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            <?php echo e(__('MRR, plans, renewals, grace periods, and tenant billing — enterprise SaaS financial operations.')); ?>

                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?php echo e(route('subscriptions.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/25 transition hover:brightness-110">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <?php echo e(__('Add Subscription')); ?>

                        </a>
                        <a href="<?php echo e(route('subscriptions.create', ['upgrade' => 1])); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <?php echo e(__('Upgrade Plan')); ?>

                        </a>
                        <form method="POST" action="<?php echo e(route('subscriptions.renew')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <?php echo e(__('Renew Plan')); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('subscriptions.invoice')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <?php echo e(__('Generate Invoice')); ?>

                            </button>
                        </form>
                    </div>
                </div>

                <?php if($selectedTenant): ?>
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-violet-500/25 bg-violet-500/5 px-5 py-3 ring-1 ring-violet-500/15">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            <?php echo e(__('Filtered to subscriptions for :tenant.', ['tenant' => $selectedTenant->company_name])); ?>

                        </p>
                        <a href="<?php echo e(route('subscriptions.index')); ?>" class="text-sm font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400"><?php echo e(__('Show all tenants')); ?></a>
                    </div>
                <?php endif; ?>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('MRR'),'value' => $kpis['mrr'],'animate' => false,'trend' => $kpis['mrrGrowth'] ?? null,'sublabel' => __('ARR').': '.$kpis['arr'],'points' => $spark('sub-mrr'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('MRR')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['mrr']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['mrrGrowth'] ?? null),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('ARR').': '.$kpis['arr']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-mrr')),'tone' => 'violet']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.25-1.638M18 9.75l.75-.75a12 12 0 0 0-12 12h12V9.75" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active'),'value' => $kpis['active'],'trend' => '+5','sublabel' => __('Paying subscriptions'),'points' => $spark('sub-active'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('+5'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Paying subscriptions')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-active')),'tone' => 'emerald']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Trials'),'value' => $kpis['trial'],'sublabel' => __('Conversion pipeline'),'points' => $spark('sub-trial'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Trials')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['trial']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Conversion pipeline')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-trial')),'tone' => 'sky']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Expiring'),'value' => $kpis['expiring'],'trend' => $kpis['expiring'] > 0 ? '!' : '0','sublabel' => __('Next 14 days'),'points' => $spark('sub-exp'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Expiring')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['expiring']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['expiring'] > 0 ? '!' : '0'),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Next 14 days')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-exp')),'tone' => 'amber']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Suspended'),'value' => $kpis['suspended'],'sublabel' => __('Inc. cancelled'),'points' => $spark('sub-sus'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Suspended')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['suspended']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Inc. cancelled')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-sus')),'tone' => 'rose']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Churn Rate'),'value' => $kpis['churn'].'%','animate' => false,'sublabel' => __('Cancelled share of all subscriptions'),'points' => $spark('sub-churn'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Churn Rate')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['churn'].'%'),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Cancelled share of all subscriptions')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('sub-churn')),'tone' => 'indigo']); ?>
                         <?php $__env->slot('icon', null, []); ?> 
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
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
                    <div class="lg:col-span-8 space-y-5">
                        <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Subscriptions')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subscriptions'))]); ?>
                            <table class="prady-table" id="subscriptions-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Tenant')); ?></th>
                                        <th><?php echo e(__('Product')); ?></th>
                                        <th><?php echo e(__('Plan')); ?></th>
                                        <th><?php echo e(__('Cycle')); ?></th>
                                        <th><?php echo e(__('Renewal')); ?></th>
                                        <th class="text-right"><?php echo e(__('Amount')); ?></th>
                                        <th><?php echo e(__('Status')); ?></th>
                                        <th><?php echo e(__('Auto')); ?></th>
                                        <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    <?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="group">
                                            <td class="font-semibold text-slate-900 dark:text-white">
                                                <a href="<?php echo e($sub->tenant ? route('tenants.show', $sub->tenant) : '#'); ?>" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                    <?php echo e($sub->tenant?->company_name ?? '—'); ?>

                                                </a>
                                            </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($sub->product_name ?? $sub->tenant?->project?->name ?? '—'); ?></td>
                                            <td><span class="rounded-md bg-violet-500/10 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:text-violet-300"><?php echo e($sub->plan_name); ?></span></td>
                                            <td class="text-xs capitalize text-slate-500"><?php echo e($sub->billing_cycle); ?></td>
                                            <td class="text-xs text-slate-600 dark:text-slate-300"><?php echo e($sub->current_period_end?->format('M j, Y') ?? '—'); ?></td>
                                            <td class="text-right font-mono text-sm tabular-nums font-medium text-slate-900 dark:text-white">KES <?php echo e(number_format((float) $sub->amount, 0)); ?></td>
                                            <td>
                                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $sub->statusVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sub->statusVariant())]); ?><?php echo e(ucfirst(str_replace('_', ' ', $sub->status))); ?> <?php echo $__env->renderComponent(); ?>
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
                                            <td class="text-center">
                                                <?php if($sub->auto_renew): ?>
                                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check','class' => 'text-emerald-600 dark:text-emerald-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'text-emerald-600 dark:text-emerald-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                                                <?php else: ?>
                                                    —
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
                                                    <?php if($sub->tenant): ?>
                                                        <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('tenants.show', [$sub->tenant, 'tab' => 'billing'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tenants.show', [$sub->tenant, 'tab' => 'billing']))]); ?><?php echo e(__('View billing')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('subscriptions.create', array_filter([
                                                            'tenant_id' => $sub->tenant_id,
                                                            'saas_plan_id' => $sub->saas_plan_id,
                                                            'upgrade' => 1,
                                                        ]))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('subscriptions.create', array_filter([
                                                            'tenant_id' => $sub->tenant_id,
                                                            'saas_plan_id' => $sub->saas_plan_id,
                                                            'upgrade' => 1,
                                                        ])))]); ?><?php echo e(__('Upgrade plan')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('subscriptions.subscription.invoice', $sub),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('subscriptions.subscription.invoice', $sub)),'method' => 'POST']); ?><?php echo e(__('Generate invoice')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                                    <?php if($sub->status !== 'suspended'): ?>
                                                        <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('subscriptions.subscription.suspend', $sub),'method' => 'POST','danger' => true,'confirm' => __('Suspend this subscription?')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('subscriptions.subscription.suspend', $sub)),'method' => 'POST','danger' => true,'confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Suspend this subscription?'))]); ?><?php echo e(__('Suspend')); ?> <?php echo $__env->renderComponent(); ?>
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
                                            <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500"><?php echo e(__('No subscriptions yet. Add a subscription to start tracking MRR and renewals.')); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                             <?php $__env->slot('footer', null, []); ?> <?php echo e($subscriptions->links()); ?> <?php $__env->endSlot(); ?>
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

                        <div id="plans" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    <?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <a
                                            href="<?php echo e(route('subscriptions.create', ['saas_plan_id' => $plan->id])); ?>#plans"
                                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'group relative block overflow-hidden rounded-2xl border p-5 shadow-card transition hover:shadow-card-hover',
                                                'border-violet-300/60 bg-gradient-to-br from-violet-50 to-fuchsia-50/50 ring-2 ring-violet-500/20 dark:border-violet-800 dark:from-violet-950/40 dark:to-fuchsia-950/20' => $plan->tier === 'professional',
                                                'border-slate-200/80 bg-white hover:border-violet-300 dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-violet-700' => $plan->tier !== 'professional',
                                            ]); ?>"
                                        >
                                            <?php if($plan->tier === 'professional'): ?>
                                                <span class="absolute right-3 top-3 rounded-full bg-violet-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white"><?php echo e(__('Popular')); ?></span>
                                            <?php endif; ?>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e($plan->tier); ?></p>
                                            <h3 class="mt-1 text-lg font-bold text-slate-900 dark:text-white"><?php echo e($plan->name); ?></h3>
                                            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">
                                                <?php echo e($plan->slug === 'custom' ? __('Custom') : $plan->formattedMonthly()); ?>

                                                <?php if($plan->slug !== 'custom'): ?>
                                                    <span class="text-sm font-normal text-slate-500">/mo</span>
                                                <?php endif; ?>
                                            </p>
                                            <ul class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                                                <?php $__currentLoopData = $plan->features ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="flex items-start gap-2">
                                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        <?php echo e($feature); ?>

                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-200/80 pt-3 text-[10px] dark:border-slate-700">
                                                <div><span class="text-slate-500"><?php echo e(__('API')); ?></span><p class="font-semibold tabular-nums"><?php echo e($plan->api_quota ? number_format($plan->api_quota) : '∞'); ?></p></div>
                                                <div><span class="text-slate-500"><?php echo e(__('Storage')); ?></span><p class="font-semibold"><?php echo e($plan->storage_gb ? $plan->storage_gb.' GB' : '∞'); ?></p></div>
                                            </div>
                                            <p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-violet-600 opacity-0 transition group-hover:opacity-100 dark:text-violet-400"><?php echo e(__('Assign to tenant →')); ?></p>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <p class="col-span-full rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700"><?php echo e(__('No SaaS plans configured yet.')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                    <div class="space-y-5 lg:col-span-4">
                        <div class="overflow-hidden rounded-2xl border border-violet-200/60 bg-gradient-to-br from-violet-50/90 via-white to-fuchsia-50/40 p-5 shadow-card dark:border-violet-900/40 dark:from-violet-950/30 dark:via-slate-900 dark:to-fuchsia-950/20">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('MRR Trend')); ?></h2>
                            <p class="text-xs text-slate-500"><?php echo e(__('6-month recurring revenue')); ?></p>
                            <div class="mt-4 flex h-32 items-end gap-1.5">
                                <?php $__currentLoopData = $mrrSeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(10, (int) round(($point['value'] / $mrrMax) * 110)); ?>
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t-md bg-gradient-to-t from-violet-600 to-fuchsia-400" style="height: <?php echo e($h); ?>px" title="KES <?php echo e(number_format($point['value'])); ?>"></div>
                                        <span class="text-[9px] text-slate-500"><?php echo e($point['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Billing Alerts')); ?></h2>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="px-4 py-3 text-sm">
                                        <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($alert['title']); ?></p>
                                        <p class="mt-0.5 text-xs text-slate-500"><?php echo e($alert['body']); ?></p>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Billing Automation')); ?></h2>
                            <dl class="mt-3 space-y-3 text-xs">
                                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Auto-renew enabled')); ?></dt><dd class="font-semibold tabular-nums"><?php echo e($automation['auto_renew_enabled']); ?> (<?php echo e($automation['auto_renew_pct']); ?>%)</dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Failed payment retries')); ?></dt><dd class="font-semibold text-amber-600"><?php echo e($automation['retry_queue']); ?> <?php echo e(__('queued')); ?></dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Grace period active')); ?></dt><dd class="font-semibold"><?php echo e($automation['grace_active']); ?></dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Invoice sync')); ?></dt><dd class="font-semibold text-emerald-600"><?php echo e($automation['invoice_sync']); ?> <?php echo e(__('synced')); ?></dd></div>
                                <div class="flex justify-between"><dt class="text-slate-500"><?php echo e(__('Payment success')); ?></dt><dd class="font-semibold tabular-nums"><?php echo e($automation['payment_success_rate'] !== null ? $automation['payment_success_rate'].'%' : '—'); ?></dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Subscription Growth vs Churn')); ?></h2>
                        <div class="mt-4 flex h-28 items-end gap-2">
                            <?php $__currentLoopData = $growthSeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bucket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $newH = max(6, (int) round(($bucket['new'] / $growthMax) * 90));
                                    $churnH = max(4, (int) round(($bucket['churned'] / $growthMax) * 90));
                                ?>
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div class="flex w-full items-end justify-center gap-0.5">
                                        <div class="w-2 rounded-t bg-emerald-500" style="height: <?php echo e($newH); ?>px"></div>
                                        <div class="w-2 rounded-t bg-rose-400" style="height: <?php echo e($churnH); ?>px"></div>
                                    </div>
                                    <span class="text-[9px] text-slate-500"><?php echo e($bucket['label']); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-2 flex gap-4 text-[10px] font-semibold uppercase">
                            <span class="text-emerald-600"><?php echo e(__('New')); ?></span>
                            <span class="text-rose-500"><?php echo e(__('Churned')); ?></span>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Tenant Usage Insights')); ?></h2>
                        </div>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between gap-4 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            <?php if(! empty($row['tenant_id'])): ?>
                                                <a href="<?php echo e(route('tenants.show', ['tenant' => $row['tenant_id'], 'tab' => 'billing'])); ?>" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"><?php echo e($row['tenant']); ?></a>
                                            <?php else: ?>
                                                <?php echo e($row['tenant']); ?>

                                            <?php endif; ?>
                                        </p>
                                        <p class="text-xs text-slate-500"><?php echo e($row['plan']); ?> · <?php echo e($row['metric']); ?></p>
                                    </div>
                                    <div class="text-right">
                                                <p class="font-mono text-sm font-semibold tabular-nums text-slate-800 dark:text-slate-100"><?php echo e($row['value']); ?></p>
                                                <p class="text-[10px] font-medium text-emerald-600"><?php echo e($row['trend']); ?></p>
                                            </div>
                                        </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
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

<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/subscriptions/index.blade.php ENDPATH**/ ?>