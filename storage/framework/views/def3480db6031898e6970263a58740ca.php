<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'method' => null,
    'danger' => false,
    'confirm' => null,
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
    'method' => null,
    'danger' => false,
    'confirm' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium transition';
    $classes .= $danger
        ? ' text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10'
        : ' text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800';
?>

<?php if($href && ! $method): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $classes, 'role' => 'menuitem'])); ?>><?php echo e($slot); ?></a>
<?php elseif($href && $method): ?>
    <form
        method="POST"
        action="<?php echo e($href); ?>"
        class="block"
        <?php if($confirm): ?> onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($confirm)->toHtml() ?>)" <?php endif; ?>
    >
        <?php echo csrf_field(); ?>
        <?php if(strtolower((string) $method) !== 'post'): ?>
            <?php echo method_field($method); ?>
        <?php endif; ?>
        <button type="submit" <?php echo e($attributes->merge(['class' => $classes, 'role' => 'menuitem'])); ?>><?php echo e($slot); ?></button>
    </form>
<?php else: ?>
    <button type="button" <?php echo e($attributes->merge(['class' => $classes, 'role' => 'menuitem'])); ?>><?php echo e($slot); ?></button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/ui/row-action.blade.php ENDPATH**/ ?>