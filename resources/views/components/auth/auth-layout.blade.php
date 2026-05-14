@props([
    'showMarketing' => true,
])

<div
    x-data="authShell()"
    class="relative flex min-h-[100dvh] min-h-screen flex-col overflow-x-hidden bg-slate-50 text-slate-900 dark:bg-[#030712] dark:text-slate-100"
>
    {{-- Theme toggle --}}
    <div
        class="fixed right-4 top-4 z-50 flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/90 p-1 shadow-lg backdrop-blur-md dark:border-white/10 dark:bg-slate-900/90"
    >
        <button
            type="button"
            class="rounded-full px-2.5 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800"
            :class="theme === 'light' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'text-slate-500 dark:text-slate-400'"
            @click="setTheme('light')"
            title="{{ __('Light') }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
        </button>
        <button
            type="button"
            class="rounded-full px-2.5 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800"
            :class="theme === 'dark' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'text-slate-500 dark:text-slate-400'"
            @click="setTheme('dark')"
            title="{{ __('Dark') }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
        </button>
        <button
            type="button"
            class="rounded-full px-2.5 py-1.5 text-xs font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-800"
            :class="theme === 'system' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'text-slate-500 dark:text-slate-400'"
            @click="setTheme('system')"
            title="{{ __('System') }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
            </svg>
        </button>
    </div>

    <div class="flex flex-1 flex-col">
        <div class="flex flex-1 flex-col lg:flex-row lg:items-stretch">
            @if ($showMarketing)
                {{-- Mobile brand strip --}}
                <div
                    class="relative z-[1] flex shrink-0 items-center justify-between gap-3 border-b border-white/10 bg-login-gradient px-5 py-4 lg:hidden"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20 backdrop-blur-sm"
                        >
                            <x-application-logo class="h-6 w-6 text-cyan-300" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200/90">PradytecAI</p>
                            <p class="text-sm font-semibold text-white">{{ __('Control plane') }}</p>
                        </div>
                    </div>
                    <span
                        class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-300 ring-1 ring-emerald-400/30"
                    >
                        {{ __('Secure') }}
                    </span>
                </div>

                {{-- Left: marketing / intelligence --}}
                <aside
                    class="relative hidden w-full overflow-hidden bg-login-gradient lg:flex lg:w-[44%] lg:shrink-0 lg:flex-col xl:w-[42%]"
                >
                    <div class="pointer-events-none absolute inset-0 bg-auth-glow opacity-90"></div>
                    <div
                        class="pointer-events-none absolute inset-0 opacity-[0.4]"
                        style="background-image: linear-gradient(to right, rgba(148, 163, 184, 0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(148, 163, 184, 0.06) 1px, transparent 1px); background-size: 48px 48px"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="pointer-events-none absolute inset-0 opacity-30"
                        style="background-image: radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.06) 0, transparent 2px), radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.04) 0, transparent 2px); background-size: 120px 120px"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="pointer-events-none absolute -left-24 top-1/4 h-80 w-80 rounded-full bg-indigo-500/30 blur-3xl animate-auth-blob"
                    ></div>
                    <div
                        class="pointer-events-none absolute bottom-0 right-0 h-[22rem] w-[22rem] translate-x-1/4 translate-y-1/4 rounded-full bg-cyan-500/20 blur-3xl animate-auth-blob-slow"
                    ></div>
                    <div
                        class="pointer-events-none absolute right-1/4 top-10 h-48 w-48 rounded-full bg-purple-500/25 blur-2xl animate-auth-glow-pulse"
                    ></div>
                    <div
                        class="pointer-events-none absolute left-1/3 top-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-indigo-600/10 blur-3xl animate-auth-drift"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="relative z-[1] flex w-full flex-1 flex-col justify-between px-8 py-10 sm:px-10 sm:py-12 xl:px-14 xl:py-14"
                    >
                        <div class="animate-auth-fade-up">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 transition-opacity hover:opacity-90">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-lg ring-1 ring-white/15 backdrop-blur-md"
                                >
                                    <x-application-logo class="h-7 w-7 text-cyan-300" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-indigo-200/90">
                                        PradytecAI
                                    </p>
                                    <p class="text-lg font-bold tracking-tight text-white">{{ __('Operations cloud') }}</p>
                                </div>
                            </a>

                            <h1 class="mt-6 max-w-lg text-2xl font-bold leading-[1.15] tracking-tight text-white sm:mt-8 sm:text-3xl xl:text-[2rem]">
                                {{ __('Infrastructure-grade SaaS for tenants, billing, and deployments') }}
                            </h1>
                            <p class="mt-4 max-w-lg text-sm leading-relaxed text-slate-300/95">
                                {{ __('Monitor hosts, ship releases, enforce licensing, and orchestrate customer workloads from one control plane—built like a product you would trust with revenue.') }}
                            </p>

                            <ul class="mt-8 max-w-md space-y-3 text-sm text-slate-200/90">
                                @foreach (
                                    [
                                        __('Live health & capacity signals'),
                                        __('Tenant isolation & policy controls'),
                                        __('Automated backups & SSL lifecycle'),
                                        __('API keys, webhooks, and audit trails'),
                                        __('Blue/green-friendly deployment hooks'),
                                    ] as $item
                                )
                                    <li class="flex items-start gap-3">
                                        <span
                                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-400/25"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        </span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Floating KPI layer --}}
                        <div class="relative z-[2] mt-8 hidden lg:block xl:mt-10">
                            <div class="flex flex-wrap gap-3 xl:gap-4">
                                <div
                                    class="animate-auth-float w-[calc(50%-0.375rem)] rounded-2xl border border-white/10 bg-slate-950/45 p-3.5 shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset,0_20px_40px_-12px_rgba(0,0,0,0.5)] ring-1 ring-cyan-400/10 backdrop-blur-xl sm:w-[11.25rem]"
                                    style="animation-delay: 0ms"
                                >
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Active tenants') }}</p>
                                    <p class="mt-1 font-mono text-xl font-bold tabular-nums text-white" x-data="countUp(58)" x-text="display">0</p>
                                </div>
                                <div
                                    class="animate-auth-float-slow w-[calc(50%-0.375rem)] rounded-2xl border border-white/10 bg-slate-950/45 p-3.5 shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset,0_20px_40px_-12px_rgba(0,0,0,0.5)] ring-1 ring-emerald-400/15 backdrop-blur-xl sm:w-[11.25rem]"
                                    style="animation-delay: 120ms"
                                >
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Uptime') }}</p>
                                    <p class="mt-1 font-mono text-xl font-bold tabular-nums text-emerald-300" x-data="countUpDecimal(99.98)" x-text="display + '%'">0</p>
                                </div>
                                <div
                                    class="animate-auth-float w-[calc(50%-0.375rem)] rounded-2xl border border-white/10 bg-slate-950/45 p-3.5 shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset,0_20px_40px_-12px_rgba(0,0,0,0.5)] ring-1 ring-indigo-400/15 backdrop-blur-xl sm:w-[11.25rem]"
                                    style="animation-delay: 240ms"
                                >
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Servers live') }}</p>
                                    <p class="mt-1 font-mono text-xl font-bold tabular-nums text-white" x-data="countUp(12)" x-text="display">0</p>
                                </div>
                                <div
                                    class="animate-auth-float-slow w-[calc(50%-0.375rem)] rounded-2xl border border-white/10 bg-slate-950/45 p-3.5 shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset,0_20px_40px_-12px_rgba(0,0,0,0.5)] ring-1 ring-violet-400/15 backdrop-blur-xl sm:w-[11.25rem]"
                                    style="animation-delay: 360ms"
                                >
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Deployments') }}</p>
                                    <p class="mt-1 font-mono text-xl font-bold tabular-nums text-white" x-data="countUp(128)" x-text="display">0</p>
                                </div>
                            </div>
                            <div
                                class="animate-auth-float relative mt-4 max-w-md rounded-2xl border border-white/15 bg-gradient-to-r from-indigo-500/15 via-slate-950/50 to-cyan-500/10 p-4 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl"
                                style="animation-delay: 480ms"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Monthly revenue') }}</p>
                                        <p class="mt-1 font-mono text-lg font-bold tabular-nums text-white">KES 1.2M</p>
                                    </div>
                                    <span
                                        class="rounded-full bg-cyan-400/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-cyan-200 ring-1 ring-cyan-400/25"
                                    >
                                        {{ __('MRR') }}
                                    </span>
                                </div>
                                <p class="mt-2 text-[11px] text-slate-400">{{ __('Illustrative telemetry for demo environments') }}</p>
                            </div>
                        </div>

                        {{-- Analytics deck --}}
                        <div
                            class="mt-8 max-w-md rounded-2xl border border-white/10 bg-slate-950/50 p-4 shadow-[0_0_0_1px_rgba(255,255,255,0.05)_inset,0_28px_56px_-16px_rgba(0,0,0,0.55)] ring-1 ring-white/10 backdrop-blur-xl animate-auth-fade-up-delay sm:mt-10 sm:p-5"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                        {{ __('Live fabric') }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-white">{{ __('Global edge · API plane') }}</p>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 ring-1 ring-emerald-400/25"
                                >
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"
                                        ></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                    </span>
                                    {{ __('Operational') }}
                                </span>
                            </div>

                            <div class="mt-5 flex items-end gap-1.5" aria-hidden="true">
                                @foreach ([40, 65, 48, 78, 55, 88, 62, 92, 70, 98, 84, 76] as $h)
                                    <div
                                        class="flex-1 rounded-sm bg-gradient-to-t from-indigo-600/50 to-cyan-400/85 transition-all duration-500 hover:from-indigo-500/70 hover:to-cyan-300"
                                        style="height: {{ $h }}px"
                                    ></div>
                                @endforeach
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 ring-1 ring-white/5">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('API / min') }}
                                    </p>
                                    <p class="mt-1 font-mono text-sm font-bold tabular-nums text-white sm:text-base" x-data="countUp(18420)" x-text="display.toLocaleString()">0</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 ring-1 ring-white/5">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('Jobs queued') }}
                                    </p>
                                    <p class="mt-1 font-mono text-sm font-bold tabular-nums text-white sm:text-base" x-data="countUp(14)" x-text="display">0</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 ring-1 ring-white/5">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('Regions') }}
                                    </p>
                                    <p class="mt-1 font-mono text-sm font-bold tabular-nums text-white sm:text-base">6</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3 ring-1 ring-white/5">
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('Incidents') }}
                                    </p>
                                    <p class="mt-1 font-mono text-sm font-bold tabular-nums text-emerald-300 sm:text-base">0</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="pointer-events-none absolute bottom-6 right-4 hidden opacity-[0.4] xl:right-8 xl:block animate-auth-fade-up-delay-2"
                            aria-hidden="true"
                        >
                            <svg width="200" height="160" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="24" y="20" width="152" height="112" rx="12" stroke="url(#authg1)" stroke-width="1.2" />
                                <rect x="40" y="36" width="48" height="28" rx="6" fill="url(#authg2)" opacity="0.5" />
                                <rect x="96" y="36" width="64" height="12" rx="4" fill="url(#authg2)" opacity="0.35" />
                                <rect x="96" y="54" width="48" height="10" rx="4" fill="url(#authg2)" opacity="0.25" />
                                <rect x="40" y="76" width="120" height="40" rx="8" stroke="url(#authg1)" stroke-width="1" opacity="0.6" />
                                <path d="M56 96h24M56 104h40" stroke="url(#authg1)" stroke-width="2" stroke-linecap="round" opacity="0.5" />
                                <defs>
                                    <linearGradient id="authg1" x1="24" y1="20" x2="176" y2="140" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#67E8F9" stop-opacity="0.9" />
                                        <stop offset="1" stop-color="#818CF8" stop-opacity="0.4" />
                                    </linearGradient>
                                    <linearGradient id="authg2" x1="40" y1="36" x2="160" y2="100" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#A5B4FC" />
                                        <stop offset="1" stop-color="#22D3EE" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </aside>
            @endif

            {{-- Right: auth --}}
            <main class="relative flex flex-1 flex-col bg-mesh-light dark:bg-mesh-dark">
                <div
                    class="pointer-events-none absolute inset-0 overflow-hidden"
                    aria-hidden="true"
                >
                    <div class="absolute -right-24 -top-32 h-[28rem] w-[28rem] rounded-full bg-indigo-400/25 blur-3xl dark:bg-indigo-600/20"></div>
                    <div class="absolute -bottom-32 left-1/4 h-80 w-80 rounded-full bg-violet-400/20 blur-3xl dark:bg-violet-600/15"></div>
                    <div
                        class="absolute inset-0 opacity-[0.35] dark:opacity-[0.2]"
                        style="background-image: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.12), transparent)"
                    ></div>
                </div>

                <header
                    class="relative z-[2] flex w-full shrink-0 items-center justify-between gap-3 border-b border-slate-200/60 bg-white/70 px-4 py-3 backdrop-blur-xl dark:border-white/[0.06] dark:bg-slate-950/50 sm:px-8 lg:px-10"
                >
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 text-slate-900 transition hover:opacity-90 dark:text-white">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md shadow-indigo-500/25"
                        >
                            P
                        </span>
                        <span class="truncate text-sm font-bold tracking-tight">{{ config('app.name', 'Prady Dashboard') }}</span>
                    </a>
                    <nav class="flex shrink-0 items-center gap-1 text-xs font-semibold sm:gap-2 sm:text-sm">
                        <a
                            href="{{ route('home') }}"
                            class="rounded-lg px-2.5 py-2 text-slate-600 transition hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white"
                        >
                            {{ __('Home') }}
                        </a>
                        @if (Route::has('login') && ! request()->routeIs('login'))
                            <a
                                href="{{ route('login') }}"
                                class="rounded-lg px-2.5 py-2 text-slate-600 transition hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white"
                            >
                                {{ __('Log in') }}
                            </a>
                        @endif
                        @if (Route::has('register') && ! request()->routeIs('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-2 text-white shadow-md shadow-indigo-500/20 transition hover:brightness-110"
                            >
                                {{ __('Register') }}
                            </a>
                        @endif
                    </nav>
                </header>

                <div
                    class="relative z-[1] flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-8 sm:py-12 lg:px-12 lg:py-14"
                >
                    <div class="relative w-full max-w-md animate-auth-fade-up">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>

        <footer
            class="shrink-0 border-t border-slate-200/80 bg-white/80 px-4 py-3 text-xs text-slate-500 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:text-slate-400 sm:px-6"
        >
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 sm:flex-row">
                <span>© {{ now()->year }} PradytecAI · {{ __('All rights reserved.') }}</span>
                <span class="tabular-nums">{{ __('Version') }} {{ config('app.version', '1.0.0') }}</span>
            </div>
        </footer>
    </div>
</div>
