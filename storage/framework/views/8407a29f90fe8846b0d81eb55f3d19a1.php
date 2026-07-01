<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url',
    'label' => null,
    'variant' => 'button',
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
    'url',
    'label' => null,
    'variant' => 'button',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $label ??= $variant === 'icon' ? __('PDF') : __('Download PDF');
?>

<div
    x-data="pdfDownloadLink()"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'inline-block' => in_array($variant, ['icon', 'menu'], true),
        'inline-flex flex-col items-start gap-1' => ! in_array($variant, ['icon', 'menu'], true),
    ]); ?>"
>
    <?php if($variant === 'menu'): ?>
        <button
            type="button"
            @click="download(<?php echo \Illuminate\Support\Js::from($url)->toHtml() ?>)"
            :disabled="downloading"
            <?php echo e($attributes->class([
                'flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-800',
            ])); ?>

            role="menuitem"
        >
            <span x-show="! downloading" class="inline-flex w-4 shrink-0 justify-center"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'file-pdf','class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'file-pdf','class' => 'text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
            <span x-show="downloading" x-cloak class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600" aria-hidden="true"></span>
            <span x-text="downloading ? <?php echo \Illuminate\Support\Js::from(__('Preparing PDF…'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($label)->toHtml() ?>"><?php echo e($label); ?></span>
        </button>
    <?php elseif($variant === 'icon'): ?>
        <button
            type="button"
            @click="download(<?php echo \Illuminate\Support\Js::from($url)->toHtml() ?>)"
            :disabled="downloading"
            :title="downloading ? <?php echo \Illuminate\Support\Js::from(__('Preparing PDF…'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($label)->toHtml() ?>"
            <?php echo e($attributes->class([
                'rounded p-1 text-slate-500 hover:bg-slate-100 disabled:cursor-wait disabled:opacity-60 dark:hover:bg-slate-800',
            ])); ?>

        >
            <span x-show="! downloading" aria-hidden="true"><?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'file-pdf']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'file-pdf']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?></span>
            <span x-show="downloading" x-cloak class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600" aria-hidden="true"></span>
            <span class="sr-only" x-text="downloading ? <?php echo \Illuminate\Support\Js::from(__('Preparing PDF…'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($label)->toHtml() ?>"><?php echo e($label); ?></span>
        </button>
    <?php else: ?>
        <button
            type="button"
            @click="download(<?php echo \Illuminate\Support\Js::from($url)->toHtml() ?>)"
            :disabled="downloading"
            <?php echo e($attributes->class([
                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 disabled:cursor-wait disabled:opacity-70 dark:hover:bg-slate-800',
            ])); ?>

        >
            <span
                x-show="downloading"
                x-cloak
                class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"
                aria-hidden="true"
            ></span>
            <span x-text="downloading ? <?php echo \Illuminate\Support\Js::from(__('Preparing PDF…'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($label)->toHtml() ?>"><?php echo e($label); ?></span>
        </button>
    <?php endif; ?>

    <p
        x-show="error"
        x-cloak
        x-text="error"
        class="max-w-xs text-[10px] text-rose-700 dark:text-rose-300"
    ></p>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/billing/pdf-download-link.blade.php ENDPATH**/ ?>