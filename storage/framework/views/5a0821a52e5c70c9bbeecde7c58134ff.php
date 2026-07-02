<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'value' => 0,
    'sublabel' => null,
    'trend' => null,
    'tone' => 'indigo',
    'points' => [],
    'animate' => true,
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
    'value' => 0,
    'sublabel' => null,
    'trend' => null,
    'tone' => 'indigo',
    'points' => [],
    'animate' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $tones = [
        'indigo' => [
            'icon' => 'from-indigo-500 to-violet-600 shadow-indigo-500/25',
            'spark' => 'stroke-indigo-500',
            'fill' => 'fill-indigo-500/10',
        ],
        'emerald' => [
            'icon' => 'from-emerald-500 to-teal-600 shadow-emerald-500/25',
            'spark' => 'stroke-emerald-500',
            'fill' => 'fill-emerald-500/10',
        ],
        'amber' => [
            'icon' => 'from-amber-500 to-orange-600 shadow-amber-500/25',
            'spark' => 'stroke-amber-500',
            'fill' => 'fill-amber-500/10',
        ],
        'rose' => [
            'icon' => 'from-rose-500 to-red-600 shadow-rose-500/25',
            'spark' => 'stroke-rose-500',
            'fill' => 'fill-rose-500/10',
        ],
        'violet' => [
            'icon' => 'from-violet-500 to-fuchsia-600 shadow-violet-500/25',
            'spark' => 'stroke-violet-500',
            'fill' => 'fill-violet-500/10',
        ],
        'sky' => [
            'icon' => 'from-sky-500 to-blue-600 shadow-sky-500/25',
            'spark' => 'stroke-sky-500',
            'fill' => 'fill-sky-500/10',
        ],
    ];
    $t = $tones[$tone] ?? $tones['indigo'];
    $numeric = is_numeric($value);
?>

<div <?php echo e($attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card transition-shadow duration-200 hover:shadow-card-hover dark:border-slate-800/80 dark:bg-slate-900/60'])); ?>>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white to-slate-50/40 opacity-90 dark:from-slate-900 dark:to-slate-950/40"></div>
    <div class="relative flex items-start justify-between gap-3">
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?php echo e($title); ?></p>
            <div class="flex flex-wrap items-baseline gap-2">
                <?php if($numeric && $animate): ?>
                    <p class="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white" x-data="countUp(<?php echo e((int) $value); ?>)" x-text="display">0</p>
                <?php else: ?>
                    <p class="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white"><?php echo e($value); ?></p>
                <?php endif; ?>
                <?php if($trend): ?>
                    <?php
                        $trendUp = str_starts_with(ltrim((string) $trend), '+');
                        $trendIcon = match ($trend) {
                            'check', '✓' => 'check',
                            'xmark', '✗' => 'xmark',
                            default => null,
                        };
                    ?>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1',
                        'bg-emerald-500/10 text-emerald-700 ring-emerald-500/15 dark:text-emerald-300' => $trendUp || $trendIcon === 'check',
                        'bg-rose-500/10 text-rose-700 ring-rose-500/15 dark:text-rose-300' => ! $trendUp && $trendIcon !== 'check',
                    ]); ?>">
                        <?php if($trendIcon): ?>
                            <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => $trendIcon,'class' => 'text-[10px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trendIcon),'class' => 'text-[10px]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                        <?php else: ?>
                            <?php echo e($trend); ?>

                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if($sublabel): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo $sublabel; ?></p>
            <?php endif; ?>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br <?php echo e($t['icon']); ?> text-white shadow-lg">
                <?php echo $icon ?? ''; ?>

            </div>
            <?php if (isset($component)) { $__componentOriginal3d112de9c6878cf313ff359ec2ca014b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d112de9c6878cf313ff359ec2ca014b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.sparkline','data' => ['points' => $points,'strokeClass' => $t['spark'],'fillClass' => $t['fill']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.sparkline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['points' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($points),'stroke-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t['spark']),'fill-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t['fill'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d112de9c6878cf313ff359ec2ca014b)): ?>
<?php $attributes = $__attributesOriginal3d112de9c6878cf313ff359ec2ca014b; ?>
<?php unset($__attributesOriginal3d112de9c6878cf313ff359ec2ca014b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d112de9c6878cf313ff359ec2ca014b)): ?>
<?php $component = $__componentOriginal3d112de9c6878cf313ff359ec2ca014b; ?>
<?php unset($__componentOriginal3d112de9c6878cf313ff359ec2ca014b); ?>
<?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/ui/kpi-card.blade.php ENDPATH**/ ?>