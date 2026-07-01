<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'variant' => 'solid',
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
    'name',
    'variant' => 'solid',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $styleClass = match ($variant) {
        'regular' => 'fa-regular',
        'brands' => 'fa-brands',
        default => 'fa-solid',
    };
?>

<i <?php echo e($attributes->merge(['class' => trim("$styleClass fa-$name"), 'aria-hidden' => 'true'])); ?>></i>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/ui/icon.blade.php ENDPATH**/ ?>