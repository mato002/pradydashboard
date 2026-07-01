<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'actionHref' => null,
    'actionLabel' => null,
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
    'title',
    'actionHref' => null,
    'actionLabel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60'])); ?>>
    <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
        <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e($title); ?></h2>
        <?php if($actionHref && $actionLabel): ?>
            <a href="<?php echo e($actionHref); ?>" class="text-xs font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"><?php echo e($actionLabel); ?></a>
        <?php endif; ?>
    </div>
    <div class="prady-scrollbar overflow-x-auto">
        <?php echo e($slot); ?>

    </div>
    <?php if(isset($footer)): ?>
        <div class="border-t border-slate-200/80 px-4 py-3 text-sm dark:border-slate-800/80">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/ui/table-panel.blade.php ENDPATH**/ ?>