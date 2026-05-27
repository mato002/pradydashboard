@props([
    'href' => null,
])

@php
    $clickable = filled($href) && $href !== '#';
@endphp

<tr
    @if ($clickable)
        data-href="{{ $href }}"
        tabindex="0"
        role="link"
    @endif
    {{ $attributes->class($clickable ? ['cursor-pointer'] : []) }}
>
    {{ $slot }}
</tr>
