<?php
    $tabUrl = fn (string $t) => route('invoices.index', array_merge(request()->except('tab', 'page'), ['tab' => $t]));
    $tabClass = fn (string $t) => $tab === $t
        ? 'border-amber-600 text-amber-700 dark:border-amber-400 dark:text-amber-300'
        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400';
?>
<nav class="-mb-px flex gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800" aria-label="<?php echo e(__('Financial operations tabs')); ?>">
    <?php $__currentLoopData = [
        'overview' => __('Overview'),
        'invoices' => __('Invoices'),
        'quotations' => __('Quotations'),
        'proforma' => __('Proforma'),
        'receipts' => __('Receipts'),
        'recurring' => __('Recurring Billing'),
        'collections' => __('Collections'),
        'payments' => __('Payment Inbox'),
        'templates' => __('Templates'),
        'statements' => __('Statements'),
        'automation' => __('Automation Rules'),
        'activity' => __('Activity'),
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($tabUrl($key)); ?>" class="whitespace-nowrap border-b-2 px-3 py-2.5 text-xs font-semibold <?php echo e($tabClass($key)); ?>"><?php echo e($label); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/invoices/partials/nav.blade.php ENDPATH**/ ?>