<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
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
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    id="prady-workspace-content"
    class="prady-workspace-content min-w-0"
    <?php if($heading): ?> data-page-heading="<?php echo e($heading); ?>" <?php endif; ?>
    <?php if($subheading): ?> data-page-subheading="<?php echo e($subheading); ?>" <?php endif; ?>
    <?php if($documentTitle): ?> data-document-title="<?php echo e($documentTitle); ?>" <?php endif; ?>
>
    <?php if(session('status')): ?>
        <div class="prady-flash-status mb-5 rounded-2xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/prady-workspace-content.blade.php ENDPATH**/ ?>