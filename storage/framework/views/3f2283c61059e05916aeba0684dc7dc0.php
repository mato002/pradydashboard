<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'step' => null,
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
    'description' => null,
    'step' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60'])); ?>>
    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div class="flex items-start gap-3">
            <?php if($step): ?>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white shadow-lg shadow-indigo-500/25"><?php echo e($step); ?></span>
            <?php endif; ?>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($title); ?></h3>
                <?php if($description): ?>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><?php echo e($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="p-5">
        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/form-section.blade.php ENDPATH**/ ?>