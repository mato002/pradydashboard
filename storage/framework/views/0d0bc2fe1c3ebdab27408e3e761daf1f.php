<?php
    $statusVariant = fn (string $s): string => match ($s) {
        'active', 'live' => 'success',
        'maintenance', 'building', 'deploying' => 'warning',
        'suspended', 'failed' => 'danger',
        default => 'info',
    };

    $deployLabel = fn (string $s): string => match ($s) {
        'live' => __('Live'),
        'building' => __('Building'),
        'deploying' => __('Deploying'),
        'failed' => __('Failed'),
        default => ucfirst($s),
    };

    $ciColor = fn (string $s): string => match ($s) {
        'passed' => 'text-emerald-500',
        'running' => 'text-cyan-400 animate-pulse',
        'failed' => 'text-rose-500',
        default => 'text-amber-500',
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Hosted Projects'),'subheading' => __('Cloud product operations & deployment control')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted Projects')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Cloud product operations & deployment control'))]); ?>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                </span>
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-600 dark:text-cyan-400"><?php echo e(__('CI/CD connected')); ?></p>
            </div>
            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl text-slate-900 dark:text-white"><?php echo e(__('Hosted instances Center')); ?></h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('Manage SaaS products, environments, deployments, and infrastructure allocation')); ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" title="<?php echo e(__('Deploy all pending')); ?>">
                <svg class="h-4 w-4 text-cyan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                <?php echo e(__('Deploy')); ?>

            </button>
            <a href="<?php echo e(route('hosted-projects.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <?php echo e(__('Add hosted project')); ?>

            </a>
        </div>
    </div>

    
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Total Projects'),'value' => $kpis['total_projects'],'points' => $spark('projects'),'tone' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Total Projects')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['total_projects']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('projects')),'tone' => 'indigo']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15a2.25 2.25 0 012.25 2.25v.75m-18 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-18 0v-1.5A2.25 2.25 0 014.5 9h15a2.25 2.25 0 012.25 2.25v1.5" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Production Apps'),'value' => $kpis['production_apps'],'sublabel' => __('Live environments'),'points' => $spark('prod'),'tone' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production Apps')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['production_apps']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Live environments')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('prod')),'tone' => 'emerald']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active Deployments'),'value' => $kpis['active_deployments'],'sublabel' => __('Build &amp; deploy in flight'),'points' => $spark('deploy'),'tone' => 'sky']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active Deployments')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_deployments']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Build &amp; deploy in flight')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('deploy')),'tone' => 'sky']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Failed Deployments'),'value' => $kpis['failed_deployments'],'sublabel' => __('Requires rollback review'),'points' => $spark('failed'),'tone' => 'rose']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed Deployments')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['failed_deployments']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Requires rollback review')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('failed')),'tone' => 'rose']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 01-18 0zm-9 3.75h.008v.008H12v-.008z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active Tenants'),'value' => $kpis['active_tenants'],'sublabel' => __('Mapped to products'),'points' => $spark('tenants'),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active Tenants')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_tenants']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Mapped to products')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('tenants')),'tone' => 'violet']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Average Uptime'),'value' => $kpis['avg_uptime'].'%','animate' => false,'sublabel' => __('Rolling 30-day SLO'),'points' => $spark('uptime'),'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Average Uptime')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_uptime'].'%'),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Rolling 30-day SLO')),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($spark('uptime')),'tone' => 'amber']); ?>
             <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg> <?php $__env->endSlot(); ?>
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

    <?php if (isset($component)) { $__componentOriginal47ea7b45967a63ec6c3c6ac1618cc4ec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal47ea7b45967a63ec6c3c6ac1618cc4ec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.quick-links','data' => ['group' => 'control_plane','class' => 'mb-4 mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.quick-links'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['group' => 'control_plane','class' => 'mb-4 mt-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal47ea7b45967a63ec6c3c6ac1618cc4ec)): ?>
