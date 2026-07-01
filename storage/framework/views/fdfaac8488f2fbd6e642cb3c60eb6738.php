<?php
    $priorityVariant = fn (string $p): string => match ($p) {
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        default => 'neutral',
    };

    $statusVariant = fn (string $s): string => match ($s) {
        'open' => 'info',
        'pending' => 'neutral',
        'escalated' => 'danger',
        'in_progress' => 'warning',
        'resolved' => 'success',
        'closed' => 'neutral',
        default => 'neutral',
    };

    $slaVariant = fn (string $s): string => match ($s) {
        'breached' => 'danger',
        'at_risk' => 'warning',
        default => 'success',
    };

    $incidentTypeLabel = fn (string $t): string => match ($t) {
        'server_outage' => __('Server outage'),
        'ssl_failure' => __('SSL failure'),
        'backup_failure' => __('Backup failure'),
        'api_downtime' => __('API downtime'),
        'deployment_failure' => __('Deployment failure'),
        default => ucfirst(str_replace('_', ' ', $t)),
    };

    $resolutionMax = max(collect($analytics['resolution_trend'])->max('value') ?? 1, 1);
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Support & Incidents'),'subheading' => __('Enterprise customer support operations center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Support & Incidents')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Enterprise customer support operations center'))]); ?>
    <div
        x-data="supportCenter(<?php echo \Illuminate\Support\Js::from($tickets)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($incidents)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($conversations)->toHtml() ?>)"
        class="space-y-6"
    >
        
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Operations')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Customer Support & Incident Management')); ?></h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400"><?php echo e(__('Tenant tickets, infrastructure incidents, SLA compliance, assignments, and resolution analytics.')); ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <?php echo e(__('Live queue')); ?>

                </span>
                <a href="<?php echo e(route('support-tickets.create')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <?php echo e(__('New ticket')); ?>

                </a>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-orange-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-rose-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    <?php echo e(__('Declare incident')); ?>

                </button>
            </div>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Open tickets'),'value' => $kpis['open_tickets']['value'],'trend' => $kpis['open_tickets']['trend'],'sublabel' => $kpis['open_tickets']['sublabel'],'points' => $kpis['open_tickets']['points'],'tone' => $kpis['open_tickets']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Open tickets')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['open_tickets']['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['open_tickets']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['open_tickets']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['open_tickets']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['open_tickets']['tone'])]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Critical incidents'),'value' => $kpis['critical_incidents']['value'],'trend' => $kpis['critical_incidents']['trend'],'sublabel' => $kpis['critical_incidents']['sublabel'],'points' => $kpis['critical_incidents']['points'],'tone' => $kpis['critical_incidents']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Critical incidents')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['critical_incidents']['tone'])]); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('SLA breaches'),'value' => $kpis['sla_breaches']['value'],'trend' => $kpis['sla_breaches']['trend'],'sublabel' => $kpis['sla_breaches']['sublabel'],'points' => $kpis['sla_breaches']['points'],'tone' => $kpis['sla_breaches']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SLA breaches')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['sla_breaches']['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['sla_breaches']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['sla_breaches']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['sla_breaches']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['sla_breaches']['tone'])]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Avg resolution'),'value' => $kpis['avg_resolution']['value'],'animate' => false,'trend' => $kpis['avg_resolution']['trend'],'sublabel' => $kpis['avg_resolution']['sublabel'],'points' => $kpis['avg_resolution']['points'],'tone' => $kpis['avg_resolution']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Avg resolution')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_resolution']['value']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_resolution']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_resolution']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_resolution']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['avg_resolution']['tone'])]); ?>
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
            <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Active agents'),'value' => $kpis['active_agents']['value'],'trend' => $kpis['active_agents']['trend'],'sublabel' => $kpis['active_agents']['sublabel'],'points' => $kpis['active_agents']['points'],'tone' => $kpis['active_agents']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active agents')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_agents']['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_agents']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_agents']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_agents']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['active_agents']['tone'])]); ?>
                 <?php $__env->slot('icon', null, []); ?> <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg> <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => __('Resolved today'),'value' => $kpis['resolved_today']['value'],'trend' => $kpis['resolved_today']['trend'],'sublabel' => $kpis['resolved_today']['sublabel'],'points' => $kpis['resolved_today']['points'],'tone' => $kpis['resolved_today']['tone']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Resolved today')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['resolved_today']['value']),'trend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['resolved_today']['trend']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['resolved_today']['sublabel']),'points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['resolved_today']['points']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpis['resolved_today']['tone'])]); ?>
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
        </div>

        
        <div class="grid gap-5 xl:grid-cols-12">
            
            <div class="xl:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Ticket queue')); ?></h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="filteredTickets.length + ' <?php echo e(__('tickets')); ?>'"></p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value=""><?php echo e(__('All statuses')); ?></option>
                                <option value="open"><?php echo e(__('Open')); ?></option>
                                <option value="pending"><?php echo e(__('Pending')); ?></option>
                                <option value="escalated"><?php echo e(__('Escalated')); ?></option>
                                <option value="in_progress"><?php echo e(__('In Progress')); ?></option>
                                <option value="resolved"><?php echo e(__('Resolved')); ?></option>
                                <option value="closed"><?php echo e(__('Closed')); ?></option>
                            </select>
                            <select x-model="filterPriority" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value=""><?php echo e(__('All priorities')); ?></option>
                                <option value="critical"><?php echo e(__('Critical')); ?></option>
                                <option value="high"><?php echo e(__('High')); ?></option>
                                <option value="medium"><?php echo e(__('Medium')); ?></option>
                                <option value="low"><?php echo e(__('Low')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table w-full min-w-[900px]">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Ticket ID')); ?></th>
                                    <th><?php echo e(__('Tenant')); ?></th>
                                    <th><?php echo e(__('Subject')); ?></th>
                                    <th><?php echo e(__('Category')); ?></th>
                                    <th><?php echo e(__('Priority')); ?></th>
                                    <th><?php echo e(__('Assigned To')); ?></th>
                                    <th><?php echo e(__('SLA')); ?></th>
                                    <th><?php echo e(__('Last Response')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <template x-for="ticket in filteredTickets" :key="ticket.id">
                                    <tr
                                        @click="selectTicket(ticket)"
                                        class="cursor-pointer transition hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                                        :class="selectedTicket?.id === ticket.id ? 'bg-indigo-50/80 dark:bg-indigo-500/10' : ''"
                                    >
                                        <td class="font-mono text-xs font-semibold">
                                            <a
                                                :href="'<?php echo e(url('support-tickets')); ?>/' + (ticket.public_id ?? ticket.db_id ?? ticket.id)"
                                                @click.stop
                                                class="text-indigo-600 hover:underline dark:text-indigo-400"
                                                x-text="ticket.id"
                                            ></a>
                                        </td>
                                        <td class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="ticket.tenant"></td>
                                        <td class="max-w-[200px] truncate text-sm text-slate-700 dark:text-slate-300" x-text="ticket.subject" :title="ticket.subject"></td>
                                        <td><span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300" x-text="ticket.category"></span></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                                                :class="{
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20 dark:text-rose-300': ticket.priority === 'critical',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20 dark:text-amber-200': ticket.priority === 'high',
                                                    'bg-sky-500/12 text-sky-800 ring-sky-500/20 dark:text-sky-200': ticket.priority === 'medium',
                                                    'bg-slate-500/10 text-slate-600 ring-slate-500/15 dark:text-slate-300': ticket.priority === 'low',
                                                }"
                                                x-text="ticket.priority"
                                            ></span>
                                        </td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400" x-text="ticket.assigned_to"></td>
                                        <td>
                                            <div class="flex min-w-[80px] flex-col gap-1">
                                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                    <div
                                                        class="h-full rounded-full transition-all"
                                                        :class="{
                                                            'bg-rose-500': ticket.sla_status === 'breached',
                                                            'bg-amber-500': ticket.sla_status === 'at_risk',
                                                            'bg-emerald-500': ticket.sla_status === 'on_track',
                                                        }"
                                                        :style="'width:' + ticket.sla_progress + '%'"
                                                    ></div>
                                                </div>
                                                <span class="text-[10px] font-semibold uppercase" :class="{
                                                    'text-rose-600 dark:text-rose-400': ticket.sla_status === 'breached',
                                                    'text-amber-600 dark:text-amber-400': ticket.sla_status === 'at_risk',
                                                    'text-emerald-600 dark:text-emerald-400': ticket.sla_status === 'on_track',
                                                }" x-text="ticket.sla_status.replace('_', ' ')"></span>
                                            </div>
                                        </td>
                                        <td class="text-xs text-slate-500 dark:text-slate-400" x-text="ticket.last_response"></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1 ring-inset"
                                                :class="{
                                                    'bg-sky-500/12 text-sky-800 ring-sky-500/20': ticket.status === 'open',
                                                    'bg-slate-500/10 text-slate-600 ring-slate-500/15': ticket.status === 'pending' || ticket.status === 'closed',
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20': ticket.status === 'escalated',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20': ticket.status === 'in_progress',
                                                    'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': ticket.status === 'resolved',
                                                }"
                                                x-text="ticket.status.replace('_', ' ')"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Conversation')); ?></h3>
                            <p class="text-xs text-slate-500" x-show="selectedTicket" x-text="selectedTicket?.id + ' — ' + selectedTicket?.subject"></p>
                            <p class="text-xs text-slate-500" x-show="!selectedTicket"><?php echo e(__('Select a ticket to view thread')); ?></p>
                        </div>
                        <div class="flex gap-1 rounded-lg bg-slate-100 p-0.5 dark:bg-slate-800">
                            <button type="button" @click="convTab = 'thread'" :class="convTab === 'thread' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300"><?php echo e(__('Thread')); ?></button>
                            <button type="button" @click="convTab = 'internal'" :class="convTab === 'internal' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300"><?php echo e(__('Internal')); ?></button>
                            <button type="button" @click="convTab = 'audit'" :class="convTab === 'audit' ? 'bg-white shadow dark:bg-slate-700' : ''" class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition dark:text-slate-300"><?php echo e(__('Audit')); ?></button>
                        </div>
                    </div>
                    <div class="max-h-80 space-y-3 overflow-y-auto p-4 prady-scrollbar">
                        <template x-if="!selectedTicket">
                            <p class="py-8 text-center text-sm text-slate-500"><?php echo e(__('Click any ticket row to load communication history.')); ?></p>
                        </template>
                        <template x-for="msg in activeMessages" :key="msg.time + msg.author">
                            <div
                                class="flex gap-3"
                                :class="msg.type === 'customer' ? '' : msg.type === 'internal' ? 'opacity-90' : ''"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                                    :class="{
                                        'bg-indigo-500': msg.type === 'customer',
                                        'bg-violet-500': msg.type === 'agent',
                                        'bg-amber-500': msg.type === 'internal',
                                        'bg-slate-500': msg.type === 'system',
                                    }"
                                    x-text="msg.author.charAt(0)"
                                ></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200" x-text="msg.author"></span>
                                        <span class="text-[10px] text-slate-400" x-text="msg.time"></span>
                                        <span x-show="msg.type === 'internal'" class="rounded bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-bold uppercase text-amber-700 dark:text-amber-300"><?php echo e(__('Internal')); ?></span>
                                    </div>
                                    <p class="mt-1 rounded-xl rounded-tl-sm bg-slate-100 px-3 py-2 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300" x-text="msg.body"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedTicket && activeMessages.length === 0">
                            <p class="py-6 text-center text-sm text-slate-500"><?php echo e(__('No messages in this view.')); ?></p>
                        </template>
                    </div>
                    <div class="border-t border-slate-200/80 p-3 dark:border-slate-800/80">
                        <div class="flex gap-2">
                            <input type="text" placeholder="<?php echo e(__('Reply to customer…')); ?>" class="flex-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" disabled />
                            <button type="button" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white opacity-60" disabled><?php echo e(__('Send')); ?></button>
                        </div>
                        <p class="mt-2 flex flex-wrap gap-3 text-[10px] text-slate-400">
                            <span>📎 <?php echo e(__('Attachments')); ?></span>
                            <span>📝 <?php echo e(__('Canned responses')); ?></span>
                            <span>🔒 <?php echo e(__('Internal note')); ?></span>
                        </p>
                    </div>
                </div>
            </div>

            
            <div class="xl:col-span-4 space-y-4">
                
                <div class="overflow-hidden rounded-2xl border border-rose-200/60 bg-gradient-to-b from-rose-50/80 to-white shadow-card dark:border-rose-500/20 dark:from-rose-950/30 dark:to-slate-900/60">
                    <div class="flex items-center justify-between border-b border-rose-200/50 px-4 py-3 dark:border-rose-500/20">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-rose-900 dark:text-rose-200">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg>
                            <?php echo e(__('Critical incidents')); ?>

                        </h3>
                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300"><?php echo e(count($incidents)); ?> <?php echo e(__('active')); ?></span>
                    </div>
                    <div class="max-h-[420px] space-y-3 overflow-y-auto p-3 prady-scrollbar">
                        <?php $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                x-data="{ open: <?php echo e($loop->first ? 'true' : 'false'); ?> }"
                                class="rounded-xl border border-slate-200/80 bg-white/90 p-3 dark:border-slate-700 dark:bg-slate-900/80"
                            >
                                <button type="button" @click="open = !open" class="w-full text-left">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-mono text-[10px] font-bold text-rose-600 dark:text-rose-400"><?php echo e($incident['id']); ?></p>
                                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($incident['title']); ?></p>
                                            <p class="mt-1 text-[11px] text-slate-500"><?php echo e($incidentTypeLabel($incident['type'])); ?> · <?php echo e($incident['started']); ?></p>
                                        </div>
                                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $incident['severity'] === 'critical' ? 'danger' : ($incident['severity'] === 'high' ? 'warning' : 'info')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($incident['severity'] === 'critical' ? 'danger' : ($incident['severity'] === 'high' ? 'warning' : 'info'))]); ?>
                                            <?php echo e(ucfirst($incident['severity'])); ?>

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
                                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                        <div>
                                            <p class="text-lg font-bold text-slate-900 dark:text-white"><?php echo e($incident['affected_tenants']); ?></p>
                                            <p class="text-[10px] uppercase text-slate-500"><?php echo e(__('Tenants')); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-bold text-amber-600"><?php echo e($incident['escalation_level']); ?></p>
                                            <p class="text-[10px] uppercase text-slate-500"><?php echo e(__('Escalation')); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-bold text-emerald-600"><?php echo e($incident['recovery']); ?>%</p>
                                            <p class="text-[10px] uppercase text-slate-500"><?php echo e(__('Recovery')); ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                        <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-emerald-500" style="width: <?php echo e($incident['recovery']); ?>%"></div>
                                    </div>
                                </button>
                                <div x-show="open" x-transition class="mt-3 border-t border-slate-200/80 pt-3 dark:border-slate-700">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Timeline')); ?></p>
                                    <ul class="space-y-2">
                                        <?php $__currentLoopData = $incident['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="flex gap-2 text-xs">
                                                <span class="shrink-0 font-mono text-slate-400"><?php echo e($event['time']); ?></span>
                                                <span class="text-slate-600 dark:text-slate-400"><?php echo e($event['event']); ?> <span class="text-slate-400">— <?php echo e($event['actor']); ?></span></span>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('SLA management')); ?></h3>
                        <p class="text-xs text-slate-500"><?php echo e(__('Compliance')); ?>: <span class="font-semibold text-emerald-600"><?php echo e($slaOverview['compliance_pct']); ?>%</span></p>
                    </div>
                    <div class="space-y-4 p-4">
                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo e($slaOverview['response_target']); ?></p>
                                <p class="text-slate-500"><?php echo e(__('Response target')); ?></p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo e($slaOverview['resolution_target']); ?></p>
                                <p class="text-slate-500"><?php echo e(__('Resolution target')); ?></p>
                            </div>
                        </div>
                        <?php $__currentLoopData = $slaOverview['timers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <div class="mb-1 flex justify-between text-xs">
                                    <span class="font-medium text-slate-700 dark:text-slate-300"><?php echo e($timer['label']); ?></span>
                                    <span class="font-mono font-semibold" :class="'<?php echo e($timer['status']); ?>' === 'at_risk' ? 'text-amber-600' : 'text-emerald-600'"><?php echo e($timer['remaining']); ?></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'h-full rounded-full',
                                        'bg-amber-500' => $timer['status'] === 'at_risk',
                                        'bg-emerald-500' => $timer['status'] === 'on_track',
                                    ]); ?>" style="width: <?php echo e($timer['pct']); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('At risk / overdue')); ?></p>
                        <ul class="space-y-1.5">
                            <?php $__currentLoopData = $slaOverview['overdue']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between rounded-lg bg-rose-50/50 px-2 py-1.5 text-xs dark:bg-rose-500/10">
                                    <span class="font-mono text-rose-700 dark:text-rose-300"><?php echo e($ticket['id']); ?></span>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $slaVariant($ticket['sla_status'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($slaVariant($ticket['sla_status']))]); ?><?php echo e(str_replace('_', ' ', $ticket['sla_status'])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>

                
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Agent performance')); ?></h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-3 px-4 py-3">
                                <div class="relative">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                                        <?php echo e(strtoupper(substr($agent['name'], 0, 1))); ?>

                                    </div>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-slate-900',
                                        'bg-emerald-500' => $agent['status'] === 'online',
                                        'bg-amber-400' => $agent['status'] === 'away',
                                    ]); ?>"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($agent['name']); ?></p>
                                    <p class="text-[11px] text-slate-500"><?php echo e($agent['role']); ?></p>
                                </div>
                                <div class="text-right text-xs">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($agent['tickets']); ?> <?php echo e(__('open')); ?></p>
                                    <p class="text-emerald-600"><?php echo e($agent['sla_pct']); ?>% SLA</p>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>

        
        <div class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Support analytics')); ?></h3>
                    </div>
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Resolution trend (7d)')); ?></p>
                            <div class="flex h-32 items-end gap-1">
                                <?php $__currentLoopData = $analytics['resolution_trend']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div
                                            class="w-full rounded-t bg-gradient-to-t from-indigo-600 to-violet-500 transition-all hover:opacity-80"
                                            style="height: <?php echo e(max(8, ($bar['value'] / $resolutionMax) * 100)); ?>%"
                                            title="<?php echo e($bar['value']); ?>"
                                        ></div>
                                        <span class="text-[10px] text-slate-500"><?php echo e($bar['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('First response distribution')); ?></p>
                            <?php $__currentLoopData = $analytics['response_times']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="mb-2">
                                    <div class="mb-0.5 flex justify-between text-xs">
                                        <span class="text-slate-600 dark:text-slate-400"><?php echo e($rt['label']); ?></span>
                                        <span class="font-semibold tabular-nums"><?php echo e($rt['pct']); ?>%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                        <div class="h-full rounded-full bg-sky-500" style="width: <?php echo e($rt['pct']); ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="mt-4 flex items-center gap-2 rounded-xl bg-violet-50 p-3 dark:bg-violet-500/10">
                                <span class="text-2xl">⭐</span>
                                <div>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white"><?php echo e($analytics['satisfaction']); ?>/5</p>
                                    <p class="text-xs text-slate-500"><?php echo e(__('Tenant satisfaction (CSAT)')); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Issue categories')); ?></p>
                            <div class="space-y-2">
                                <?php $__currentLoopData = $analytics['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-3">
                                        <span class="w-28 shrink-0 text-xs font-medium text-slate-700 dark:text-slate-300"><?php echo e($cat['name']); ?></span>
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-full rounded-full bg-indigo-500" style="width: <?php echo e($cat['pct']); ?>%"></div>
                                        </div>
                                        <span class="w-8 text-right text-xs font-semibold tabular-nums text-slate-600"><?php echo e($cat['count']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div class="sm:col-span-2 border-t border-slate-200/80 pt-4 dark:border-slate-800/80">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500"><?php echo e(__('Recurring incidents')); ?></p>
                            <div class="flex flex-wrap gap-2">
                                <?php $__currentLoopData = $analytics['recurring']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        <?php echo e($rec['issue']); ?>

                                        <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold dark:bg-slate-700"><?php echo e($rec['count']); ?></span>
                                        <?php if($rec['trend'] === 'up'): ?>
                                            <span class="text-rose-500">↑</span>
                                        <?php elseif($rec['trend'] === 'down'): ?>
                                            <span class="text-emerald-500">↓</span>
                                        <?php else: ?>
                                            <span class="text-slate-400">→</span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Automation & routing')); ?></h3>
                        <p class="text-xs text-slate-500"><?php echo e(__('Rules engine — operational')); ?></p>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <?php $__currentLoopData = $automation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-3 px-4 py-3">
                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                    'bg-emerald-500/15 text-emerald-600' => $rule['enabled'],
                                    'bg-slate-500/10 text-slate-400' => ! $rule['enabled'],
                                ]); ?>">
                                    <?php if($rule['enabled']): ?>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <?php else: ?>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($rule['name']); ?></p>
                                        <span class="shrink-0 rounded-full bg-indigo-500/10 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-300"><?php echo e(number_format($rule['runs'])); ?></span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><?php echo e($rule['description']); ?></p>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/support-tickets/index.blade.php ENDPATH**/ ?>