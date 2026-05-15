@props([
    'title' => null,
    'subtitle' => null,
    'showLogo' => true,
])

<div {{ $attributes->merge(['class' => 'relative']) }}>
    {{-- Glowing border frame --}}
    <div
        class="pointer-events-none absolute -inset-[1px] rounded-[1.35rem] bg-auth-card-border opacity-60 blur-[0.5px] animate-auth-border-glow"
        aria-hidden="true"
    ></div>

    <div class="auth-glass-card-light relative p-6 sm:p-7">
        <div
            class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-indigo-400/15 blur-3xl dark:bg-indigo-500/10"
            aria-hidden="true"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-12 -left-12 h-32 w-32 rounded-full bg-cyan-400/10 blur-3xl dark:bg-cyan-500/10"
            aria-hidden="true"
        ></div>

        <div class="relative">
            @if ($showLogo)
                <div class="mb-5 flex justify-center">
                    <a
                        href="{{ route('home') }}"
                        class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 via-violet-600 to-cyan-500 p-[1px] shadow-lg transition duration-300 hover:scale-[1.04] animate-auth-logo-pulse"
                    >
                        <span class="flex h-full w-full items-center justify-center rounded-[11px] bg-white dark:bg-slate-950">
                            <x-brand-logo class="h-7 w-7 text-indigo-600 dark:text-cyan-300" />
                        </span>
                    </a>
                </div>
            @endif

            @if ($title || $subtitle)
                <header class="mb-5 text-center">
                    @if ($title)
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-[1.35rem]">
                            {{ $title }}
                        </h2>
                    @endif
                    @if ($subtitle)
                        <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $subtitle }}
                        </p>
                    @endif
                </header>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
