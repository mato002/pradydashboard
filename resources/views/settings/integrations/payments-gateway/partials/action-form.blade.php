@props([
    'action',
    'label',
    'confirm',
    'variant' => 'warning',
])

@php
    $classes = $variant === 'danger'
        ? 'border-rose-200 text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-300 dark:hover:bg-rose-950'
        : 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-950';
@endphp

<form method="post" action="{{ $action }}" onsubmit="return confirm(@js($confirm))">
    @csrf
    <button type="submit" class="rounded-xl border px-3 py-2 text-xs font-semibold {{ $classes }}">
        {{ $label }}
    </button>
</form>
