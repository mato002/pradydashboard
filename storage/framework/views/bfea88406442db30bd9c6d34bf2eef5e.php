<?php
    $s = $snapshot;
    $currency = $s['currency'] ?? 'KES';
    $amountPaid = (float) ($s['amount_paid'] ?? 0);
    $balanceDue = (float) ($s['balance_due'] ?? max(0, (float) ($s['total'] ?? 0) - $amountPaid));
?>
<section style="margin-top:16px;text-align:right;font-size:13px;">
    <p><?php echo e(__('Subtotal')); ?>: <?php echo e($currency); ?> <?php echo e(number_format($s['subtotal'] ?? 0, 2)); ?></p>
    <?php if((float) ($s['discount_amount'] ?? 0) > 0): ?>
        <p><?php echo e(__('Discount')); ?>: − <?php echo e(number_format((float) $s['discount_amount'], 2)); ?></p>
    <?php endif; ?>
    <p><?php echo e(__('Tax')); ?>: <?php echo e($currency); ?> <?php echo e(number_format($s['tax_amount'] ?? 0, 2)); ?></p>
    <p style="font-size:16px;font-weight:700;"><?php echo e(__('Total')); ?>: <?php echo e($currency); ?> <?php echo e(number_format($s['total'] ?? 0, 2)); ?></p>
    <?php if($amountPaid > 0): ?>
        <p style="color:#059669;"><?php echo e(__('Paid')); ?>: <?php echo e($currency); ?> <?php echo e(number_format($amountPaid, 2)); ?></p>
        <p style="font-size:15px;font-weight:700;"><?php echo e(__('Balance')); ?>: <?php echo e($currency); ?> <?php echo e(number_format($balanceDue, 2)); ?></p>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/billing/documents/partials/totals.blade.php ENDPATH**/ ?>