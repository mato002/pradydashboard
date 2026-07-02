<table class="pc-ledger" width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="pc-col-num">&nbsp;</th>
            <th class="pc-col-desc"><?php echo e(__('Item Description')); ?></th>
            <th class="pc-col-unit"><?php echo e(__('Unit Price')); ?><br><?php echo e(__('Shs')); ?></th>
            <th class="pc-col-amt"><?php echo e(__('Total Amount')); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="pc-col-num c"><?php echo e($i + 1); ?></td>
                <td class="pc-col-desc"><?php echo e($line['description'] ?? ''); ?></td>
                <td class="pc-col-unit r"><?php echo e($fmtAmount((float) ($line['unit_price'] ?? 0))); ?></td>
                <td class="pc-col-amt r"><?php echo e($fmtAmount((float) ($line['line_total'] ?? 0))); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td class="pc-col-num c">&nbsp;</td>
                <td class="pc-col-desc">&nbsp;</td>
                <td class="pc-col-unit r">&nbsp;</td>
                <td class="pc-col-amt r">&nbsp;</td>
            </tr>
        <?php endif; ?>

        <?php $padRows = max(0, $minLedgerRows - count($lineItems)); ?>
        <?php for($r = 0; $r < $padRows; $r++): ?>
            <tr class="pc-empty-row">
                <td class="pc-col-num c">&nbsp;</td>
                <td class="pc-col-desc">&nbsp;</td>
                <td class="pc-col-unit r">&nbsp;</td>
                <td class="pc-col-amt r">&nbsp;</td>
            </tr>
        <?php endfor; ?>
    </tbody>
    <?php if($showPaymentOptions || $showPaidBalance || $isQuotation): ?>
        <tfoot>
            <tr>
                <td colspan="2" class="pc-pay-cell">
                    <?php if($showPaymentOptions): ?>
                        <?php echo $__env->make('billing.documents.prady-classic.partials.payment-box', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </td>
                <td colspan="2" class="pc-totals-cell">
                    <?php echo $__env->make('billing.documents.prady-classic.partials.totals-box', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/billing/documents/prady-classic/partials/ledger-body.blade.php ENDPATH**/ ?>