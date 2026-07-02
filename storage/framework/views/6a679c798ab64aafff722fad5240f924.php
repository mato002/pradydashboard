<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action' => null,
    'exportHref' => null,
    'exportLabel' => __('Export CSV'),
    'searchPlaceholder' => __('Search…'),
    'searchValue' => '',
    'searchName' => 'q',
    'autoSearch' => true,
    'resetHref' => null,
    'resultCount' => null,
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
    'action' => null,
    'exportHref' => null,
    'exportLabel' => __('Export CSV'),
    'searchPlaceholder' => __('Search…'),
    'searchValue' => '',
    'searchName' => 'q',
    'autoSearch' => true,
    'resetHref' => null,
    'resultCount' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $action = $action ?? url()->current();
    $resetHref = $resetHref ?? $action;
    $hasFilters = trim($slot) !== '';
?>

<div <?php echo e($attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white p-3 shadow-card dark:border-slate-800 dark:bg-slate-900/60'])); ?>>
    <form
        method="GET"
        action="<?php echo e($action); ?>"
        <?php if($autoSearch): ?> x-data <?php endif; ?>
        class="flex flex-col gap-3"
    >
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 w-full flex-1 sm:min-w-[12rem]">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <input
                    type="search"
                    name="<?php echo e($searchName); ?>"
                    value="<?php echo e($searchValue); ?>"
                    placeholder="<?php echo e($searchPlaceholder); ?>"
                    <?php if($autoSearch): ?> @input.debounce.400ms="$el.form.submit()" <?php endif; ?>
                    class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
            </div>

            <?php if($hasFilters): ?>
                <?php echo e($slot); ?>

            <?php endif; ?>

            <?php if(! $autoSearch): ?>
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
                    <?php echo e(__('Apply')); ?>

                </button>
            <?php endif; ?>

            <?php if(request()->hasAny(array_merge([$searchName, 'status', 'environment', 'gateway', 'product_id', 'server_id', 'from', 'to', 'allowed', 'access_level'], $hasFilters ? ['filter'] : []))): ?>
                <a href="<?php echo e($resetHref); ?>" class="inline-flex items-center rounded-xl border border-slate-200/80 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    <?php echo e(__('Reset')); ?>

                </a>
            <?php endif; ?>

            <?php if($exportHref): ?>
                <a href="<?php echo e($exportHref); ?>" data-prady-full-nav data-turbo="false" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-500/15 dark:text-emerald-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
                    <?php echo e($exportLabel); ?>

                </a>
            <?php endif; ?>
        </div>

        <?php if($resultCount !== null): ?>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                <?php echo e(__('Showing :count results', ['count' => number_format((int) $resultCount)])); ?>

            </p>
        <?php endif; ?>
    </form>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/list-toolbar.blade.php ENDPATH**/ ?>