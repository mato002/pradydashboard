<?php
    $kit = $integrationKit ?? [];
    $primary = $kit['primary_tenant'] ?? null;
?>

<div
    class="overflow-hidden rounded-2xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/90 via-white to-cyan-50/50 shadow-card dark:border-indigo-900/50 dark:from-indigo-950/40 dark:via-slate-900 dark:to-cyan-950/20"
    x-data="{ envOpen: true }"
>
    <div class="border-b border-indigo-100/80 px-4 py-3 dark:border-indigo-900/50">
        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Product integration')); ?></p>
        <h3 class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('MFI / hosted app .env credentials')); ?></h3>
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            <?php echo e(__('Copy these into the product installation (e.g. :path). Set :domain on each tenant to match the product URL host.', [
                'path' => 'htdocs/mfi/.env',
                'domain' => 'tenant_domain',
            ])); ?>

        </p>
    </div>

    <div class="space-y-3 p-4">
        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_DASHBOARD_URL'),'value' => $kit['dashboard_url'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_DASHBOARD_URL')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['dashboard_url'] ?? '')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_PRODUCT_KEY'),'value' => $kit['product_key'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_PRODUCT_KEY')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['product_key'] ?? '')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_PRODUCT_NAME'),'value' => $kit['product_name'] ?? '','mono' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_PRODUCT_NAME')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['product_name'] ?? ''),'mono' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_PROJECT_API_TOKEN'),'value' => $kit['project_api_token'] ?? '','masked' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_PROJECT_API_TOKEN')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['project_api_token'] ?? ''),'masked' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('License API endpoint'),'value' => $kit['license_endpoint'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('License API endpoint')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['license_endpoint'] ?? '')]); ?>
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

        <?php if($primary): ?>
            <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_TENANT_KEY'),'value' => $primary['tenant_key'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_TENANT_KEY')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primary['tenant_key'] ?? '')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_TENANT_CODE'),'value' => $primary['tenant_code'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_TENANT_CODE')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primary['tenant_code'] ?? '')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_LICENSE_SECRET'),'value' => $primary['license_secret'] ?? '','masked' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_LICENSE_SECRET')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primary['license_secret'] ?? ''),'masked' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Tenant domain (must match product host)'),'value' => $primary['tenant_domain'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tenant domain (must match product host)')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primary['tenant_domain'] ?? '')]); ?>
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
        <?php else: ?>
            <div class="rounded-lg border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-semibold"><?php echo e(__('No tenant linked yet')); ?></p>
                <p class="mt-1 opacity-90"><?php echo e(__('Create a tenant for this hosted project to get tenant_key and license_secret.')); ?></p>
                <a href="<?php echo e($kit['create_tenant_url'] ?? route('tenants.create')); ?>" class="mt-2 inline-flex font-semibold text-indigo-700 underline dark:text-indigo-300"><?php echo e(__('Add tenant')); ?> →</a>
            </div>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PRADY_DASHBOARD_API_TOKEN (suggested — paste into product .env)'),'value' => $kit['suggested_dashboard_api_token'] ?? '','masked' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PRADY_DASHBOARD_API_TOKEN (suggested — paste into product .env)')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['suggested_dashboard_api_token'] ?? ''),'masked' => true]); ?>
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

        <?php if(count($kit['tenants'] ?? []) > 1): ?>
            <div class="rounded-lg border border-slate-200/80 p-3 dark:border-slate-700">
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Multiple tenants')); ?></p>
                <ul class="mt-2 space-y-2 text-xs">
                    <?php $__currentLoopData = $kit['tenants']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-slate-50 px-2 py-1.5 dark:bg-slate-800/60">
                            <a href="<?php echo e($t['show_url']); ?>" class="font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($t['company_name']); ?></a>
                            <code class="font-mono text-[10px] text-slate-500"><?php echo e($t['tenant_key']); ?></code>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="rounded-lg border border-slate-200/80 dark:border-slate-700">
            <button
                type="button"
                @click="envOpen = !envOpen"
                class="flex w-full items-center justify-between px-3 py-2 text-left text-xs font-semibold text-slate-700 dark:text-slate-200"
            >
                <?php echo e(__('Copy full .env block')); ?>

                <span x-text="envOpen ? '−' : '+'"></span>
            </button>
            <div x-show="envOpen" x-cloak class="border-t border-slate-200/80 p-3 dark:border-slate-700">
                <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Complete snippet'),'value' => $kit['env_block'] ?? '','mono' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Complete snippet')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kit['env_block'] ?? ''),'mono' => true]); ?>
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
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/hosted-projects/partials/integration-kit.blade.php ENDPATH**/ ?>