<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'placeholder' => null,
    'options' => [],
    'value' => '',
    'auto' => true,
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
    'label' => null,
    'placeholder' => null,
    'options' => [],
    'value' => '',
    'auto' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<label class="inline-flex w-full min-w-0 flex-col gap-1 sm:w-auto">
    <?php if($label): ?>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?php echo e($label); ?></span>
    <?php endif; ?>
    <select
        name="<?php echo e($name); ?>"
        <?php echo e($attributes->merge(['class' => 'w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 sm:w-auto'])); ?>

        <?php if($auto): ?> @change="$el.form.submit()" <?php endif; ?>
    >
        <?php if($placeholder): ?>
            <option value=""><?php echo e($placeholder); ?></option>
        <?php endif; ?>
        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optValue => $optLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($optValue); ?>" <?php if((string) $value === (string) $optValue): echo 'selected'; endif; ?>><?php echo e($optLabel); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</label>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/filter-select.blade.php ENDPATH**/ ?>