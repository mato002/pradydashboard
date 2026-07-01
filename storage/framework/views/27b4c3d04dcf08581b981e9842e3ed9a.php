<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'logs',
    'empty' => __('No activity recorded yet.'),
    'compact' => false,
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
    'logs',
    'empty' => __('No activity recorded yet.'),
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900'])); ?>>
    <?php if(! $compact): ?>
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(__('Activity')); ?></h3>
        </div>
    <?php endif; ?>
    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="px-4 py-3 text-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($log->description); ?></p>
                    <time class="shrink-0 text-xs text-gray-500" datetime="<?php echo e($log->created_at?->toIso8601String()); ?>">
                        <?php echo e($log->created_at?->diffForHumans()); ?>

                    </time>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    <?php echo e($log->actorDisplayName()); ?>

                    · <span class="capitalize"><?php echo e($log->categoryLabel()); ?></span>
                    · <span class="font-mono text-[10px]"><?php echo e($log->action); ?></span>
                </p>
                <?php if($log->old_values || $log->new_values): ?>
                    <details class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                        <summary class="cursor-pointer font-medium"><?php echo e(__('Change details')); ?></summary>
                        <?php if($log->old_values): ?>
                            <p class="mt-1"><span class="font-semibold"><?php echo e(__('Before')); ?>:</span> <?php echo e(json_encode($log->old_values)); ?></p>
                        <?php endif; ?>
                        <?php if($log->new_values): ?>
                            <p class="mt-1"><span class="font-semibold"><?php echo e(__('After')); ?>:</span> <?php echo e(json_encode($log->new_values)); ?></p>
                        <?php endif; ?>
                    </details>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="px-4 py-8 text-center text-sm text-gray-500"><?php echo e($empty); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/activity-feed.blade.php ENDPATH**/ ?>