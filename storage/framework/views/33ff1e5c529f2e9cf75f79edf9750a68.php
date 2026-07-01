<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'badge' => null,
    'backHref',
    'backLabel' => null,
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
    'subtitle' => null,
    'badge' => null,
    'backHref',
    'backLabel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $backLabel = $backLabel ?? __('Back');
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="<?php echo e($backHref); ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                <?php echo e($backLabel); ?>

            </a>
            <?php if($badge): ?>
                <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e($badge); ?></p>
            <?php endif; ?>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e($title); ?></h2>
            <?php if($subtitle): ?>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php if(isset($actions)): ?>
            <div class="flex flex-wrap items-center gap-2">
                <?php echo e($actions); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php if(session('status')): ?>
        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/form-shell.blade.php ENDPATH**/ ?>