<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'showMarketing' => true,
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
    'showMarketing' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="authShell()"
    class="relative flex min-h-[100dvh] min-h-screen flex-col overflow-x-hidden bg-[#f8fafc] text-slate-900 dark:bg-[#020617] dark:text-slate-100"
>
    <?php echo $__env->make('components.auth.partials.auth-topnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="flex min-h-0 flex-1 flex-col lg:flex-row">
        <?php if($showMarketing): ?>
            
            <div class="relative overflow-hidden bg-enterprise-mesh px-5 py-5 lg:hidden">
                <div class="pointer-events-none absolute inset-0 bg-auth-glow opacity-60" aria-hidden="true"></div>
                <div class="relative flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                            <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['class' => 'h-6 w-6 text-cyan-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-cyan-300']); ?>
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
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-200/90">PradytecAI</p>
                            <p class="text-sm font-bold text-white"><?php echo e(__('Operations Cloud')); ?></p>
                        </div>
                    </div>
                    <span class="rounded-full border border-emerald-400/30 bg-emerald-500/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-300">
                        <?php echo e(__('Secure')); ?>

                    </span>
                </div>
                <p class="relative mt-3 text-sm font-semibold leading-snug text-white/95">
                    <?php echo e(__('Enterprise infrastructure control plane')); ?>

                </p>
            </div>

            <?php echo $__env->make('components.auth.partials.enterprise-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        
        <main class="relative flex min-h-0 flex-1 flex-col lg:w-[48%] lg:shrink-0 xl:w-[50%]">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-600/15"></div>
                <div class="absolute -bottom-24 left-1/4 h-72 w-72 rounded-full bg-violet-400/15 blur-3xl dark:bg-violet-600/10"></div>
                <div
                    class="absolute inset-0 bg-mesh-light opacity-80 dark:bg-mesh-dark dark:opacity-100"
                ></div>
            </div>

            <div class="relative z-[1] flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
                <div class="w-full max-w-[400px] animate-auth-fade-up">
                    <?php echo e($slot); ?>

                </div>
            </div>

            <footer class="relative z-[1] shrink-0 border-t border-slate-200/70 px-4 py-3 text-center text-[11px] text-slate-500 dark:border-white/[0.06] dark:text-slate-500 sm:px-8">
                <span>© <?php echo e(now()->year); ?> PradytecAI · <?php echo e(__('All rights reserved.')); ?></span>
                <span class="mx-2 opacity-40">·</span>
                <span class="tabular-nums"><?php echo e(__('Version')); ?> <?php echo e(config('app.version', '1.0.0')); ?></span>
            </footer>
        </main>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/auth/auth-layout.blade.php ENDPATH**/ ?>