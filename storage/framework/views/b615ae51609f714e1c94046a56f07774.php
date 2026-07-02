<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
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
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $clickable = filled($href) && $href !== '#';
?>

<tr
    <?php if($clickable): ?>
        data-href="<?php echo e($href); ?>"
        tabindex="0"
        role="link"
    <?php endif; ?>
    <?php echo e($attributes->class($clickable ? ['cursor-pointer'] : [])); ?>

>
    <?php echo e($slot); ?>

</tr>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/ui/clickable-row.blade.php ENDPATH**/ ?>