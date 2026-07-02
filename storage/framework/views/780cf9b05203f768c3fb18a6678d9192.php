<?php
    use Illuminate\Support\Facades\Storage;

    $fmtKes = function (float $n): string {
        if ($n >= 1_000_000) {
            return 'KES '.number_format($n / 1_000_000, 2).'M';
        }
        if ($n >= 1_000) {
            return 'KES '.number_format($n / 1_000, 1).'K';
        }

        return 'KES '.number_format($n, 0);
    };

    $mrrLabel = $fmtKes($monthlyRevenue);
    $overdueLabel = $fmtKes($overdueExposure);

    $tenantVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'trial' => 'warning',
        'overdue' => 'danger',
        'suspended', 'cancelled' => 'neutral',
        default => 'info',
    };

    $serverTone = function ($server): string {
        return match ($server->status) {
            'online' => 'emerald',
            'offline' => 'rose',
            default => 'amber',
        };
    };

    $seriesMax = max(collect($revenueSeries)->max('value') ?? 0, 1);
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Overview'),'subheading' => __('Operations control center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Overview')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Operations control center'))]); ?>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Live workspace')); ?></p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Infrastructure & tenants at a glance')); ?></h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.view')): ?>
                <a href="<?php echo e(route('monitoring.queues')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4 text-cyan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    <?php echo e(__('Redis & Queues')); ?>

                </a>
                <a href="<?php echo e(route('monitoring.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" /></svg>
                    <?php echo e(__('Monitoring')); ?>

                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('servers.create')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <?php echo e(__('Add server')); ?>

            </a>
            <a href="<?php echo e(route('tenants.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <?php echo e(__('Add tenant')); ?>

            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Total servers'),'value' => $serversCount,'sublabel' => __('Online').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$onlineServers.'</span>','points' => $spark('servers'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total servers')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($serversCount),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Online').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$onlineServers.'</span>'),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('servers')),'tone' => 'indigo']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Hosted projects'),'value' => $projectsCount,'sublabel' => __('Active').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$activeProjects.'</span>','points' => $spark('projects'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted projects')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($projectsCount),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$activeProjects.'</span>'),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('projects')),'tone' => 'emerald']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15a2.25 2.25 0 012.25 2.25v.75m-18 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-18 0v-1.5A2.25 2.25 0 014.5 9h15a2.25 2.25 0 012.25 2.25v1.5" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Total tenants'),'value' => $tenantsCount,'sublabel' => __('Active').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$activeTenants.'</span>','points' => $spark('tenants'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total tenants')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenantsCount),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$activeTenants.'</span>'),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('tenants')),'tone' => 'amber']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Monthly revenue'),'value' => $mrrLabel,'animate' => false,'trend' => $revenueGrowthPct !== null ? (($revenueGrowthPct >= 0 ? '+' : '').number_format($revenueGrowthPct, 1).'%') : null,'sublabel' => __('Recurring &amp; hosted MRR'),'points' => $spark('mrr'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monthly revenue')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mrrLabel),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($revenueGrowthPct !== null ? (($revenueGrowthPct >= 0 ? '+' : '').number_format($revenueGrowthPct, 1).'%') : null),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Recurring &amp; hosted MRR')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('mrr')),'tone' => 'violet']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.25-1.638M18 9.75l.75-.75a12 12 0 00-12 12h12V9.75" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Overdue tenants'),'value' => $overdueTenants,'sublabel' => __('Exposure').': <span class=\'font-semibold text-rose-600 dark:text-rose-300\'>'.$overdueLabel.'</span>','points' => $spark('overdue'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Overdue tenants')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overdueTenants),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Exposure').': <span class=\'font-semibold text-rose-600 dark:text-rose-300\'>'.$overdueLabel.'</span>'),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('overdue')),'tone' => 'rose']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Open tickets'),'value' => $openTickets,'sublabel' => __('High priority').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$highPriorityTickets.'</span>','points' => $spark('tickets'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Open tickets')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openTickets),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('High priority').': <span class=\'font-semibold text-slate-800 dark:text-slate-100\'>'.$highPriorityTickets.'</span>'),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('tickets')),'tone' => 'sky']); ?>
             <?php $__env->slot('icon', null, []); ?> 
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a23.922 23.922 0 0112.204 3.66c1.115.885 1.51 2.316.94 3.572a3.105 3.105 0 01-.037.043 12.404 12.404 0 01-4.255 1.5c-.188.028-.377.052-.566.075-1.456.194-2.911.292-4.371.292-1.39 0-2.781-.085-4.163-.254a12.404 12.404 0 01-4.255-1.5 3.105 3.105 0 01-.037-.043.75.75 0 01.631-1.151 23.922 23.922 0 0012.204-3.66.75.75 0 01.631.151c.41.314.68.81.68 1.362 0 .548-.27 1.048-.68 1.362z" /></svg>
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

    <?php echo $__env->make('admin.dashboard.partials.support-ops', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.view')): ?>
        <?php if($monitoringSnapshot ?? null): ?>
            <?php echo $__env->make('admin.dashboard.partials.monitoring-ops', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php echo $__env->make('admin.dashboard.partials.attention-required', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('admin.dashboard.partials.recent-activity', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Servers snapshot'),'actionHref' => route('servers.index'),'actionLabel' => __('Manage')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Servers snapshot')),'action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('servers.index')),'action-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manage'))]); ?>
                <table class="prady-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Server')); ?></th>
                            <th><?php echo e(__('IP')); ?></th>
                            <th class="text-right"><?php echo e(__('Projects')); ?></th>
                            <th class="text-right"><?php echo e(__('Tenants')); ?></th>
                            <th><?php echo e(__('CPU')); ?></th>
                            <th><?php echo e(__('RAM')); ?></th>
                            <th><?php echo e(__('Disk')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th class="text-right"><?php echo e(__('Monthly cost')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__empty_1 = true; $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $server): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $h = $server->latestHealthLog;
                                $cpu = $h?->cpu_percent;
                                $ram = $h?->ram_percent;
                                $disk = $server->disk_usage_percent ?? $h?->disk_percent;
                            ?>
                            <tr>
                                <td class="font-semibold text-slate-900 dark:text-white">
                                    <a href="<?php echo e(route('servers.show', $server)); ?>" class="text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"><?php echo e($server->name); ?></a>
                                </td>
                                <td class="font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e($server->ip_address ?? '—'); ?></td>
                                <td class="text-right tabular-nums"><?php echo e($server->projects_count); ?></td>
                                <td class="text-right tabular-nums"><?php echo e($server->tenants_count); ?></td>
                                <td class="min-w-[5.5rem]">
                                    <?php if($cpu !== null): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: <?php echo e(min(100, (float) $cpu)); ?>%"></div>
                                            </div>
                                            <span class="text-[11px] tabular-nums text-slate-500"><?php echo e((int) round((float) $cpu)); ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="min-w-[5.5rem]">
                                    <?php if($ram !== null): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-500" style="width: <?php echo e(min(100, (float) $ram)); ?>%"></div>
                                            </div>
                                            <span class="text-[11px] tabular-nums text-slate-500"><?php echo e((int) round((float) $ram)); ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="min-w-[5.5rem]">
                                    <?php if($disk !== null): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="width: <?php echo e(min(100, (float) $disk)); ?>%"></div>
                                            </div>
                                            <span class="text-[11px] tabular-nums text-slate-500"><?php echo e((int) round((float) $disk)); ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $tone = $serverTone($server); ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold capitalize text-slate-700 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                                        <span class="h-1.5 w-1.5 rounded-full class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.35)]' => $tone === 'emerald',
                                            'bg-rose-500 shadow-[0_0_0_3px_rgba(244,63,94,0.35)]' => $tone === 'rose',
                                            'bg-amber-500 shadow-[0_0_0_3px_rgba(245,158,11,0.35)]' => $tone === 'amber',
                                        ]); ?>""></span>
                                        <?php echo e(ucfirst($server->status)); ?>

                                    </span>
                                </td>
                                <td class="text-right tabular-nums font-medium"><?php echo e($server->currency); ?> <?php echo e(number_format((float) $server->monthly_cost, 0)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('No servers yet. Add your first VPS or cPanel node.')); ?></td>
                            </tr>
                        <?php endif; ?>
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

        <div class="lg:col-span-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                    <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Recent tenants')); ?></h2>
                    <a href="<?php echo e(route('tenants.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"><?php echo e(__('View all')); ?></a>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="flex items-center gap-3 px-4 py-3 transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                            <?php if($tenant->logo_path): ?>
                                <img src="<?php echo e(Storage::url($tenant->logo_path)); ?>" alt="" class="h-10 w-10 shrink-0 rounded-xl border border-slate-200/80 object-cover dark:border-slate-700" />
                            <?php else: ?>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-slate-200 to-slate-300 text-xs font-bold text-slate-700 dark:from-slate-700 dark:to-slate-600 dark:text-white">
                                    <?php echo e(mb_strtoupper(mb_substr($tenant->company_name, 0, 2))); ?>

                                </span>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <a href="<?php echo e(route('tenants.show', $tenant)); ?>" class="block truncate font-semibold text-slate-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"><?php echo e($tenant->company_name); ?></a>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400"><?php echo e($tenant->domain ?? $tenant->tenant_domain ?? $tenant->hostedProject?->domain ?? '—'); ?></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $tenantVariant($tenant->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenantVariant($tenant->status))]); ?><?php echo e($tenant->status); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                <span class="text-[10px] text-slate-400"><?php echo e(__('Renews')); ?> <?php echo e($tenant->renewal_date?->diffForHumans() ?? '—'); ?></span>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('No tenants yet. Link each client company to a hosted project.')); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Revenue overview')); ?></h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Trailing six months')); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold tabular-nums text-slate-900 dark:text-white"><?php echo e($mrrLabel); ?></p>
                        <?php if($revenueGrowthPct !== null): ?>
                            <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">+<?php echo e(number_format($revenueGrowthPct, 1)); ?>%</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-4 flex-1">
                    <svg class="h-40 w-full" viewBox="0 0 320 120" preserveAspectRatio="none" aria-hidden="true">
                        <?php
                            $w = 320;
                            $h = 120;
                            $pad = 8;
                            $pts = [];
                            $n = count($revenueSeries);
                            foreach ($revenueSeries as $i => $row) {
                                $x = $pad + ($i / max(1, $n - 1)) * ($w - $pad * 2);
                                $v = (float) $row['value'];
                                $y = $h - $pad - ($v / $seriesMax) * ($h - $pad * 2);
                                $pts[] = round($x, 1).','.round($y, 1);
                            }
                            $line = implode(' ', $pts);
                            $poly = $pad.','.($h - $pad).' '.$line.' '.($w - $pad).','.($h - $pad);
                        ?>
                        <defs>
                            <linearGradient id="rvGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(99,102,241)" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="rgb(99,102,241)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <polygon points="<?php echo e($poly); ?>" fill="url(#rvGrad)" />
                        <polyline points="<?php echo e($line); ?>" fill="none" stroke="rgb(99,102,241)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                    </svg>
                    <div class="mt-2 flex justify-between text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        <?php $__currentLoopData = $revenueSeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span><?php echo e($row['label']); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="h-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Product revenue breakdown')); ?></h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('By hosted product / project')); ?></p>
                <ul class="mt-4 space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $productRevenue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li>
                            <div class="flex items-center justify-between gap-2 text-xs font-semibold text-slate-800 dark:text-slate-100">
                                <span class="truncate"><?php echo e($row['name']); ?></span>
                                <span class="shrink-0 tabular-nums text-slate-500 dark:text-slate-400">KES <?php echo e(number_format($row['amount'], 0)); ?></span>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500" style="width: <?php echo e(min(100, $row['pct'])); ?>%"></div>
                                </div>
                                <span class="text-[11px] font-semibold tabular-nums text-slate-500"><?php echo e($row['pct']); ?>%</span>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-6 text-center text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('Add monthly revenue on projects to populate this breakdown.')); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="h-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('System alerts')); ?></h2>
                <ul class="mt-3 space-y-3">
                    <?php $__currentLoopData = $systemAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $accent = match ($alert['type']) {
                                'critical', 'danger' => 'border-l-rose-500 bg-rose-50/60 dark:bg-rose-950/30',
                                'warning' => 'border-l-amber-500 bg-amber-50/60 dark:bg-amber-950/25',
                                'info' => 'border-l-sky-500 bg-sky-50/60 dark:bg-sky-950/25',
                                'success' => 'border-l-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/25',
                                default => 'border-l-slate-400 bg-slate-50 dark:bg-slate-800/40',
                            };
                        ?>
                        <li class="rounded-xl border border-slate-100/80 border-l-4 p-3 text-xs shadow-sm dark:border-slate-800/80 <?php echo e($accent); ?>">
                            <p class="font-semibold text-slate-900 dark:text-white"><?php echo e($alert['title']); ?></p>
                            <p class="mt-1 text-slate-600 dark:text-slate-300"><?php echo e($alert['body']); ?></p>
                            <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e($alert['time']); ?></p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-indigo-100/80 bg-gradient-to-br from-indigo-50/90 via-white to-violet-50/80 p-5 text-sm text-indigo-950 shadow-card dark:border-indigo-900/40 dark:from-indigo-950/50 dark:via-slate-900 dark:to-violet-950/40 dark:text-indigo-100">
        <div class="flex flex-wrap items-start gap-3">
            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
            </span>
            <div>
                <p class="font-semibold"><?php echo e(__('License API (hosted projects)')); ?></p>
                <p class="mt-1 text-indigo-900/90 dark:text-indigo-200/90"><?php echo e(__('Hosted products call')); ?>

                    <code class="rounded-md bg-white/80 px-1.5 py-0.5 text-xs font-mono text-indigo-900 shadow-sm dark:bg-slate-950 dark:text-indigo-100">POST <?php echo e(url('/api/v1/license/check')); ?></code>
                    <?php echo e(__('with Bearer token, tenant/product keys, domain, and HMAC signature.')); ?>

                </p>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>