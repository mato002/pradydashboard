<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'fallback' => null,
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
    'fallback' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $logoUrl = \App\Models\Setting::logoUrl();
    $fallback = $fallback ?? strtoupper(substr(config('app.name', 'P'), 0, 1));
?>

<?php if($logoUrl): ?>
    <img
        src="<?php echo e($logoUrl); ?>"
        alt="<?php echo e(config('app.name')); ?>"
        <?php echo e($attributes->merge(['class' => 'object-contain'])); ?>

    />
<?php else: ?>
    <span <?php echo e($attributes->merge(['class' => 'inline-flex items-center justify-center font-bold leading-none'])); ?>>
        <?php echo e($fallback); ?>

    </span>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/brand-logo.blade.php ENDPATH**/ ?>