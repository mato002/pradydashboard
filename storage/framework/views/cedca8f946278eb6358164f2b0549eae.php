<div class="pc-doc-title"><?php echo e($docLabel); ?></div>

<table class="pc-meta-row" width="100%" cellpadding="0" cellspacing="0">
    <tr class="pc-meta-line-row">
        <td class="pc-meta-no" align="left" width="50%" valign="bottom">
            No. <span class="pc-meta-no-val"><?php echo e(str_replace('No. ', '', $displayNumber)); ?></span>
        </td>
        <td class="pc-meta-date" align="right" width="50%" valign="bottom">
            Date <span class="pc-meta-date-val"><?php echo e($issueDateDisplay); ?></span>
        </td>
    </tr>
    <tr class="pc-meta-line-row pc-meta-line-row--second">
        <td class="pc-meta-client" align="left" width="50%" valign="bottom">
            <span class="pc-client-prefix"><?php echo e($clientPrefix); ?></span>
            <span class="pc-client-name-block">
                <span class="pc-client-name"><?php echo e(strtoupper($clientName)); ?></span>
                <span class="pc-client-dots">&nbsp;</span>
            </span>
        </td>
        <td class="pc-meta-due" align="right" width="50%" valign="bottom">
            <?php if($showDueDate && ! empty($dueDate)): ?>
                <?php echo e(__('Due')); ?> <span class="pc-meta-due-val"><?php echo e(\Illuminate\Support\Carbon::parse($dueDate)->format('d/m/Y')); ?></span>
            <?php elseif($showValidity && ! empty($dueDate)): ?>
                <?php echo e(__('Valid until')); ?> <span class="pc-meta-due-val"><?php echo e(\Illuminate\Support\Carbon::parse($dueDate)->format('d/m/Y')); ?></span>
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </td>
    </tr>
</table>

<?php if($isStatement && ! empty($s['period_start'] ?? ($s['statement']['period_start'] ?? null))): ?>
    <div class="pc-meta-sub">
        <?php echo e(__('Period')); ?>:
        <?php echo e($s['period_start'] ?? $s['statement']['period_start']); ?>

        —
        <?php echo e($s['period_end'] ?? $s['statement']['period_end'] ?? '—'); ?>

    </div>
<?php endif; ?>

<?php if($isReceipt && $linkedInvoice): ?>
    <div class="pc-meta-sub"><?php echo e(__('Invoice ref')); ?>: <?php echo e($linkedInvoice); ?></div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/billing/documents/prady-classic/partials/document-meta.blade.php ENDPATH**/ ?>