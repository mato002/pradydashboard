@props([
    'points' => [],
    'strokeClass' => 'stroke-indigo-500',
    'fillClass' => 'fill-indigo-500/10',
])

@php
    $pts = collect($points)->values()->take(12)->map(fn ($v) => (float) $v)->all();
    if (count($pts) < 2) {
        $pts = [40, 55, 48, 62, 58, 70, 66, 74];
    }
    $w = 120;
    $h = 36;
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
@endphp

<svg class="h-9 w-[7.5rem] shrink-0 overflow-visible" viewBox="0 0 {{ $w }} {{ $h }}" fill="none" aria-hidden="true">
    <polygon points="{{ $polygon }}" class="{{ $fillClass }}" />
    <polyline points="{{ $line }}" class="{{ $strokeClass }} fill-none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
</svg>
