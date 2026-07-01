<?php
    use App\Support\Billing\PradyClassicBrandAssets;

    $headerSrc = PradyClassicBrandAssets::headerSrc();
?>

<?php if($headerSrc): ?>
    <img src="<?php echo e($headerSrc); ?>" class="prady-header-image" alt="">
<?php else: ?>
    <?php echo $__env->make('billing.documents.prady-classic.partials.header-brand-fallback', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<table class="pc-contact-row" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="pc-contact-left" width="50%" valign="top">
            <?php if(($issuer['phone'] ?? '') !== ''): ?>
                <div>Tel: <?php echo e($issuer['phone']); ?></div>
            <?php endif; ?>
            <?php if(($issuer['address'] ?? '') !== ''): ?>
                <div><?php echo e($issuer['address']); ?></div>
            <?php endif; ?>
        </td>
        <td class="pc-contact-right" width="50%" valign="top" align="right">
            <?php if(($issuer['email'] ?? '') !== ''): ?>
                <div>Email: <?php echo e($issuer['email']); ?></div>
            <?php endif; ?>
            <?php if(($issuer['website'] ?? '') !== ''): ?>
                <div>Website: <?php echo e($issuer['website']); ?></div>
            <?php endif; ?>
        </td>
    </tr>
</table>

<div class="pc-contact-rule">&nbsp;</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/billing/documents/prady-classic/partials/header.blade.php ENDPATH**/ ?>