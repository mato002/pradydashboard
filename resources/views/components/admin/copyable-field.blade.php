@props([
    'label',
    'value' => '',
    'mono' => true,
    'masked' => false,
])

@php
    $display = $masked && strlen((string) $value) > 12
        ? substr((string) $value, 0, 8).'…'.substr((string) $value, -4)
        : (string) $value;
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200/80 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/40']) }}
    x-data="{ copied: false, reveal: {{ $masked ? 'false' : 'true' }} }"
>
    <div class="flex items-start justify-between gap-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
        <div class="flex shrink-0 gap-1">
            @if ($masked)
                <button
                    type="button"
                    @click="reveal = !reveal"
                    class="rounded-md px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-white dark:text-slate-300 dark:ring-slate-600"
                    x-text="reveal ? @js(__('Hide')) : @js(__('Show'))"
                ></button>
            @endif
            <button
                type="button"
                @click="navigator.clipboard.writeText(@js((string) $value)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                class="rounded-md bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-indigo-500"
                x-text="copied ? @js(__('Copied')) : @js(__('Copy'))"
            ></button>
        </div>
    </div>
    <p @class([
        'mt-2 break-all text-xs text-slate-800 dark:text-slate-100',
        'font-mono' => $mono,
    ])>
        <span x-show="reveal" x-cloak>{{ $value ?: '—' }}</span>
        @if ($masked)
            <span x-show="!reveal">{{ $display ?: '—' }}</span>
        @endif
    </p>
</div>
