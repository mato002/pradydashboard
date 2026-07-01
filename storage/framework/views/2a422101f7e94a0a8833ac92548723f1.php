<?php if($gatewayContractWarning ?? false): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <p class="font-semibold"><?php echo e(__('Payments Gateway API contract warning')); ?></p>
        <p class="mt-1"><?php echo e($gatewayContractWarning); ?></p>
    </div>
<?php endif; ?>

<?php if($gatewayUnavailable ?? false): ?>
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
        <p class="font-semibold"><?php echo e(__('Payments Gateway unavailable')); ?></p>
        <p class="mt-1"><?php echo e($gatewayMessage ?? __('Unable to reach payments.pradytecai.com.')); ?></p>
    </div>
<?php endif; ?>

<?php if(session('status')): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
        <?php echo e(session('status')); ?>

    </div>
<?php endif; ?>

<?php if(session('bulk_action_errors')): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <p class="font-semibold"><?php echo e(__('Some bulk actions failed')); ?></p>
        <ul class="mt-2 space-y-1 font-mono text-xs">
            <?php $__currentLoopData = session('bulk_action_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e(substr($error['uuid'] ?? '', 0, 8)); ?>… — <?php echo e($error['message'] ?? __('Gateway request failed.')); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(session('gateway_error')): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <?php echo e(session('gateway_error')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/settings/integrations/payments-gateway/partials/alerts.blade.php ENDPATH**/ ?>