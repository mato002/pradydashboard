<?php
    use Illuminate\Support\Str;

    $statusVariant = fn (string $s): string => match ($s) {
        'active', 'live', 'success' => 'success',
        'maintenance', 'building', 'deploying', 'pending' => 'warning',
        'suspended', 'failed' => 'danger',
        default => 'info',
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $project->name,'subheading' => $project->domain]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->name),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->domain)]); ?>
    <?php if(session('status')): ?>
        <div class="mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-lg font-bold text-white shadow-lg shadow-cyan-500/30">
                <?php echo e(mb_strtoupper(mb_substr($project->name, 0, 2))); ?>

            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($project->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($project->status))]); ?><?php echo e($project->status); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300"><?php echo e($meta['environment']); ?></span>
                    <span class="font-mono text-xs text-slate-500"><?php echo e($meta['version']); ?></span>
                </div>
                <p class="mt-1 text-sm text-slate-500"><?php echo e($project->server?->name ?? __('Unassigned server')); ?> · <?php echo e($project->tenants->count()); ?> <?php echo e(__('tenants')); ?></p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-500/25">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                <?php echo e(__('Deploy')); ?>

            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><?php echo e(__('Rollback')); ?></button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><?php echo e(__('Restart')); ?></button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><?php echo e(__('View Logs')); ?></button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-violet-200/80 bg-violet-50 px-4 py-2 text-xs font-semibold text-violet-800 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-200"><?php echo e(__('Scale')); ?></button>
            <a href="<?php echo e(route('hosted-projects.edit', $hostedProject)); ?>" class="inline-flex items-center rounded-xl border border-slate-200/80 px-4 py-2 text-xs font-semibold dark:border-slate-700"><?php echo e(__('Edit')); ?></a>
        </div>
    </div>

    
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <?php $__currentLoopData = [
            ['label' => __('Uptime'), 'value' => number_format($meta['uptime_pct'], 2).'%', 'tone' => 'emerald'],
            ['label' => __('Response'), 'value' => $meta['response_ms'].'ms', 'tone' => 'sky'],
            ['label' => __('Error rate'), 'value' => $meta['error_rate'].'%', 'tone' => 'rose'],
            ['label' => __('SSL'), 'value' => ucfirst($meta['ssl_health']), 'tone' => 'indigo'],
            ['label' => __('Bandwidth'), 'value' => $meta['bandwidth_gb'].' GB', 'tone' => 'violet'],
            ['label' => __('Storage'), 'value' => $meta['storage_pct'].'%', 'tone' => 'amber'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e($metric['label']); ?></p>
                <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white"><?php echo e($metric['value']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-12">
        
        <div class="space-y-5 lg:col-span-7">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Deployment pipeline')); ?></h3>
                </div>
                <div class="flex items-center gap-2 px-4 py-6">
                    <?php $__currentLoopData = $pipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold ring-2',
                                'bg-cyan-500 text-white ring-cyan-400 shadow-lg shadow-cyan-500/40 animate-pulse' => $stage['status'] === 'active',
                                'bg-emerald-500 text-white ring-emerald-400' => $stage['status'] === 'done',
                                'bg-rose-500 text-white ring-rose-400' => $stage['status'] === 'failed',
                                'bg-slate-100 text-slate-400 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700' => $stage['status'] === 'pending',
                            ]); ?>"><?php echo e($i + 1); ?></div>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($stage['label']); ?></span>
                        </div>
                        <?php if(! $loop->last): ?>
                            <div class="h-0.5 flex-1 rounded bg-slate-200 dark:bg-slate-700"></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Deployment history')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Deployment history'))]); ?>
                <table class="prady-table">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Version')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th><?php echo e(__('Environment')); ?></th>
                            <th><?php echo e(__('Duration')); ?></th>
                            <th><?php echo e(__('Deployed')); ?></th>
                            <th><?php echo e(__('By')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $deploymentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-href="<?php echo e(route('deployments.index')); ?>">
                                <td class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e($dep['version']); ?></td>
                                <td><?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $dep['status'] === 'success' ? 'success' : 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dep['status'] === 'success' ? 'success' : 'danger')]); ?><?php echo e($dep['status']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?></td>
                                <td class="capitalize text-xs"><?php echo e($dep['environment']); ?></td>
                                <td class="tabular-nums text-xs"><?php echo e($dep['duration_sec']); ?>s</td>
                                <td class="text-xs text-slate-500"><?php echo e($dep['deployed_at']->diffForHumans()); ?></td>
                                <td class="text-xs text-slate-500"><?php echo e($dep['triggered_by']); ?></td>
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

            <div class="overflow-hidden rounded-2xl border border-slate-950 bg-slate-950 shadow-card">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-2">
                    <span class="text-xs font-semibold text-slate-300"><?php echo e(__('Build logs')); ?></span>
                    <span class="font-mono text-[10px] text-cyan-400"><?php echo e($meta['version']); ?></span>
                </div>
                <pre class="max-h-48 overflow-auto p-4 font-mono text-[11px] leading-relaxed text-emerald-400/90"><?php $__currentLoopData = $buildLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($line); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></pre>
            </div>
        </div>

        
        <div class="space-y-5 lg:col-span-5">
            <?php echo $__env->make('admin.hosted-projects.partials.integration-kit', ['integrationKit' => $integrationKit], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('DNS & SSL')); ?></h3>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">A</dt><dd class="font-mono text-slate-700 dark:text-slate-200"><?php echo e($project->server?->ip_address ?? '—'); ?></dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">CNAME</dt><dd class="font-mono"><?php echo e($project->domain); ?></dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('SSL')); ?></dt><dd class="font-semibold capitalize"><?php echo e($meta['ssl_health']); ?></dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Environment variables')); ?></h3>
                <ul class="mt-3 space-y-2">
                    <?php $__currentLoopData = $envVars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2 font-mono text-[11px] dark:bg-slate-800/80">
                            <span class="text-cyan-600 dark:text-cyan-400"><?php echo e($var['key']); ?></span>
                            <span class="truncate text-slate-600 dark:text-slate-300"><?php echo e($var['value']); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <form method="post" action="<?php echo e(route('hosted-projects.regenerate-token', $hostedProject)); ?>" class="flex items-center justify-between gap-2" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Regenerate API token? Existing product .env must be updated.'))->toHtml() ?>);">
                    <?php echo csrf_field(); ?>
                    <p class="text-[11px] text-slate-500"><?php echo e(__('Invalidates the current project API token.')); ?></p>
                    <button type="submit" class="shrink-0 text-xs font-semibold text-rose-600 dark:text-rose-400"><?php echo e(__('Regenerate token')); ?></button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Active tenants')); ?></h3>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <?php $__empty_1 = true; $__currentLoopData = $project->tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="flex items-center justify-between px-4 py-3 text-sm">
                            <a href="<?php echo e(route('tenants.show', $tenant)); ?>" class="font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($tenant->company_name); ?></a>
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant($tenant->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant($tenant->status))]); ?><?php echo e($tenant->status); ?> <?php echo $__env->renderComponent(); ?>
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
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="px-4 py-4 text-sm text-slate-500">
                            <?php echo e(__('No tenants linked.')); ?>

                            <a href="<?php echo e($integrationKit['create_tenant_url'] ?? route('tenants.create')); ?>" class="mt-1 block font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e(__('Add tenant for license keys')); ?> →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php if($project->technology_stack || $project->notes): ?>
        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <?php if($project->technology_stack): ?>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold"><?php echo e(__('Technology stack')); ?></h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300"><?php echo e($project->technology_stack); ?></p>
                </div>
            <?php endif; ?>
            <?php if($project->notes): ?>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold"><?php echo e(__('Notes')); ?></h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-600 dark:text-slate-300"><?php echo e($project->notes); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/hosted-projects/show.blade.php ENDPATH**/ ?>