@props([
    'fallback' => null,
])

@php
    $logoUrl = \App\Models\Setting::logoUrl();
    $fallback = $fallback ?? strtoupper(substr(config('app.name', 'P'), 0, 1));
@endphp

@if ($logoUrl)
    <img
        src="{{ $logoUrl }}"
        alt="{{ config('app.name') }}"
        {{ $attributes->merge(['class' => 'object-contain']) }}
    />
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center font-bold leading-none']) }}>
        {{ $fallback }}
    </span>
@endif
