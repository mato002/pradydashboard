<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'risks',
    'title' => __('Operational risks'),
    'empty' => __('No risks detected for this record.'),
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
    'risks',
    'title' => __('Operational risks'),
    'empty' => __('No risks detected for this record.'),
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($risks->isNotEmpty()): ?>
    <div <?php echo e($attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900'])); ?>>
        <?php if($title): ?>
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e($title); ?></h3>
            </div>
        <?php endif; ?>
        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            <?php $__currentLoopData = $risks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $severityClass = match ($risk['severity']) {
                        'critical' => 'border-rose-200 bg-rose-50/60 dark:border-rose-900 dark:bg-rose-950/30',
                        'warning' => 'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20',
                        default => 'border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950/30',
                    };
                    $muted = $risk['acknowledged'] ?? false;
                ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['px-4 py-3 text-sm border-l-4', $severityClass, $muted ? 'opacity-50' : '']); ?>">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                <?php if(! empty($risk['url'])): ?>
                                    <a href="<?php echo e($risk['url']); ?>" class="hover:text-indigo-600 hover:underline"><?php echo e($risk['title']); ?></a>
                                <?php else: ?>
                                    <?php echo e($risk['title']); ?>

                                <?php endif; ?>
                                <?php if($muted): ?>
                                    <span class="ms-1 text-[10px] uppercase text-gray-400"><?php echo e(__('Acknowledged')); ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400"><?php echo e($risk['description']); ?></p>
                            <?php if (! ($compact)): ?>
                                <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-300"><?php echo e($risk['recommended_action']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if(! $muted): ?>
                            <form method="post" action="<?php echo e(route('risk-center.acknowledge')); ?>" class="shrink-0">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="risk_key" value="<?php echo e($risk['key']); ?>" />
                                <button type="submit" class="rounded border px-2 py-1 text-[10px] font-semibold uppercase"><?php echo e(__('Acknowledge')); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php elseif(! $compact): ?>
    <p class="text-sm text-gray-500"><?php echo e($empty); ?></p>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/risk-cards.blade.php ENDPATH**/ ?>