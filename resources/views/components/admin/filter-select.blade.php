@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'options' => [],
    'value' => '',
    'auto' => true,
])

<label class="inline-flex w-full min-w-0 flex-col gap-1 sm:w-auto">
    @if ($label)
        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</span>
    @endif
    <select
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-2 pr-8 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 sm:w-auto']) }}
        @if ($auto) @change="$el.form.submit()" @endif
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
</label>
