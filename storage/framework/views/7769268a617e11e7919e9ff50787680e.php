<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Add hosted project'),'subheading' => __('Register a deployed domain under a product')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add hosted project')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Register a deployed domain under a product'))]); ?>
    <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['title' => __('New hosted project'),'subtitle' => __('Link a domain/instance to a product.'),'badge' => __('Hosted projects'),'backHref' => route('hosted-projects.index'),'backLabel' => __('Back to hosted projects')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New hosted project')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Link a domain/instance to a product.')),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted projects')),'back-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.index')),'back-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Back to hosted projects'))]); ?>
        <form method="post" action="<?php echo e(route('hosted-projects.store')); ?>" class="max-w-4xl space-y-5">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('admin.hosted-projects._form', ['hostedProject' => $hostedProject, 'products' => $products, 'servers' => $servers], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:brightness-110">
                    <?php echo e(__('Save hosted project')); ?>

                </button>
                <a href="<?php echo e(route('hosted-projects.index')); ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"><?php echo e(__('Cancel')); ?></a>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/hosted-projects/create.blade.php ENDPATH**/ ?>