<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['assignments' => collect(), 'title' => __('Assigned staff')]));

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

foreach (array_filter((['assignments' => collect(), 'title' => __('Assigned staff')]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($assignments->isNotEmpty()): ?>
    <div <?php echo e($attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900'])); ?>>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e($title); ?></h3>
        <ul class="mt-3 space-y-2">
            <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex flex-wrap items-start justify-between gap-2 text-sm">
                    <div>
                        <a href="<?php echo e(route('hr.staff.show', $assignment->staffProfile)); ?>" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            <?php echo e($assignment->staffProfile?->full_name); ?>

                        </a>
                        <?php if($assignment->role_on_assignment): ?>
                            <span class="text-gray-500"> — <?php echo e($assignment->role_on_assignment); ?></span>
                        <?php endif; ?>
                        <?php if($assignment->staffProfile?->department): ?>
                            <p class="text-xs text-gray-500"><?php echo e($assignment->staffProfile->department->name); ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300"><?php echo e($assignment->status); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/assigned-staff.blade.php ENDPATH**/ ?>