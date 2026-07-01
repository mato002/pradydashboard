<div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="flex min-w-0 items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white shadow-lg shadow-indigo-500/25">
            <?php echo e(mb_strtoupper(mb_substr($tenant->company_name, 0, 2))); ?>

        </span>
        <div class="min-w-0">
            <p class="truncate text-sm text-slate-500 dark:text-slate-400">
                <?php echo e($tenant->project?->name); ?>

                <?php if($tenant->project?->domain): ?>
                    <span class="text-slate-400">·</span> <?php echo e($tenant->project->domain); ?>

                <?php endif; ?>
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' => $tenant->status === 'active',
                    'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100' => in_array($tenant->status, ['trial', 'warning', 'overdue'], true),
                    'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200' => in_array($tenant->status, ['suspended', 'restricted', 'terminated'], true),
                    'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' => $tenant->status === 'cancelled',
                ]); ?>"><?php echo e(str_replace('_', ' ', $tenant->status)); ?></span>
                <?php if($tenant->alerts->whereNull('dismissed_at')->isNotEmpty()): ?>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200">
                        <?php echo e(__('Open alerts')); ?>: <?php echo e($tenant->alerts->whereNull('dismissed_at')->count()); ?>

                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="flex shrink-0 flex-wrap gap-2">
        <a
            href="<?php echo e(route('tenants.edit', $tenant).'?return_tab='.urlencode($tab)); ?>"
            data-tenant-full-nav
            class="inline-flex items-center rounded-xl border border-slate-200/80 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        ><?php echo e(__('Edit tenant')); ?></a>
        <form method="post" action="<?php echo e(route('tenants.destroy', $tenant)); ?>" onsubmit="return confirm(<?php echo json_encode(__('Delete this tenant?'), 15, 512) ?>);">
            <?php echo csrf_field(); ?>
            <?php echo method_field('delete'); ?>
            <button type="submit" class="inline-flex items-center rounded-xl border border-rose-200/80 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200"><?php echo e(__('Delete')); ?></button>
        </form>
    </div>
</div>

<?php echo $__env->make('admin.tenants.partials.integration-credentials', ['tenant' => $tenant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if (isset($component)) { $__componentOriginal7408c88f8f69ac708d2acdd799a27d40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7408c88f8f69ac708d2acdd799a27d40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-cards','data' => ['risks' => $operationalRisks,'class' => 'mb-4','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-cards'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['risks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($operationalRisks),'class' => 'mb-4','compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7408c88f8f69ac708d2acdd799a27d40)): ?>
<?php $attributes = $__attributesOriginal7408c88f8f69ac708d2acdd799a27d40; ?>
<?php unset($__attributesOriginal7408c88f8f69ac708d2acdd799a27d40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7408c88f8f69ac708d2acdd799a27d40)): ?>
<?php $component = $__componentOriginal7408c88f8f69ac708d2acdd799a27d40; ?>
<?php unset($__componentOriginal7408c88f8f69ac708d2acdd799a27d40); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/tenants/partials/workspace/header.blade.php ENDPATH**/ ?>