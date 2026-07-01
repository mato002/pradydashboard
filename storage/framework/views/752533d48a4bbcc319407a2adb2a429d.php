<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href',
    'active' => false,
    'label',
    'icon' => null,
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
    'href',
    'active' => false,
    'label',
    'icon' => null,
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
    $linkClass = $active
        ? 'bg-gradient-to-r from-indigo-500/20 to-violet-500/10 text-white shadow-inner shadow-indigo-500/10 ring-1 ring-inset ring-white/10'
        : 'text-slate-400 hover:bg-white/5 hover:text-white';
?>

<a
    href="<?php echo e($href); ?>"
    data-prady-nav
    @click="if ($store.sidebar.collapsed) { $dispatch('sidebar-close-flyout') }"
    <?php echo e($attributes->merge(['class' => trim("group flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium transition {$linkClass} " . ($nested ? 'pl-9' : ''))])); ?>

    title="<?php echo e($label); ?>"
>
    <?php if($icon): ?>
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10 group-hover:bg-white/10">
            <?php echo $icon; ?>

        </span>
    <?php elseif($nested): ?>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ml-1 h-1.5 w-1.5 shrink-0 rounded-full', 'bg-indigo-400' => $active, 'bg-slate-500' => ! $active]); ?>"></span>
    <?php endif; ?>
    <span class="sidebar-link-label truncate" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"><?php echo e($label); ?></span>
</a>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/sidebar-link.blade.php ENDPATH**/ ?>