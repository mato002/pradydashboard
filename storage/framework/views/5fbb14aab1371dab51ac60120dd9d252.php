<?php
    $formAction = $formAction ?? route('invoices.payments.record');
    $defaultTenantId = $defaultTenantId ?? '';
    $defaultInvoiceId = $defaultInvoiceId ?? '';
    $compact = $compact ?? false;
?>

<form method="post" action="<?php echo e($formAction); ?>" class="<?php echo e($compact ? 'space-y-2 text-sm' : 'rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900 space-y-3'); ?>">
    <?php echo csrf_field(); ?>
    <?php if($defaultInvoiceId): ?>
        <input type="hidden" name="tenant_invoice_id" value="<?php echo e($defaultInvoiceId); ?>">
    <?php endif; ?>
    <?php if (! ($compact)): ?>
        <h3 class="text-sm font-semibold"><?php echo e(__('Record payment')); ?></h3>
    <?php endif; ?>
    <div class="<?php echo e($compact ? 'space-y-2' : 'grid gap-3 sm:grid-cols-2 lg:grid-cols-3'); ?>">
        <?php if(empty($defaultInvoiceId)): ?>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Tenant (optional)')); ?></label>
                <select name="tenant_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    <option value=""><?php echo e(__('— Unmatched —')); ?></option>
                    <?php $__currentLoopData = $filterTenants ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>" <?php if($defaultTenantId == $t->id): echo 'selected'; endif; ?>><?php echo e($t->company_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        <?php endif; ?>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Payer name')); ?></label>
            <input name="payer_name" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Amount')); ?> *</label>
            <input type="number" step="0.01" min="0.01" name="amount" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Payment date')); ?> *</label>
            <input type="date" name="payment_date" value="<?php echo e(now()->toDateString()); ?>" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Source')); ?> *</label>
            <select name="source" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                <?php $__currentLoopData = $paymentSources ?? \App\Support\Billing\PaymentSource::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($src); ?>"><?php echo e(\App\Support\Billing\PaymentSource::label($src)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Reference / transaction code')); ?></label>
            <input name="reference" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Bank / account / source')); ?></label>
            <input name="bank_source" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Payer phone')); ?></label>
            <input name="payer_phone" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Payer email')); ?></label>
            <input type="email" name="payer_email" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs text-slate-500"><?php echo e(__('Narration / description')); ?></label>
            <input name="narration" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
        </div>
    </div>
    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white"><?php echo e(__('Record payment')); ?></button>
    <?php if(empty($defaultInvoiceId)): ?>
        <p class="text-[10px] text-slate-500"><?php echo e(__('Without an invoice, payment is saved as unreconciled in the Payment Inbox.')); ?></p>
    <?php endif; ?>
</form>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/invoices/partials/record-payment-form.blade.php ENDPATH**/ ?>