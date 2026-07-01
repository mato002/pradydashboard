<?php if(($riskOpsCenter['total'] ?? 0) > 0): ?>
    <?php if (isset($component)) { $__componentOriginalf3b2e072d2fb746341f22c2f71fe8709 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3b2e072d2fb746341f22c2f71fe8709 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.operations-risk-center','data' => ['center' => $riskOpsCenter]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.operations-risk-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['center' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riskOpsCenter)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3b2e072d2fb746341f22c2f71fe8709)): ?>
<?php $attributes = $__attributesOriginalf3b2e072d2fb746341f22c2f71fe8709; ?>
<?php unset($__attributesOriginalf3b2e072d2fb746341f22c2f71fe8709); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3b2e072d2fb746341f22c2f71fe8709)): ?>
<?php $component = $__componentOriginalf3b2e072d2fb746341f22c2f71fe8709; ?>
<?php unset($__componentOriginalf3b2e072d2fb746341f22c2f71fe8709); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/dashboard/partials/attention-required.blade.php ENDPATH**/ ?>