<?php $attributes = $__attributesOriginal47ea7b45967a63ec6c3c6ac1618cc4ec; ?>
<?php unset($__attributesOriginal47ea7b45967a63ec6c3c6ac1618cc4ec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal47ea7b45967a63ec6c3c6ac1618cc4ec)): ?>
<?php $component = $__componentOriginal47ea7b45967a63ec6c3c6ac1618cc4ec; ?>
<?php unset($__componentOriginal47ea7b45967a63ec6c3c6ac1618cc4ec); ?>
<?php endif; ?>

    <div class="grid gap-5 lg:grid-cols-12">
        
        <div class="space-y-4 lg:col-span-8">
            <?php if (isset($component)) { $__componentOriginalf96caf528f8b7f0a3799a7714984f886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf96caf528f8b7f0a3799a7714984f886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.list-toolbar','data' => ['searchValue' => $filters['q'] ?? '','searchPlaceholder' => ''.e(__('Search products, domains…')).'','exportHref' => route('hosted-projects.export', request()->query()),'resultCount' => $hostedProjects->total()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.list-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['q'] ?? ''),'search-placeholder' => ''.e(__('Search products, domains…')).'','export-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.export', request()->query())),'result-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hostedProjects->total())]); ?>
                <?php if (isset($component)) { $__componentOriginalae2b664bfbf39dde620003b48f024607 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae2b664bfbf39dde620003b48f024607 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-select','data' => ['name' => 'environment','placeholder' => __('Environment'),'value' => $filters['environment'] ?? '','options' => ['production' => __('Production'), 'staging' => __('Staging')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'environment','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Environment')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['environment'] ?? ''),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['production' => __('Production'), 'staging' => __('Staging')])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae2b664bfbf39dde620003b48f024607)): ?>
<?php $attributes = $__attributesOriginalae2b664bfbf39dde620003b48f024607; ?>
<?php unset($__attributesOriginalae2b664bfbf39dde620003b48f024607); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae2b664bfbf39dde620003b48f024607)): ?>
<?php $component = $__componentOriginalae2b664bfbf39dde620003b48f024607; ?>
<?php unset($__componentOriginalae2b664bfbf39dde620003b48f024607); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalae2b664bfbf39dde620003b48f024607 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae2b664bfbf39dde620003b48f024607 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-select','data' => ['name' => 'status','placeholder' => __('Status'),'value' => $filters['status'] ?? '','options' => ['active' => __('Active'), 'maintenance' => __('Maintenance'), 'suspended' => __('Suspended')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'status','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? ''),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['active' => __('Active'), 'maintenance' => __('Maintenance'), 'suspended' => __('Suspended')])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae2b664bfbf39dde620003b48f024607)): ?>
<?php $attributes = $__attributesOriginalae2b664bfbf39dde620003b48f024607; ?>
<?php unset($__attributesOriginalae2b664bfbf39dde620003b48f024607); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae2b664bfbf39dde620003b48f024607)): ?>
<?php $component = $__componentOriginalae2b664bfbf39dde620003b48f024607; ?>
<?php unset($__componentOriginalae2b664bfbf39dde620003b48f024607); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf96caf528f8b7f0a3799a7714984f886)): ?>
<?php $attributes = $__attributesOriginalf96caf528f8b7f0a3799a7714984f886; ?>
<?php unset($__attributesOriginalf96caf528f8b7f0a3799a7714984f886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf96caf528f8b7f0a3799a7714984f886)): ?>
<?php $component = $__componentOriginalf96caf528f8b7f0a3799a7714984f886; ?>
<?php unset($__componentOriginalf96caf528f8b7f0a3799a7714984f886); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Hosted products'),'actionHref' => route('hosted-projects.create'),'actionLabel' => __('Add')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted products')),'action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.create')),'action-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add'))]); ?>
                <table class="prady-table min-w-[1200px]">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Hosted project')); ?></th>
                            <th><?php echo e(__('Domain')); ?></th>
                            <th><?php echo e(__('Environment')); ?></th>
                            <th><?php echo e(__('Hosting Server')); ?></th>
                            <th class="text-right"><?php echo e(__('Tenants')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th><?php echo e(__('Uptime')); ?></th>
                            <th><?php echo e(__('Last Deployment')); ?></th>
                            <th><?php echo e(__('Version')); ?></th>
                            <th class="text-right"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $enrichedRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $hostedProject = $row['hostedProject']; ?>
                            <?php if (isset($component)) { $__componentOriginald8c3566cffa833a512e92beeb42a1003 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8c3566cffa833a512e92beeb42a1003 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.clickable-row','data' => ['href' => route('hosted-projects.show', $hostedProject),'class' => 'group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.clickable-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.show', $hostedProject)),'class' => 'group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30']); ?>
                                <td>
                                    <a href="<?php echo e(route('hosted-projects.show', $hostedProject)); ?>" class="flex items-center gap-2 font-semibold text-indigo-600 dark:text-indigo-400">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 text-xs font-bold text-indigo-700 dark:text-indigo-200"><?php echo e(mb_strtoupper(mb_substr($hostedProject->name, 0, 2))); ?></span>
                                        <?php echo e($hostedProject->name); ?>

                                    </a>
                                </td>
                                <td class="font-mono text-xs text-slate-600 dark:text-slate-300"><?php echo e($hostedProject->domain); ?></td>
                                <td>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1',
                                        'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300' => $row['environment'] === 'production',
                                        'bg-amber-500/10 text-amber-800 ring-amber-500/20 dark:text-amber-200' => $row['environment'] === 'staging',
                                    ]); ?>"><?php echo e($row['environment']); ?></span>
                                </td>
                                <td class="text-sm text-slate-600 dark:text-slate-300"><?php echo e($hostedProject->server?->name ?? '—'); ?></td>
                                <td class="text-right tabular-nums font-medium"><?php echo e($hostedProject->tenants_count); ?></td>
                                <td>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($hostedProject->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($hostedProject->status))]); ?><?php echo e($hostedProject->status); ?> <?php echo $__env->renderComponent(); ?>
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
                                    <div class="flex items-center gap-2 min-w-[5rem]">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width: <?php echo e(min(100, $row['uptime_pct'])); ?>%"></div>
                                        </div>
                                        <span class="text-[11px] tabular-nums font-semibold text-slate-600 dark:text-slate-300"><?php echo e(number_format($row['uptime_pct'], 1)); ?>%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-500">
                                    <span class="block font-medium text-slate-700 dark:text-slate-200"><?php echo e($row['last_deployment']['deployed_at']->diffForHumans()); ?></span>
                                    <span class="<?php echo e($ciColor($row['ci_status'])); ?>"><?php echo e($deployLabel($row['deploy_status'])); ?></span>
                                </td>
                                <td class="font-mono text-xs text-slate-600 dark:text-slate-300"><?php echo e($row['version']); ?></td>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('hosted-projects.show', $hostedProject)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.show', $hostedProject))]); ?><?php echo e(__('View')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('hosted-projects.edit', $hostedProject)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.edit', $hostedProject))]); ?><?php echo e(__('Edit')); ?> <?php echo $__env->renderComponent(); ?>
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
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8c3566cffa833a512e92beeb42a1003)): ?>
<?php $attributes = $__attributesOriginald8c3566cffa833a512e92beeb42a1003; ?>
<?php unset($__attributesOriginald8c3566cffa833a512e92beeb42a1003); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8c3566cffa833a512e92beeb42a1003)): ?>
<?php $component = $__componentOriginald8c3566cffa833a512e92beeb42a1003; ?>
<?php unset($__componentOriginald8c3566cffa833a512e92beeb42a1003); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="py-12 text-center text-sm text-slate-500"><?php echo e(__('No hosted projects yet. Add your first SaaS product.')); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                 <?php $__env->slot('footer', null, []); ?> <?php if (isset($component)) { $__componentOriginal2ec6395a4bcebbd8d30c3f248019c46d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ec6395a4bcebbd8d30c3f248019c46d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.pagination-bar','data' => ['paginator' => $hostedProjects]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.pagination-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hostedProjects)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ec6395a4bcebbd8d30c3f248019c46d)): ?>
<?php $attributes = $__attributesOriginal2ec6395a4bcebbd8d30c3f248019c46d; ?>
<?php unset($__attributesOriginal2ec6395a4bcebbd8d30c3f248019c46d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ec6395a4bcebbd8d30c3f248019c46d)): ?>
<?php $component = $__componentOriginal2ec6395a4bcebbd8d30c3f248019c46d; ?>
<?php unset($__componentOriginal2ec6395a4bcebbd8d30c3f248019c46d); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
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

        
        <div class="space-y-4 lg:col-span-4">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-900 to-slate-950 shadow-card dark:border-slate-700">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold text-white"><?php echo e(__('Deployment center')); ?></h3>
                    <p class="text-[11px] text-slate-400"><?php echo e(__('Recent pipeline activity')); ?></p>
                </div>
                <ul class="max-h-80 divide-y divide-white/5 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $recentDeployments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold text-white">
                                        <a href="<?php echo e(route('hosted-projects.show', $dep['project_id'])); ?>" class="hover:text-cyan-300"><?php echo e($dep['project'] ?? '—'); ?></a>
                                    </p>
                                    <p class="mt-0.5 font-mono text-[10px] text-cyan-400/90"><?php echo e($dep['version']); ?></p>
                                </div>
                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $dep['status'] === 'success' ? 'success' : 'danger','class' => '!text-[9px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dep['status'] === 'success' ? 'success' : 'danger'),'class' => '!text-[9px]']); ?><?php echo e($dep['status']); ?> <?php echo $__env->renderComponent(); ?>
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
                            <p class="mt-1.5 text-[10px] text-slate-500"><?php echo e($dep['triggered_by']); ?> · <?php echo e($dep['deployed_at']->diffForHumans()); ?> · <?php echo e($dep['duration_sec']); ?>s</p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="px-4 py-6 text-center text-[11px] text-slate-500"><?php echo e(__('No deployments recorded yet.')); ?></li>
                    <?php endif; ?>
                </ul>
                <div class="border-t border-white/10 px-4 py-3">
                    <div class="flex gap-2">
                        <button type="button" class="flex-1 rounded-lg bg-cyan-600/20 py-2 text-[11px] font-semibold text-cyan-300 ring-1 ring-cyan-500/30"><?php echo e(__('Rollback')); ?></button>
                        <button type="button" class="flex-1 rounded-lg bg-white/5 py-2 text-[11px] font-semibold text-slate-300 ring-1 ring-white/10"><?php echo e(__('View logs')); ?></button>
                    </div>
                </div>
            </div>

            
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Fleet health')); ?></h3>
                <ul class="mt-3 space-y-3 text-xs">
                    <?php $__currentLoopData = $enrichedRows->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <div class="flex justify-between font-semibold text-slate-700 dark:text-slate-200">
                                <span class="truncate"><?php echo e($row['hostedProject']->name); ?></span>
                                <span class="tabular-nums text-slate-500"><?php echo e($row['response_ms']); ?>ms</span>
                            </div>
                            <div class="mt-1 flex gap-2 text-[10px] text-slate-500">
                                <span><?php echo e(__('Errors')); ?> <?php echo e($row['error_rate']); ?>%</span>
                                <span>·</span>
                                <span><?php echo e(__('SSL')); ?> <?php echo e($row['ssl_health']); ?></span>
                                <span>·</span>
                                <span><?php echo e($row['bandwidth_gb']); ?> GB</span>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>

    
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Infrastructure mapping')); ?></h3>
            <p class="text-xs text-slate-500"><?php echo e(__('Server allocation · staging vs production · scaling')); ?></p>
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php $__empty_1 = true; $__currentLoopData = $infrastructure; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-900 dark:text-white"><?php echo e($node['server']); ?></span>
                        <span class="h-2 w-2 rounded-full <?php echo e($node['status'] === 'online' ? 'bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.6)]' : 'bg-rose-500'); ?>"></span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e(__(':count projects', ['count' => $node['projects']])); ?> · <?php echo e($node['production']); ?> prod / <?php echo e($node['staging']); ?> stg</p>
                    <div class="mt-3 space-y-2">
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500"><span>CPU</span><span><?php echo e($node['cpu_pct']); ?>%</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-cyan-500" style="width: <?php echo e($node['cpu_pct']); ?>%"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500"><span><?php echo e(__('Storage')); ?></span><span><?php echo e($node['storage_pct']); ?>%</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-indigo-500" style="width: <?php echo e($node['storage_pct']); ?>%"></div></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="col-span-full py-6 text-center text-sm text-slate-500"><?php echo e(__('Add servers to map infrastructure to hosted-projects.')); ?></p>
            <?php endif; ?>
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

<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/hosted-projects/index.blade.php ENDPATH**/ ?>