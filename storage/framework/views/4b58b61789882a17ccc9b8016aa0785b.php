<?php if($isReceipt): ?>
    <div class="pc-total-line">
        <span class="pc-total-label"><?php echo e(__('PAID')); ?>:</span>
        <span class="pc-total-amount"><?php echo e($fmtKsh($paid)); ?></span>
    </div>
    <div class="pc-total-line pc-balance-line">
        <span class="pc-total-label"><?php echo e(__('BALANCE')); ?>:</span>
        <span class="pc-total-amount"><?php echo e($fmtKsh($balance)); ?></span>
    </div>
<?php elseif($isQuotation): ?>
    <div class="pc-total-line">
        <span class="pc-total-label"><?php echo e(__('Total')); ?>:</span>
        <span class="pc-total-amount"><?php echo e($fmtKsh($total)); ?></span>
    </div>
<?php else: ?>
    <?php if($discount > 0): ?>
        <div class="pc-total-line">
            <span class="pc-total-label"><?php echo e(__('Discount')); ?>:</span>
            <span class="pc-total-amount"><?php echo e($fmtKsh($discount)); ?></span>
        </div>
    <?php endif; ?>
    <?php if($tax > 0): ?>
        <div class="pc-total-line">
            <span class="pc-total-label"><?php echo e(__('Tax')); ?>:</span>
            <span class="pc-total-amount"><?php echo e($fmtKsh($tax)); ?></span>
        </div>
    <?php endif; ?>
    <div class="pc-total-line">
        <span class="pc-total-label"><?php echo e(__('Subtotal')); ?>:</span>
        <span class="pc-total-amount"><?php echo e($fmtKsh($subtotal)); ?></span>
    </div>
    <div class="pc-total-line">
        <span class="pc-total-label"><?php echo e(__('Grand Total')); ?>:</span>
        <span class="pc-total-amount"><?php echo e($fmtKsh($total)); ?></span>
    </div>
    <?php if($showPaidBalance): ?>
        <div class="pc-total-line">
            <span class="pc-total-label"><?php echo e(__('PAID')); ?>:</span>
            <span class="pc-total-amount"><?php echo e($fmtKsh($paid)); ?></span>
        </div>
        <div class="pc-total-line pc-balance-line">
            <span class="pc-total-label"><?php echo e(__('BALANCE')); ?>:</span>
            <span class="pc-total-amount"><?php echo e($fmtKsh($balance)); ?></span>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/billing/documents/prady-classic/partials/totals-box.blade.php ENDPATH**/ ?>