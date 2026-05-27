@props([
    'href' => null,
    'method' => null,
    'danger' => false,
    'confirm' => null,
])

@php
    $classes = 'flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium transition';
    $classes .= $danger
        ? ' text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10'
        : ' text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800';
@endphp

@if ($href && ! $method)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'role' => 'menuitem']) }}>{{ $slot }}</a>
@elseif ($href && $method)
    <form
        method="POST"
        action="{{ $href }}"
        class="block"
        @if ($confirm) onsubmit="return confirm(@js($confirm))" @endif
    >
        @csrf
        @if (strtolower((string) $method) !== 'post')
            @method($method)
        @endif
        <button type="submit" {{ $attributes->merge(['class' => $classes, 'role' => 'menuitem']) }}>{{ $slot }}</button>
    </form>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'role' => 'menuitem']) }}>{{ $slot }}</button>
@endif
