@php
    $isLogin = request()->routeIs('login');
@endphp

<header class="auth-nav-glass-light relative z-50 shrink-0">
    <div class="mx-auto flex h-14 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3 transition-opacity hover:opacity-90">
            <span
                class="relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-indigo-500 via-violet-600 to-cyan-500 p-[1px] shadow-lg shadow-indigo-500/30"
            >
                <span class="flex h-full w-full items-center justify-center rounded-[11px] bg-[#020617] dark:bg-[#020617]">
                    <x-brand-logo class="h-5 w-5 text-cyan-300" />
                </span>
            </span>
            <span class="hidden min-w-0 sm:block">
                <span class="block truncate text-sm font-bold tracking-tight text-slate-900 dark:text-white">
                    PradytecAI
                </span>
                <span class="block truncate text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                    {{ __('Operations Cloud') }}
                </span>
            </span>
        </a>

        <nav class="hidden items-center gap-0.5 md:flex">
            @foreach ([
                ['label' => __('Home'), 'href' => route('home')],
                ['label' => __('Documentation'), 'href' => route('home') . '#features'],
                ['label' => __('Status'), 'href' => route('home') . '#features'],
                ['label' => __('Contact'), 'href' => route('home') . '#contact'],
            ] as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-slate-900/[0.04] hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/[0.06] dark:hover:text-white"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200/80 bg-white/60 text-slate-600 transition hover:border-indigo-300/60 hover:bg-indigo-50/80 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:border-cyan-500/30 dark:hover:bg-cyan-500/10 dark:hover:text-cyan-200"
                @click="cycleTheme()"
                :title="theme === 'light' ? '{{ __('Light mode') }}' : theme === 'dark' ? '{{ __('Dark mode') }}' : '{{ __('System theme') }}'"
            >
                <svg x-show="theme === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
                <svg x-show="theme === 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                <svg x-show="theme === 'system'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
            </button>

            @if ($isLogin)
                <span
                    class="hidden rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-3 py-2 text-[13px] font-semibold text-indigo-600 dark:border-cyan-500/25 dark:bg-cyan-500/10 dark:text-cyan-200 sm:inline-flex"
                >
                    {{ __('Sign In') }}
                </span>
            @elseif (Route::has('login'))
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 px-3.5 py-2 text-[13px] font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:brightness-110"
                >
                    {{ __('Sign In') }}
                </a>
            @endif
        </div>
    </div>
</header>
