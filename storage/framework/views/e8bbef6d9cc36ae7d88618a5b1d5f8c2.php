<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'group' => 'control_plane',
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
    'group' => 'control_plane',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $links = collect(config("admin.quick_links.{$group}", []))
        ->map(function (array $link): array {
            $href = isset($link['route']) ? route($link['route']) : ($link['href'] ?? '#');
            $active = isset($link['route']) && request()->routeIs($link['route']);

            return array_merge($link, compact('href', 'active'));
        });
?>

<?php if($links->isNotEmpty()): ?>
    <nav <?php echo e($attributes->merge(['class' => 'flex flex-wrap gap-2'])); ?> aria-label="<?php echo e(__('Quick links')); ?>">
        <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($link['href']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition',
                    'border-indigo-500/40 bg-indigo-500/10 text-indigo-700 dark:text-indigo-300' => $link['active'],
                    'border-slate-200/80 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' => ! $link['active'],
                ]); ?>"
            >
                <?php echo e(__($link['label'])); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/quick-links.blade.php ENDPATH**/ ?>