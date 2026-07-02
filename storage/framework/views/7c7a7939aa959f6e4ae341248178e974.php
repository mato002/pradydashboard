<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value' => '',
    'mono' => true,
    'masked' => false,
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
    'label',
    'value' => '',
    'mono' => true,
    'masked' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $display = $masked && strlen((string) $value) > 12
        ? substr((string) $value, 0, 8).'…'.substr((string) $value, -4)
        : (string) $value;
?>

<div
    <?php echo e($attributes->merge(['class' => 'rounded-lg border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/40'])); ?>

    x-data="{ copied: false, reveal: <?php echo e($masked ? 'false' : 'true'); ?> }"
>
    <div class="flex items-start justify-between gap-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
        <div class="flex shrink-0 gap-1">
            <?php if($masked): ?>
                <button
                    type="button"
                    @click="reveal = !reveal"
                    class="rounded-md px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-white dark:text-slate-300 dark:ring-slate-600"
                    x-text="reveal ? <?php echo \Illuminate\Support\Js::from(__('Hide'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Show'))->toHtml() ?>"
                ></button>
            <?php endif; ?>
            <button
                type="button"
                @click="navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from((string) $value)->toHtml() ?>).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                class="rounded-md bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-indigo-500"
                x-text="copied ? <?php echo \Illuminate\Support\Js::from(__('Copied'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Copy'))->toHtml() ?>"
            ></button>
        </div>
    </div>
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'mt-2 break-all text-xs text-slate-800 dark:text-slate-100',
        'font-mono' => $mono,
    ]); ?>">
        <span x-show="reveal" x-cloak><?php echo e($value ?: '—'); ?></span>
        <?php if($masked): ?>
            <span x-show="!reveal"><?php echo e($display ?: '—'); ?></span>
        <?php endif; ?>
    </p>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/copyable-field.blade.php ENDPATH**/ ?>