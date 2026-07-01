<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id',
    'label',
    'defaultOpen' => false,
    'icon' => null,
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
    'id',
    'label',
    'defaultOpen' => false,
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    class="relative"
    x-data="{
        flyout: false,
        groupId: <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>,
        defaultOpen: <?php echo \Illuminate\Support\Js::from($defaultOpen)->toHtml() ?>,
        closeFlyout() {
            this.flyout = false;
        },
        isOpen() {
            return $store.sidebar.isGroupOpen(this.groupId, this.defaultOpen);
        },
    }"
    @keydown.escape.window="closeFlyout()"
    @sidebar-close-flyout.window="closeFlyout()"
>
    <button
        type="button"
        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-left text-[13px] font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
        :class="isOpen() && !$store.sidebar.collapsed ? 'bg-white/5 text-white' : ''"
        @click="
            if ($store.sidebar.collapsed && window.matchMedia('(min-width: 1024px)').matches) {
                flyout = !flyout;
            } else {
                $store.sidebar.toggleGroup(groupId, defaultOpen);
            }
        "
        :title="<?php echo \Illuminate\Support\Js::from($label)->toHtml() ?>"
        :aria-expanded="isOpen() || flyout"
    >
        <?php if($icon): ?>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-300 ring-1 ring-white/10">
                <?php echo $icon; ?>

            </span>
        <?php endif; ?>
        <span class="min-w-0 flex-1 truncate" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"><?php echo e($label); ?></span>
        <svg
            class="h-4 w-4 shrink-0 text-slate-500 transition-transform duration-200"
            :class="[
                $store.sidebar.collapsed ? 'lg:hidden' : '',
                isOpen() ? 'rotate-180' : '',
            ]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div
        x-show="(isOpen() && !$store.sidebar.collapsed) || (flyout && $store.sidebar.collapsed)"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.outside="if ($store.sidebar.collapsed) { closeFlyout(); }"
        x-cloak
        data-sidebar-popover
        :class="$store.sidebar.collapsed
            ? 'absolute left-full top-0 z-[60] ml-2 hidden min-w-[12.5rem] rounded-xl border border-sidebar-border bg-sidebar py-2 shadow-2xl ring-1 ring-white/10 lg:block'
            : 'mt-0.5 space-y-0.5 pb-1'"
    >
        <?php if($icon): ?>
            <p class="mb-1 hidden px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500 lg:block" :class="$store.sidebar.collapsed ? '' : 'lg:!hidden'"><?php echo e($label); ?></p>
        <?php endif; ?>
        <div :class="$store.sidebar.collapsed ? 'space-y-0.5 px-1.5' : ''">
            <?php echo e($slot); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/admin/sidebar-group.blade.php ENDPATH**/ ?>