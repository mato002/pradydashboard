@props([
    'label',
    'name',
    'id' => null,
    'type' => 'text',
    'autocomplete' => null,
    'value' => null,
    'revealable' => false,
    'required' => false,
    'autofocus' => false,
    'readonly' => false,
    'placeholder' => '',
    'hint' => null,
])

@php
    $id = $id ?? $name;
    $messages = $errors->get($name);
    $isPassword = $type === 'password' && $revealable;
    $iconPad = isset($icon) ? 'pl-11' : 'pl-4';
    $labelLeft = isset($icon) ? 'left-11' : 'left-4';
    $readOnlyClass = $readonly
        ? 'cursor-not-allowed bg-slate-50/90 text-slate-600 dark:bg-slate-900/60 dark:text-slate-300'
        : '';
    $peerBase =
        'peer block h-14 w-full rounded-2xl border bg-white/95 text-sm text-slate-900 shadow-sm transition-all duration-200 placeholder:text-transparent ' .
        'border-slate-200/90 pt-5 pb-2.5 ' .
        'hover:border-slate-300/90 hover:shadow-md ' .
        'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/[0.18] focus:shadow-[0_0_0_1px_rgba(99,102,241,0.15)] ' .
        'dark:border-white/[0.08] dark:bg-slate-950/50 dark:text-slate-100 dark:hover:border-white/15 ' .
        'dark:focus:border-indigo-400 dark:focus:ring-indigo-400/20 ' .
        $iconPad .
        ' ' .
        $readOnlyClass;
@endphp

<div @class(['space-y-1.5', $attributes->get('class')])>
    @if ($isPassword)
        <div x-data="{ show: false }" class="group relative">
            @isset($icon)
                <span
                    class="pointer-events-none absolute left-0 top-0 z-[2] flex h-14 w-11 items-center justify-center text-slate-400 transition group-focus-within:text-indigo-500 dark:text-slate-500 dark:group-focus-within:text-indigo-400"
                >
                    {{ $icon }}
                </span>
            @endisset
            <input
                :type="show ? 'text' : 'password'"
                name="{{ $name }}"
                id="{{ $id }}"
                @if ($value !== null) value="{{ $value }}" @endif
                @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                @required($required)
                @if ($readonly) readonly @endif
                @autofocus($autofocus)
                placeholder=" "
                class="{{ $peerBase }} pr-12"
                {{ $attributes->except('class') }}
            />
            <label
                for="{{ $id }}"
                class="{{ $labelLeft }} pointer-events-none absolute top-1/2 z-[1] origin-[0] -translate-y-1/2 text-[15px] font-medium text-slate-500 transition-all duration-200 ease-out peer-focus:top-[0.65rem] peer-focus:-translate-y-0 peer-focus:scale-[0.72] peer-focus:text-indigo-600 dark:text-slate-400 dark:peer-focus:text-indigo-300 peer-[&:not(:placeholder-shown)]:top-[0.65rem] peer-[&:not(:placeholder-shown)]:-translate-y-0 peer-[&:not(:placeholder-shown)]:scale-[0.72]"
            >
                {{ $label }}
            </label>
            <button
                type="button"
                class="absolute inset-y-0 right-0 z-[2] flex w-12 items-center justify-center rounded-r-2xl text-slate-400 transition hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500/40 dark:text-slate-500 dark:hover:text-slate-200"
                @click="show = !show"
                :aria-pressed="show"
                tabindex="-1"
            >
                <span x-show="!show">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </span>
                <span x-show="show" x-cloak>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </span>
                <span class="sr-only">{{ __('Toggle password visibility') }}</span>
            </button>
        </div>
    @else
        <div class="group relative">
            @isset($icon)
                <span
                    class="pointer-events-none absolute left-0 top-0 z-[2] flex h-14 w-11 items-center justify-center text-slate-400 transition group-focus-within:text-indigo-500 dark:text-slate-500 dark:group-focus-within:text-indigo-400"
                >
                    {{ $icon }}
                </span>
            @endisset
            <input
                name="{{ $name }}"
                id="{{ $id }}"
                type="{{ $type }}"
                @if ($value !== null) value="{{ $value }}" @endif
                @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                @required($required)
                @if ($readonly) readonly @endif
                @autofocus($autofocus)
                placeholder=" "
                class="{{ $peerBase }} pr-4"
                {{ $attributes->except('class') }}
            />
            <label
                for="{{ $id }}"
                class="{{ $labelLeft }} pointer-events-none absolute top-1/2 z-[1] origin-[0] -translate-y-1/2 text-[15px] font-medium text-slate-500 transition-all duration-200 ease-out peer-focus:top-[0.65rem] peer-focus:-translate-y-0 peer-focus:scale-[0.72] peer-focus:text-indigo-600 dark:text-slate-400 dark:peer-focus:text-indigo-300 peer-[&:not(:placeholder-shown)]:top-[0.65rem] peer-[&:not(:placeholder-shown)]:-translate-y-0 peer-[&:not(:placeholder-shown)]:scale-[0.72]"
            >
                {{ $label }}
            </label>
        </div>
    @endif

    @if ($hint)
        <p class="px-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$messages" class="mt-1.5" />
</div>
