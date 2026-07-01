@props([
    'name',
    'variant' => 'solid',
])

@php
    $styleClass = match ($variant) {
        'regular' => 'fa-regular',
        'brands' => 'fa-brands',
        default => 'fa-solid',
    };
@endphp

<i {{ $attributes->merge(['class' => trim("$styleClass fa-$name"), 'aria-hidden' => 'true']) }}></i>
