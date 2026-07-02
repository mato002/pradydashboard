<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'showLogo' => true,
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
    'title' => null,
    'subtitle' => null,
    'showLogo' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'relative'])); ?>>
    
    <div
        class="pointer-events-none absolute -inset-[1px] rounded-[1.35rem] bg-auth-card-border opacity-60 blur-[0.5px] animate-auth-border-glow"
        aria-hidden="true"
    ></div>

    <div class="auth-glass-card-light relative p-6 sm:p-7">
        <div
            class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-indigo-400/15 blur-3xl dark:bg-indigo-500/10"
            aria-hidden="true"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-12 -left-12 h-32 w-32 rounded-full bg-cyan-400/10 blur-3xl dark:bg-cyan-500/10"
            aria-hidden="true"
        ></div>

        <div class="relative">
            <?php if($showLogo): ?>
                <div class="mb-5 flex justify-center">
                    <a
                        href="<?php echo e(route('home')); ?>"
                        class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 via-violet-600 to-cyan-500 p-[1px] shadow-lg transition duration-300 hover:scale-[1.04] animate-auth-logo-pulse"
                    >
                        <span class="flex h-full w-full items-center justify-center rounded-[11px] bg-white dark:bg-slate-950">
                            <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['class' => 'h-7 w-7 text-indigo-600 dark:text-cyan-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-7 w-7 text-indigo-600 dark:text-cyan-300']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
                        </span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if($title || $subtitle): ?>
                <header class="mb-5 text-center">
                    <?php if($title): ?>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-[1.35rem]">
                            <?php echo e($title); ?>

                        </h2>
                    <?php endif; ?>
                    <?php if($subtitle): ?>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600 dark:text-slate-400">
                            <?php echo e($subtitle); ?>

                        </p>
                    <?php endif; ?>
                </header>
            <?php endif; ?>

            <?php echo e($slot); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/auth/auth-card.blade.php ENDPATH**/ ?>