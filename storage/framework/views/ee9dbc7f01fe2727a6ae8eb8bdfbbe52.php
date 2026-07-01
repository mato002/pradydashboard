<?php
    $project = $tenant->hostedProject ?? $tenant->project;
    $dashboardUrl = rtrim((string) config('app.url'), '/');
    $productKey = $project?->resolveProductKey() ?? '';
    $envLines = implode("\n", array_filter([
        'PRADY_LICENSE_ENFORCED=true',
        'PRADY_DASHBOARD_URL='.$dashboardUrl,
        'PRADY_PROJECT_API_TOKEN='.($project?->api_token ?? ''),
        'PRADY_TENANT_KEY='.($tenant->tenant_key ?? ''),
        'PRADY_PRODUCT_KEY='.$productKey,
        'PRADY_LICENSE_SECRET='.($tenant->license_secret ?? ''),
        'PRADY_LICENSE_CACHE_TTL=600',
        'PRADY_TENANT_CODE='.($tenant->tenant_code ?? ''),
        'PRADY_PRODUCT_NAME="'.($project?->product?->name ?? $project?->name ?? config('app.name')).'"',
    ]));
?>

<?php if($project): ?>
    <div class="mb-4 overflow-hidden rounded-xl border border-indigo-200/80 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20">
        <div class="border-b border-indigo-100/80 px-4 py-2 dark:border-indigo-900/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Product .env credentials')); ?></h3>
            <p class="text-xs text-slate-600 dark:text-slate-400"><?php echo e(__('For :project — copy into the hosted installation', ['project' => $project->name])); ?></p>
        </div>
        <div class="grid gap-2 p-4 sm:grid-cols-2">
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_TENANT_KEY'),'value' => $tenant->tenant_key ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_TENANT_KEY')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->tenant_key ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_TENANT_CODE'),'value' => $tenant->tenant_code ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_TENANT_CODE')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->tenant_code ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_LICENSE_SECRET'),'value' => $tenant->license_secret ?? '','masked' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_LICENSE_SECRET')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->license_secret ?? ''),'masked' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('tenant_domain'),'value' => $tenant->tenant_domain ?? $project->domain]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tenant_domain')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->tenant_domain ?? $project->domain)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_PROJECT_API_TOKEN'),'value' => $project->api_token ?? '','masked' => true,'class' => 'sm:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_PROJECT_API_TOKEN')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->api_token ?? ''),'masked' => true,'class' => 'sm:col-span-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Full .env block'),'value' => $envLines,'class' => 'sm:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Full .env block')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($envLines),'class' => 'sm:col-span-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
        </div>
        <p class="border-t border-indigo-100/80 px-4 py-2 text-[11px] text-slate-500 dark:border-indigo-900/50">
            <a href="<?php echo e(route('hosted-projects.show', $project)); ?>" class="font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e(__('View all integration fields on hosted project')); ?> →</a>
        </p>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/tenants/partials/integration-credentials.blade.php ENDPATH**/ ?>