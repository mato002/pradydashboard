<?php
    use App\Support\Billing\PradyClassicBrandAssets;

    $footerSrc = PradyClassicBrandAssets::footerSrc();
?>
<div class="pc-doc-footer">
    <div class="pc-thanks">Thank you.</div>
    <?php if($footerSrc): ?>
        <img src="<?php echo e($footerSrc); ?>" class="prady-footer-image pc-footer-wave" alt="">
    <?php else: ?>
        <?php echo $__env->make('billing.documents.prady-classic.partials.footer-wave-fallback', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/billing/documents/prady-classic/partials/footer.blade.php ENDPATH**/ ?>