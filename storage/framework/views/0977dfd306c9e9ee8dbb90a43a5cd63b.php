<?php
    $shellUrl = route('tenants.show', $tenant);
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $tenant->company_name,'documentTitle' => $tenant->company_name.' — '.__('Tenant workspace')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->company_name),'document-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenant->company_name.' — '.__('Tenant workspace'))]); ?>
    <div
        id="tenant-workspace-root"
        x-data="tenantWorkspace({
            baseUrl: <?php echo \Illuminate\Support\Js::from($shellUrl)->toHtml() ?>,
            initialTab: <?php echo \Illuminate\Support\Js::from($tab)->toHtml() ?>,
            tabs: <?php echo \Illuminate\Support\Js::from(array_keys($workspaceTabs))->toHtml() ?>,
        })"
        class="tenant-workspace-root min-w-0 max-w-full overflow-x-hidden"
    >
        <?php echo $__env->make('admin.tenants.partials.workspace.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.tenants.partials.workspace.metrics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.tenants.partials.workspace.tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="relative mt-6">
            <div
                x-show="loading"
                x-transition.opacity
                x-cloak
                class="absolute inset-0 z-10 rounded-2xl bg-white/70 backdrop-blur-[2px] dark:bg-slate-950/70"
            >
                <?php echo $__env->make('admin.tenants.partials.workspace.skeleton', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div
                class="transition-opacity duration-200"
                :class="loading ? 'pointer-events-none opacity-40' : 'opacity-100'"
            >
                <?php echo $__env->make('admin.tenants.partials.workspace.content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/tenants/show.blade.php ENDPATH**/ ?>