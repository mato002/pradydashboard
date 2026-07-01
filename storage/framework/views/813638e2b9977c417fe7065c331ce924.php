<?php
    $s = $snapshot;
    $b = $branding;
?>
<div style="font-family:system-ui,sans-serif;max-width:800px;margin:0 auto;padding:24px;color:#0f172a;">
    <header style="display:flex;justify-content:space-between;border-bottom:3px solid <?php echo e($b['primary_color'] ?? '#4f46e5'); ?>;padding-bottom:16px;margin-bottom:24px;">
        <div>
            <h1 style="margin:0;font-size:22px;"><?php echo e($b['company_name'] ?? config('app.name')); ?></h1>
            <p style="font-size:12px;color:#64748b;"><?php echo e($b['tax_pin'] ?? ''); ?></p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:11px;text-transform:uppercase;color:#64748b;"><?php echo e(ucfirst(str_replace('_', ' ', $s['document_type'] ?? 'invoice'))); ?></p>
            <p style="font-size:18px;font-weight:700;"><?php echo e($s['invoice_number']); ?></p>
        </div>
    </header>
    <p><strong><?php echo e(__('Bill to')); ?>:</strong> <?php echo e($s['tenant']['company_name'] ?? '—'); ?></p>
    <p style="font-size:12px;color:#64748b;"><?php echo e(__('Issue')); ?>: <?php echo e($s['issue_date'] ?? '—'); ?> · <?php echo e(__('Due')); ?>: <?php echo e($s['due_date'] ?? '—'); ?></p>
    <?php echo $__env->make('billing.documents.partials.line-items-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('billing.documents.partials.totals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <footer style="margin-top:24px;font-size:11px;color:#94a3b8;"><?php echo e($b['footer_text'] ?? ''); ?></footer>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/billing/documents/modern-saas.blade.php ENDPATH**/ ?>