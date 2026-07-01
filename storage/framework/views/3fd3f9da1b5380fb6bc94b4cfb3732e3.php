<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Payments Gateway'),'subheading' => __('Overview')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments Gateway')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Overview'))]); ?>
    <div class="space-y-6">
        <?php echo $__env->make('settings.integrations.payments-gateway.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('settings.integrations.payments-gateway.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalb6f65973f5a6918a6180e1799325c972 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6f65973f5a6918a6180e1799325c972 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.kpi-card','data' => ['title' => match ($key) {
                        'gateway_status' => __('Gateway status'),
                        'total_tenants' => __('Total tenants'),
                        'active_tenants' => __('Active tenants'),
                        'total_payment_profiles' => __('Payment profiles'),
                        'total_paybill_accounts' => __('PayBill accounts'),
                        'failed_callbacks' => __('Failed callbacks'),
                        'reconciliation_issues' => __('Reconciliation issues'),
                        'last_sync_time' => __('Last sync time'),
                        default => ucfirst(str_replace('_', ' ', $key)),
                    },'value' => $card['value'],'sublabel' => $card['sublabel'],'tone' => $card['tone'],'animate' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($key) {
                        'gateway_status' => __('Gateway status'),
                        'total_tenants' => __('Total tenants'),
                        'active_tenants' => __('Active tenants'),
                        'total_payment_profiles' => __('Payment profiles'),
                        'total_paybill_accounts' => __('PayBill accounts'),
                        'failed_callbacks' => __('Failed callbacks'),
                        'reconciliation_issues' => __('Reconciliation issues'),
                        'last_sync_time' => __('Last sync time'),
                        default => ucfirst(str_replace('_', ' ', $key)),
                    }),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'sublabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['sublabel']),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['tone']),'animate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Quick links')); ?></h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="<?php echo e(route('settings.payments-gateway.tenants.index')); ?>" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    <?php echo e(__('Tenant Profiles')); ?>

                </a>
                <a href="<?php echo e(route('settings.payments-gateway.health')); ?>" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    <?php echo e(__('Gateway Health')); ?>

                </a>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/settings/integrations/payments-gateway/overview.blade.php ENDPATH**/ ?>