<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'paginator' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'paginator' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $paginator = $paginator ?? $attributes->get('paginator');
?>

<?php if($paginator && $paginator->hasPages()): ?>
    <div <?php echo e($attributes->merge(['class' => 'flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between'])); ?>>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            <?php echo e(__('Page :current of :last · :total total', [
                'current' => $paginator->currentPage(),
                'last' => $paginator->lastPage(),
                'total' => number_format($paginator->total()),
            ])); ?>

        </p>
        <?php echo e($paginator->withQueryString()->links('vendor.pagination.prady')); ?>

    </div>
<?php elseif($paginator): ?>
    <p class="text-xs text-slate-500 dark:text-slate-400">
        <?php echo e(__(':total results', ['total' => number_format($paginator->total())])); ?>

    </p>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/pagination-bar.blade.php ENDPATH**/ ?>