@props([
    'type' => 'submit',
    'loadingText' => null,
])

@php
    $loadingLabel = $loadingText ?? __('Please wait…');
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' =>
            'group relative inline-flex w-full select-none items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 px-4 py-3.5 text-sm font-bold tracking-tight text-white shadow-[0_1px_0_rgba(255,255,255,0.15)_inset,0_8px_32px_-4px_rgba(79,70,229,0.55),0_0_0_1px_rgba(255,255,255,0.08)] ring-1 ring-white/20 transition duration-200 hover:from-indigo-500 hover:via-indigo-500 hover:to-violet-500 hover:shadow-[0_1px_0_rgba(255,255,255,0.2)_inset,0_12px_40px_-4px_rgba(99,102,241,0.5)] hover:brightness-[1.02] active:scale-[0.985] active:brightness-[0.98] focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/45 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-[0_1px_0_rgba(255,255,255,0.08)_inset,0_8px_32px_-4px_rgba(67,56,202,0.45)] dark:ring-white/10 dark:focus-visible:ring-indigo-400/35',
    ]) }}
>
    <span
        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 transition group-hover:opacity-100"
        aria-hidden="true"
    ></span>
    <span
        class="inline-flex items-center gap-2 transition group-disabled:opacity-70"
        x-show="typeof submitting === 'undefined' || !submitting"
    >
        {{ $slot }}
    </span>
    <span
        class="absolute inset-0 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600"
        x-show="typeof submitting !== 'undefined' && submitting"
        x-cloak
    >
        <svg class="h-5 w-5 animate-spin text-white/90" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <span class="text-sm font-semibold">{{ $loadingLabel }}</span>
    </span>
    <span
        class="pointer-events-none absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/25 to-transparent opacity-0 transition group-hover:translate-x-[100%] group-hover:opacity-100 group-hover:duration-700"
        aria-hidden="true"
    ></span>
</button>
