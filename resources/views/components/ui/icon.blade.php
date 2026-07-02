@props([
    'name',
    'variant' => 'solid',
    'class' => 'h-4 w-4',
])

@php
    $prefix = match ($variant) {
        'regular' => 'fa-regular',
        'brands' => 'fa-brands',
        default => 'fa-solid',
    };
@endphp

<i
    {{ $attributes->merge([
        'class' => trim("{$prefix} fa-{$name} {$class}"),
        'aria-hidden' => 'true',
    ]) }}
></i>
