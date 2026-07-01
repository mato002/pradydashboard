<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'name',
    'id' => null,
    'type' => 'text',
    'autocomplete' => null,
    'value' => null,
    'revealable' => false,
    'required' => false,
    'autofocus' => false,
    'readonly' => false,
    'placeholder' => '',
    'hint' => null,
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
    'name',
    'id' => null,
    'type' => 'text',
    'autocomplete' => null,
    'value' => null,
    'revealable' => false,
    'required' => false,
    'autofocus' => false,
    'readonly' => false,
    'placeholder' => '',
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $id = $id ?? $name;
    $messages = $errors->get($name);
    $isPassword = $type === 'password' && $revealable;
    $iconPad = isset($icon) ? 'pl-11' : 'pl-4';
    $labelLeft = isset($icon) ? 'left-11' : 'left-4';
    $readOnlyClass = $readonly
        ? 'cursor-not-allowed bg-slate-50/90 text-slate-600 dark:bg-slate-900/60 dark:text-slate-300'
        : '';
    $peerBase =
        'peer block h-[3.25rem] w-full rounded-xl border bg-white/95 text-sm text-slate-900 shadow-sm transition-all duration-200 placeholder:text-transparent ' .
        'border-slate-200/90 pt-5 pb-2.5 ' .
        'hover:border-slate-300/90 hover:shadow-md ' .
        'auth-input-glow focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/[0.18] ' .
        'dark:border-white/[0.08] dark:bg-slate-950/50 dark:text-slate-100 dark:hover:border-white/15 ' .
        'dark:focus:border-indigo-400 dark:focus:ring-indigo-400/20 ' .
        $iconPad .
        ' ' .
        $readOnlyClass;
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['space-y-1.5', $attributes->get('class')]); ?>">
    <?php if($isPassword): ?>
        <div x-data="{ show: false }" class="group relative">
            <?php if(isset($icon)): ?>
                <span
                    class="pointer-events-none absolute left-0 top-0 z-[2] flex h-[3.25rem] w-11 items-center justify-center text-slate-400 transition group-focus-within:text-indigo-500 dark:text-slate-500 dark:group-focus-within:text-indigo-400"
                >
                    <?php echo e($icon); ?>

                </span>
            <?php endif; ?>
            <input
                :type="show ? 'text' : 'password'"
                name="<?php echo e($name); ?>"
                id="<?php echo e($id); ?>"
                <?php if($value !== null): ?> value="<?php echo e($value); ?>" <?php endif; ?>
                <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?>
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readonly): ?> readonly <?php endif; ?>
                @autofocus($autofocus)
                placeholder=" "
                class="<?php echo e($peerBase); ?> pr-12"
                <?php echo e($attributes->except('class')); ?>

            />
            <label
                for="<?php echo e($id); ?>"
                class="<?php echo e($labelLeft); ?> pointer-events-none absolute top-1/2 z-[1] origin-[0] -translate-y-1/2 text-[15px] font-medium text-slate-500 transition-all duration-200 ease-out peer-focus:top-[0.65rem] peer-focus:-translate-y-0 peer-focus:scale-[0.72] peer-focus:text-indigo-600 dark:text-slate-400 dark:peer-focus:text-indigo-300 peer-[&:not(:placeholder-shown)]:top-[0.65rem] peer-[&:not(:placeholder-shown)]:-translate-y-0 peer-[&:not(:placeholder-shown)]:scale-[0.72]"
            >
                <?php echo e($label); ?>

            </label>
            <button
                type="button"
                class="absolute inset-y-0 right-0 z-[2] flex w-12 items-center justify-center rounded-r-2xl text-slate-400 transition hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500/40 dark:text-slate-500 dark:hover:text-slate-200"
                @click="show = !show"
                :aria-pressed="show"
                tabindex="-1"
            >
                <span x-show="!show">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </span>
                <span x-show="show" x-cloak>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </span>
                <span class="sr-only"><?php echo e(__('Toggle password visibility')); ?></span>
            </button>
        </div>
    <?php else: ?>
        <div class="group relative">
            <?php if(isset($icon)): ?>
                <span
                    class="pointer-events-none absolute left-0 top-0 z-[2] flex h-[3.25rem] w-11 items-center justify-center text-slate-400 transition group-focus-within:text-indigo-500 dark:text-slate-500 dark:group-focus-within:text-indigo-400"
                >
                    <?php echo e($icon); ?>

                </span>
            <?php endif; ?>
            <input
                name="<?php echo e($name); ?>"
                id="<?php echo e($id); ?>"
                type="<?php echo e($type); ?>"
                <?php if($value !== null): ?> value="<?php echo e($value); ?>" <?php endif; ?>
                <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?>
                <?php if($required): echo 'required'; endif; ?>
                <?php if($readonly): ?> readonly <?php endif; ?>
                @autofocus($autofocus)
                placeholder=" "
                class="<?php echo e($peerBase); ?> pr-4"
                <?php echo e($attributes->except('class')); ?>

            />
            <label
                for="<?php echo e($id); ?>"
                class="<?php echo e($labelLeft); ?> pointer-events-none absolute top-1/2 z-[1] origin-[0] -translate-y-1/2 text-[15px] font-medium text-slate-500 transition-all duration-200 ease-out peer-focus:top-[0.65rem] peer-focus:-translate-y-0 peer-focus:scale-[0.72] peer-focus:text-indigo-600 dark:text-slate-400 dark:peer-focus:text-indigo-300 peer-[&:not(:placeholder-shown)]:top-[0.65rem] peer-[&:not(:placeholder-shown)]:-translate-y-0 peer-[&:not(:placeholder-shown)]:scale-[0.72]"
            >
                <?php echo e($label); ?>

            </label>
        </div>
    <?php endif; ?>

    <?php if($hint): ?>
        <p class="px-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"><?php echo e($hint); ?></p>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $messages,'class' => 'mt-1.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($messages),'class' => 'mt-1.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/auth/auth-input.blade.php ENDPATH**/ ?>