<?php $s = $snapshot; ?>
<table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
        <tr style="background:#4f46e5;color:#fff;">
            <th style="text-align:left;padding:8px;"><?php echo e(__('Description')); ?></th>
            <th style="text-align:right;padding:8px;"><?php echo e(__('Qty')); ?></th>
            <th style="text-align:right;padding:8px;"><?php echo e(__('Unit')); ?></th>
            <th style="text-align:right;padding:8px;"><?php echo e(__('Total')); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $s['line_items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;"><?php echo e($line['description']); ?></td>
                <td style="padding:8px;text-align:right;"><?php echo e(number_format($line['quantity'], 2)); ?></td>
                <td style="padding:8px;text-align:right;"><?php echo e(number_format($line['unit_price'], 2)); ?></td>
                <td style="padding:8px;text-align:right;"><?php echo e(number_format($line['line_total'], 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/billing/documents/partials/line-items-table.blade.php ENDPATH**/ ?>