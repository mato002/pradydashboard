<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'points' => [],
    'strokeClass' => 'stroke-indigo-500',
    'fillClass' => 'fill-indigo-500/10',
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
    'points' => [],
    'strokeClass' => 'stroke-indigo-500',
    'fillClass' => 'fill-indigo-500/10',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $pts = collect($points)->values()->take(12)->map(fn ($v) => (float) $v)->all();
    $hasChart = count($pts) >= 2;
    $w = 120;
    $h = 36;

    if ($hasChart) {
        $min = min($pts);
        $max = max($pts);
        $range = max(1e-6, $max - $min);
        $linePts = [];
        foreach ($pts as $i => $v) {
            $x = ($i / (count($pts) - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 4) - 2;
            $linePts[] = round($x, 2).','.round($y, 2);
        }
        $line = implode(' ', $linePts);
        $polygon = '0,'.$h.' '.$line.' '.$w.','.$h;
    }
?>

<?php if($hasChart): ?>
    <svg class="h-9 w-[7.5rem] shrink-0 overflow-visible" viewBox="0 0 <?php echo e($w); ?> <?php echo e($h); ?>" fill="none" aria-hidden="true">
        <polygon points="<?php echo e($polygon); ?>" class="<?php echo e($fillClass); ?>" />
        <polyline points="<?php echo e($line); ?>" class="<?php echo e($strokeClass); ?> fill-none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
    </svg>
<?php else: ?>
    <span class="inline-block h-9 w-[7.5rem] shrink-0 rounded-lg bg-slate-100/80 dark:bg-slate-800/60" aria-hidden="true"></span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/ui/sparkline.blade.php ENDPATH**/ ?>