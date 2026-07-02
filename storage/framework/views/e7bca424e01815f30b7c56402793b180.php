<?php if(($pay['bank_name'] ?? '') !== ''): ?>
    <div>Bank: <?php echo e($pay['bank_name']); ?></div>
<?php endif; ?>
<?php if(($pay['account_number'] ?? $pay['bank_account_number'] ?? '') !== ''): ?>
    <div><?php echo e(__('Account number')); ?>: <?php echo e($pay['account_number'] ?? $pay['bank_account_number']); ?></div>
<?php endif; ?>
<?php if(($pay['account_name'] ?? '') !== '' || ($pay['bank_branch'] ?? '') !== ''): ?>
    <div>
        <?php echo e(trim(($pay['account_name'] ?? '').' '.($pay['bank_branch'] ?? '').' branch')); ?>

        <?php if(($pay['paybill'] ?? $pay['mpesa_paybill'] ?? '') !== '' || ($pay['paybill_account'] ?? $pay['paybill_account_number'] ?? '') !== ''): ?>
            or
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if(($pay['paybill'] ?? $pay['mpesa_paybill'] ?? '') !== ''): ?>
    <div><?php echo e(__('Paybill')); ?>: <?php echo e($pay['paybill'] ?? $pay['mpesa_paybill']); ?></div>
<?php endif; ?>
<?php if(($pay['paybill_account'] ?? $pay['paybill_account_number'] ?? '') !== ''): ?>
    <div><?php echo e(__('Account')); ?>: <span class="pc-paybill-account-name"><?php echo e($pay['paybill_account'] ?? $pay['paybill_account_number']); ?></span></div>
<?php endif; ?>
<?php if(! empty($pay['payment_instructions'] ?? null)): ?>
    <div><?php echo e($pay['payment_instructions']); ?></div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/billing/documents/prady-classic/partials/payment-box.blade.php ENDPATH**/ ?>