<turbo-frame
    id="tenant-workspace"
    target="_top"
    class="tenant-workspace-panel relative block min-h-[12rem] transition-opacity duration-200"
    data-tenant-tab="<?php echo e($tab); ?>"
    role="region"
    aria-label="<?php echo e($workspaceTabs[$tab] ?? __('Workspace')); ?>"
>
    <?php if($tab === 'overview'): ?>
        <div class="grid gap-6 lg:grid-cols-3">
            <dl class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Business type')); ?></dt><dd class="text-right font-medium"><?php echo e($tenant->business_type ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('KRA PIN')); ?></dt><dd class="font-medium"><?php echo e($tenant->kra_pin ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Country')); ?></dt><dd class="font-medium"><?php echo e($tenant->country ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="shrink-0 text-slate-500"><?php echo e(__('Address')); ?></dt><dd class="text-right font-medium"><?php echo e($tenant->physical_address ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Contact')); ?></dt><dd class="text-right font-medium"><?php echo e($tenant->contact_person ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Phone / Email')); ?></dt><dd class="text-right font-medium"><?php echo e($tenant->phone ?? '—'); ?> · <?php echo e($tenant->email ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Tenant domain')); ?></dt><dd class="max-w-xs truncate font-mono text-xs"><?php echo e($tenant->tenant_domain ?? '—'); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('External key')); ?></dt><dd class="max-w-xs truncate font-mono text-xs"><?php echo e($tenant->external_key); ?></dd></div>
            </dl>
            <div class="space-y-4">
                <dl class="rounded-xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-3 font-semibold text-slate-900 dark:text-white"><?php echo e(__('Deployment snapshot')); ?></h3>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Assigned server')); ?></dt><dd class="text-right font-medium"><?php echo e($opsSummary['assigned_server']); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Public URL')); ?></dt><dd class="max-w-xs truncate text-right font-medium"><?php if($opsSummary['public_url']): ?><a href="<?php echo e($opsSummary['public_url']); ?>" class="text-indigo-600 dark:text-indigo-400" target="_blank" rel="noopener"><?php echo e($opsSummary['public_url']); ?></a><?php else: ?> — <?php endif; ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('SSL / Backup')); ?></dt><dd class="text-right font-medium"><?php echo e($opsSummary['ssl_status']); ?> · <?php echo e($opsSummary['backup_status']); ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Version / risk')); ?></dt><dd class="text-right font-medium"><?php echo e($opsSummary['version_status']); ?> · <?php echo e($opsSummary['update_risk']); ?></dd></div>
                </dl>
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-5 text-sm text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                    <h3 class="font-semibold"><?php echo e(__('Access & licensing')); ?></h3>
                    <p class="mt-2 text-indigo-900/90 dark:text-indigo-200"><?php echo e(__('Tenant apps authenticate with the project API token and this external_key, or enterprise POST /api/license/check with tenant_id + domain + product slug.')); ?></p>
                    <?php if($tenant->latestAccessControl): ?>
                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-indigo-800 dark:text-indigo-300"><?php echo e(__('Latest control')); ?>: <?php echo e($tenant->latestAccessControl->level); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (isset($component)) { $__componentOriginal758a4d06f179e1d63015d8fd45f690dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.assigned-staff','data' => ['assignments' => $staffAssignments,'title' => __('Account team'),'class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.assigned-staff'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['assignments' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($staffAssignments),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Account team')),'class' => 'mt-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $attributes = $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $component = $__componentOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>
    <?php elseif($tab === 'billing'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.billing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'projects'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.projects', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'licensing'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.licensing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'integrations'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.integrations', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'versions'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.versions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'documents'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.documents', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'communications'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.communications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'infrastructure'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.infrastructure', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'modules'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.modules', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'users'): ?>
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-2"><?php echo e(__('Name')); ?></th>
                        <th class="px-4 py-2"><?php echo e(__('Email')); ?></th>
                        <th class="px-4 py-2"><?php echo e(__('Last seen')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $tenant->reportedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo e($u->name ?? '—'); ?></td>
                            <td class="px-4 py-2"><?php echo e($u->email ?? '—'); ?></td>
                            <td class="px-4 py-2"><?php echo e(optional($u->last_seen_at)->toDayDateTimeString() ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500"><?php echo e(__('No users reported yet. Tenant apps can POST usage to register seats.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php elseif($tab === 'activity'): ?>
        <?php if (isset($component)) { $__componentOriginalc535bf0441c81dd81939b35e9ab2587f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc535bf0441c81dd81939b35e9ab2587f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.activity-feed','data' => ['logs' => $systemActivityLogs,'class' => 'mt-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.activity-feed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($systemActivityLogs),'class' => 'mt-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc535bf0441c81dd81939b35e9ab2587f)): ?>
<?php $attributes = $__attributesOriginalc535bf0441c81dd81939b35e9ab2587f; ?>
<?php unset($__attributesOriginalc535bf0441c81dd81939b35e9ab2587f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc535bf0441c81dd81939b35e9ab2587f)): ?>
<?php $component = $__componentOriginalc535bf0441c81dd81939b35e9ab2587f; ?>
<?php unset($__componentOriginalc535bf0441c81dd81939b35e9ab2587f); ?>
<?php endif; ?>
    <?php elseif($tab === 'support'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.support', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'notices'): ?>
        <?php echo $__env->make('admin.tenants.partials.ops.notices', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($tab === 'deployments'): ?>
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200/80 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            <?php $__empty_1 = true; $__currentLoopData = $tenant->project?->deployments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span class="font-mono font-medium"><?php echo e($dep->version); ?></span>
                    <span class="text-xs text-slate-500"><?php echo e(optional($dep->deployed_at)->toDayDateTimeString() ?? '—'); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="px-4 py-8 text-center text-sm text-slate-500"><?php echo e(__('No deployment records for this product.')); ?></li>
            <?php endif; ?>
        </ul>
    <?php elseif($tab === 'monitoring'): ?>
        <?php $m = $tenant->usageMetric; ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('Active users')); ?></p>
                <p class="mt-2 text-2xl font-semibold"><?php echo e($m?->active_users ?? '—'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('Database (MB)')); ?></p>
                <p class="mt-2 text-2xl font-semibold tabular-nums"><?php echo e($m?->database_size_mb !== null ? number_format((float) $m->database_size_mb, 1) : '—'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('Storage (MB)')); ?></p>
                <p class="mt-2 text-2xl font-semibold tabular-nums"><?php echo e($m?->storage_usage_mb !== null ? number_format((float) $m->storage_usage_mb, 1) : '—'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('Server CPU %')); ?></p>
                <p class="mt-2 text-2xl font-semibold"><?php echo e($m?->server_cpu_percent ?? '—'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('App version')); ?></p>
                <p class="mt-2 font-mono text-sm"><?php echo e($m?->reported_app_version ?? $tenant->deployment_version ?? '—'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase text-slate-500"><?php echo e(__('Last sync')); ?></p>
                <p class="mt-2 text-sm font-medium"><?php echo e(optional($m?->last_sync_at)->toDayDateTimeString() ?? '—'); ?></p>
            </div>
        </div>
        <div class="mt-6 h-40 rounded-xl border border-dashed border-slate-300 bg-slate-50/80 p-4 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-400">
            <?php echo e(__('Charts placeholder — wire Prometheus / agent metrics in Phase 4.')); ?>

        </div>
    <?php else: ?>
        <div class="rounded-xl border border-slate-200/80 bg-white p-6 text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e(__('Operational notes')); ?></h3>
            <p class="mt-2 whitespace-pre-wrap"><?php echo e($tenant->notes ?: __('No internal notes.')); ?></p>
            <p class="mt-4 text-xs text-slate-500"><?php echo e(__('Suspension workflow: reminders → warning banner → restricted transactions → login lockout → restore on payment. Automate via jobs in Phase 3.')); ?></p>
        </div>
    <?php endif; ?>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/tenants/partials/workspace/content.blade.php ENDPATH**/ ?>