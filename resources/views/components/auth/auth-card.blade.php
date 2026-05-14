@props([
    'title' => null,
    'subtitle' => null,
    'showLogo' => true,
])

<div
    {{ $attributes->merge([
        'class' =>
            'relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 p-8 shadow-[0_1px_0_rgba(255,255,255,0.9)_inset,0_32px_64px_-24px_rgba(15,23,42,0.2),0_0_0_1px_rgba(99,102,241,0.04)] ring-1 ring-slate-900/[0.04] backdrop-blur-xl transition duration-300 hover:border-indigo-200/60 hover:shadow-[0_1px_0_rgba(255,255,255,0.95)_inset,0_40px_80px_-28px_rgba(79,70,229,0.18)] dark:border-white/[0.08] dark:bg-slate-900/75 dark:shadow-[0_1px_0_rgba(255,255,255,0.04)_inset,0_32px_64px_-24px_rgba(0,0,0,0.65),0_0_0_1px_rgba(99,102,241,0.12)] dark:ring-white/[0.06] dark:hover:border-indigo-500/25 sm:p-10',
    ]) }}
>
    <div
        class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-gradient-to-br from-indigo-400/20 to-violet-500/10 blur-3xl dark:from-indigo-500/15 dark:to-violet-600/10"
        aria-hidden="true"
    ></div>
    <div
        class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-cyan-400/10 blur-3xl dark:bg-cyan-500/10"
        aria-hidden="true"
    ></div>

    <div class="relative">
        @if ($showLogo)
            <div class="mb-8 flex justify-center">
                <a
                    href="{{ route('home') }}"
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 shadow-lg shadow-indigo-500/35 ring-1 ring-white/25 transition duration-200 hover:scale-[1.03] hover:shadow-indigo-500/45 active:scale-[0.98]"
                >
                    <x-application-logo class="h-8 w-8 text-white" />
                </a>
            </div>
        @endif

        @if ($title || $subtitle)
            <header class="mb-8 text-center">
                @if ($title)
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $title }}
                    </h2>
                @endif
                @if ($subtitle)
                    <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $subtitle }}
                    </p>
                @endif
            </header>
        @endif

        {{ $slot }}
    </div>
</div>
