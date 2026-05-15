@props([
    'label',
    'name',
    'type' => 'text',
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'model' => null,
    'rows' => 3,
    'step' => null,
    'min' => null,
    'max' => null,
    'maxlength' => null,
    'value' => null,
    'masked' => false,
])

@php
    $inputId = str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $oldValue = old($name, $value);
@endphp

<div {{ $attributes->except(['x-on:input.debounce.400ms', 'x-on:input'])->merge(['class' => 'min-w-0']) }}>
    <label for="{{ $inputId }}" class="flex items-center justify-between gap-2">
        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
            {{ $label }}
            @if ($required)<span class="text-rose-500">*</span>@endif
        </span>
        @isset($badge)
            <span class="shrink-0">{{ $badge }}</span>
        @endisset
    </label>

    @if ($type === 'select')
        <select
            id="{{ $inputId }}"
            name="{{ $name }}"
            @if ($model) x-model="{{ $model }}" @endif
            @required($required)
            {{ $attributes->only(['x-on:input.debounce.400ms', 'x-on:input']) }}
            class="infra-provision-select"
        >
            {{ $slot }}
        </select>
    @elseif ($type === 'textarea')
        <textarea
            id="{{ $inputId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            @if ($model) x-model="{{ $model }}" @endif
            @required($required)
            placeholder="{{ $placeholder }}"
            {{ $attributes->only(['x-on:input.debounce.400ms', 'x-on:input']) }}
            class="infra-provision-input resize-y font-mono text-sm"
        >@unless($model){{ $oldValue }}@endunless</textarea>
    @else
        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $masked ? 'password' : $type }}"
            @unless($model) value="{{ $oldValue }}" @endunless
            @if ($model) x-model="{{ $model }}" @endif
            @if ($step) step="{{ $step }}" @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null) max="{{ $max }}" @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @required($required)
            placeholder="{{ $placeholder }}"
            @if ($masked) x-bind:type="showToken ? 'text' : 'password'" @endif
            {{ $attributes->only(['x-on:input.debounce.400ms', 'x-on:input']) }}
            @class([
                'infra-provision-input',
                'font-mono' => str_contains($name, 'ip'),
            ])
        />
    @endif

    @if ($hint)
        <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    <x-input-error class="mt-1.5" :messages="$errors->get($name)" />
</div>
