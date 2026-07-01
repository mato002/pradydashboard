<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'severity' => 'info',
    'severityLabel' => null,
    'title',
    'description' => null,
    'entity' => null,
    'timeLabel' => null,
    'url' => null,
    'actions' => [],
    'riskKey' => null,
    'nested' => false,
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
    'severity' => 'info',
    'severityLabel' => null,
    'title',
    'description' => null,
    'entity' => null,
    'timeLabel' => null,
    'url' => null,
    'actions' => [],
    'riskKey' => null,
    'nested' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $severityStyles = [
        'critical' => [
            'border' => 'border-l-rose-500',
            'icon' => 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
            'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        ],
        'warning' => [
            'border' => 'border-l-amber-500',
            'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
            'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-200',
        ],
        'info' => [
            'border' => 'border-l-sky-500',
            'icon' => 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
            'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        ],
    ];
    $style = $severityStyles[$severity] ?? $severityStyles['info'];
    $severityIcons = [
        'critical' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'warning' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    ];
?>

<article class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'flex flex-col gap-3 border-l-4 bg-white/80 px-3 py-3 dark:bg-slate-900/40 sm:flex-row sm:items-center sm:gap-4',
    $style['border'],
    $nested ? 'rounded-lg border border-slate-200/80 dark:border-slate-800' : 'border-b border-slate-100/80 last:border-b-0 dark:border-slate-800/80',
]); ?>">
    <div class="flex min-w-0 flex-1 items-start gap-3 sm:max-w-[38%]">
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl', $style['icon']]); ?>" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($severityIcons[$severity] ?? $severityIcons['info']); ?>" />
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide', $style['badge']]); ?>">
                    <?php echo e($severityLabel ?? ucfirst($severity)); ?>

                </span>
                <?php if($timeLabel): ?>
                    <span class="text-[10px] font-medium text-slate-400"><?php echo e($timeLabel); ?></span>
                <?php endif; ?>
            </div>
            <h4 class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                <?php if($url): ?>
                    <a href="<?php echo e($url); ?>" class="transition hover:text-indigo-600 dark:hover:text-indigo-400"><?php echo e($title); ?></a>
                <?php else: ?>
                    <?php echo e($title); ?>

                <?php endif; ?>
            </h4>
            <?php if($entity): ?>
                <p class="mt-0.5 truncate text-xs font-medium text-indigo-700 dark:text-indigo-300"><?php echo e($entity); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="min-w-0 flex-1 text-xs text-slate-600 dark:text-slate-400 sm:px-2">
        <?php if($description): ?>
            <p class="line-clamp-2"><?php echo e($description); ?></p>
        <?php endif; ?>
        <?php echo e($context ?? ''); ?>

    </div>

    <div class="flex shrink-0 items-center justify-end gap-2 sm:w-auto">
        <?php if(count($actions) > 0): ?>
            <?php if (isset($component)) { $__componentOriginal110b8ff0bc0114fb450fefaa85301d27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-actions-menu','data' => ['align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-actions-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right']); ?>
                <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(($action['type'] ?? null) === 'acknowledge'): ?>
                        <form method="POST" action="<?php echo e(route('risk-center.acknowledge')); ?>" class="block" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Dismiss this risk from the overview?'))->toHtml() ?>)">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="risk_key" value="<?php echo e($action['risk_key'] ?? $riskKey); ?>" />
                            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800" role="menuitem">
                                <?php echo e($action['label']); ?>

                            </button>
                        </form>
                    <?php elseif(! empty($action['href'])): ?>
                        <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => $action['href'],'method' => $action['method'] ?? null,'confirm' => $action['confirm'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['href']),'method' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['method'] ?? null),'confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action['confirm'] ?? null)]); ?><?php echo e($action['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $attributes = $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $component = $__componentOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
</article>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/risk-item-card.blade.php ENDPATH**/ ?>