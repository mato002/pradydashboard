<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta
            name="description"
            content="PradytecAI — African enterprise SaaS, cloud infrastructure, CRM, microfinance, property & ISP platforms. API-first, multi-tenant, operations-grade systems."
        >
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>PradytecAI — {{ __('Enterprise SaaS & cloud infrastructure for Africa') }}</title>

        <script>
            (function () {
                try {
                    var t = localStorage.getItem('prady-theme') || 'light';
                    var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        x-data="authShell()"
        class="min-h-[100dvh] min-h-screen bg-[#f8fafc] font-sans text-slate-900 antialiased dark:bg-[#030712] dark:text-slate-100"
    >
        {{-- Theme --}}
        <div
            class="fixed right-4 top-4 z-[60] flex items-center gap-1 rounded-full border border-slate-200/90 bg-white/95 p-1 shadow-lg backdrop-blur-md dark:border-white/10 dark:bg-slate-950/90"
            aria-label="{{ __('Theme') }}"
        >
            <button
                type="button"
                class="rounded-full px-2.5 py-1.5 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                :class="theme === 'light' ? 'bg-indigo-50 text-indigo-700 dark:bg-white/15 dark:text-white' : ''"
                @click="setTheme('light')"
                title="{{ __('Light') }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </button>
            <button
                type="button"
                class="rounded-full px-2.5 py-1.5 text-xs font-medium text-slate-400 transition-colors hover:bg-white/10 hover:text-white"
                :class="theme === 'dark' ? 'bg-indigo-50 text-indigo-700 dark:bg-white/15 dark:text-white' : ''"
                @click="setTheme('dark')"
                title="{{ __('Dark') }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </button>
            <button
                type="button"
                class="rounded-full px-2.5 py-1.5 text-xs font-medium text-slate-400 transition-colors hover:bg-white/10 hover:text-white"
                :class="theme === 'system' ? 'bg-indigo-50 text-indigo-700 dark:bg-white/15 dark:text-white' : ''"
                @click="setTheme('system')"
                title="{{ __('System') }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
            </button>
        </div>

        {{-- Header --}}
        <header
            class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/[0.06] dark:bg-[#030712]/85"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-700 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 ring-1 ring-white/20"
                    >
                        P
                    </span>
                    <span class="flex flex-col leading-tight">
                        <span class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">PradytecAI</span>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            {{ __('Infrastructure') }}
                        </span>
                    </span>
                </a>
                <nav class="hidden items-center gap-1 text-sm font-semibold text-slate-600 md:flex dark:text-slate-300">
                    <a href="#products" class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-white/5 dark:hover:text-white">{{ __('Products') }}</a>
                    <a href="#platform" class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-white/5 dark:hover:text-white">{{ __('Platform') }}</a>
                    <a href="#infrastructure" class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-white/5 dark:hover:text-white">{{ __('Infrastructure') }}</a>
                    <a href="#apis" class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-white/5 dark:hover:text-white">{{ __('APIs') }}</a>
                    <a href="#contact" class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-white/5 dark:hover:text-white">{{ __('Contact') }}</a>
                </nav>
                <div class="flex items-center gap-2">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110"
                        >
                            {{ __('Console') }}
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 sm:inline dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white"
                            >
                                {{ __('Sign in') }}
                            </a>
                        @endif
                        <a
                            href="mailto:sales@pradytecai.com?subject={{ rawurlencode(__('PradytecAI — demo or architecture discussion')) }}"
                            class="rounded-xl border border-slate-200/90 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
                        >
                            {{ __('Book demo') }}
                        </a>
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110"
                            >
                                {{ __('Start trial') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{-- 1. Hero --}}
            <section
                class="relative overflow-hidden border-b border-white/5 bg-[#030712] text-white"
                style="background-image: radial-gradient(ellipse 120% 80% at 50% -40%, rgba(99, 102, 241, 0.35), transparent), radial-gradient(ellipse 80% 50% at 100% 0%, rgba(34, 211, 238, 0.12), transparent)"
            >
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.35] dark:opacity-[0.45]"
                    style="background-image: linear-gradient(to right, rgba(148, 163, 184, 0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(148, 163, 184, 0.08) 1px, transparent 1px); background-size: 56px 56px"
                    aria-hidden="true"
                ></div>
                <div
                    class="pointer-events-none absolute -left-32 top-1/4 h-96 w-96 rounded-full bg-indigo-600/25 blur-3xl animate-auth-blob"
                    aria-hidden="true"
                ></div>
                <div
                    class="pointer-events-none absolute bottom-0 right-0 h-[28rem] w-[28rem] translate-x-1/3 translate-y-1/4 rounded-full bg-cyan-500/15 blur-3xl animate-auth-blob-slow"
                    aria-hidden="true"
                ></div>

                <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-8 lg:py-28">
                    <div class="animate-auth-fade-up">
                        <p class="inline-flex items-center gap-2 rounded-full border border-emerald-400/25 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-200">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            </span>
                            {{ __('Live operations · Multi-tenant cloud') }}
                        </p>
                        <h1 class="mt-6 text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-[2.75rem] xl:text-5xl">
                            {{ __('Enterprise software infrastructure for modern African businesses') }}
                        </h1>
                        <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                            {{ __('PradytecAI designs, hosts, and operates cloud-native business systems—CRM, microfinance, property, ISP, and automation—so your teams run on infrastructure-grade platforms, not one-off websites.') }}
                        </p>
                        <div class="mt-10 flex flex-wrap items-center gap-3">
                            <a
                                href="mailto:sales@pradytecai.com?subject={{ rawurlencode(__('Request a technical demo')) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-slate-900 shadow-xl shadow-black/20 transition hover:bg-slate-100"
                            >
                                {{ __('Book demo') }}
                            </a>
                            <a
                                href="#products"
                                class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"
                            >
                                {{ __('View products') }}
                            </a>
                            @guest
                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="inline-flex w-full items-center justify-center rounded-xl border border-white/10 px-6 py-3.5 text-sm font-semibold text-slate-200 transition hover:text-white sm:w-auto sm:border-0 sm:px-3"
                                    >
                                        {{ __('Start free trial') }}
                                        <span aria-hidden="true" class="ml-1.5">→</span>
                                    </a>
                                @endif
                            @endguest
                        </div>
                        <dl class="mt-12 grid grid-cols-2 gap-4 border-t border-white/10 pt-10 sm:grid-cols-3">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Stack') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-white">{{ __('API-first · Multi-tenant') }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Posture') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-white">{{ __('Monitored · Backed up · Audited') }}</dd>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Delivery') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-white">{{ __('African realities · Global reliability') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Hero visual: operational dashboard mock --}}
                    <div class="relative mx-auto w-full max-w-lg lg:mx-0 lg:max-w-none animate-auth-fade-up-delay">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-indigo-500/20 via-transparent to-cyan-400/20 blur-2xl" aria-hidden="true"></div>
                        <div
                            class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-950/80 shadow-2xl shadow-black/50 ring-1 ring-white/10 backdrop-blur-xl dark:bg-slate-950/90"
                        >
                            <div class="flex items-center justify-between border-b border-white/5 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-red-500/80"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400/80"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500/80"></span>
                                </div>
                                <span class="font-mono text-[10px] text-slate-500">control.pradytecai.com</span>
                                <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300 ring-1 ring-emerald-400/30">
                                    {{ __('All regions healthy') }}
                                </span>
                            </div>
                            <div class="flex min-h-[320px]">
                                <div class="hidden w-14 shrink-0 border-r border-white/5 bg-slate-900/50 py-3 sm:block">
                                    @foreach (['T', 'S', 'B', 'M', 'A'] as $g)
                                        <div class="mx-auto mb-2 h-8 w-8 rounded-lg bg-white/5 text-center text-[10px] font-bold leading-8 text-slate-500">{{ $g }}</div>
                                    @endforeach
                                </div>
                                <div class="flex-1 p-4 sm:p-5">
                                    <div class="flex flex-wrap items-end justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Throughput (24h)') }}</p>
                                            <p class="mt-1 font-mono text-2xl font-bold tabular-nums text-white" x-data="countUp(18400000)" x-text="display.toLocaleString() + ' +'">0</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-right">
                                            <p class="text-[10px] font-medium uppercase text-slate-500">{{ __('API p99') }}</p>
                                            <p class="font-mono text-sm font-semibold text-cyan-300">142ms</p>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex h-24 items-end gap-1" aria-hidden="true">
                                        @foreach ([32, 48, 40, 64, 52, 72, 58, 80, 68, 88, 76, 92] as $h)
                                            <div
                                                class="flex-1 rounded-sm bg-gradient-to-t from-indigo-600/50 to-cyan-400/70"
                                                style="height: {{ $h }}px"
                                            ></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3 ring-1 ring-white/5">
                                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ __('Tenants') }}</p>
                                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-white" x-data="countUp(2840)" x-text="display.toLocaleString()">0</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3 ring-1 ring-white/5">
                                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ __('Deployments') }}</p>
                                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-white" x-data="countUp(412)" x-text="display">0</p>
                                        </div>
                                        <div class="col-span-2 rounded-xl border border-white/10 bg-white/[0.04] p-3 ring-1 ring-emerald-500/20 sm:col-span-1">
                                            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ __('Uptime (rolling)') }}</p>
                                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-emerald-300" x-data="countUpDecimal(99.97)" x-text="display + '%'">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Floating metric chips --}}
                        <div
                            class="absolute -left-2 top-1/4 hidden rounded-xl border border-white/10 bg-slate-900/95 px-3 py-2 shadow-xl backdrop-blur sm:block lg:-left-8"
                        >
                            <p class="text-[10px] font-medium uppercase text-slate-500">{{ __('Messages / day') }}</p>
                            <p class="font-mono text-sm font-bold text-white" x-data="countUp(9200000)" x-text="(display / 1e6).toFixed(1) + 'M'">0</p>
                        </div>
                        <div
                            class="absolute -right-2 bottom-12 hidden rounded-xl border border-cyan-400/20 bg-cyan-950/80 px-3 py-2 shadow-xl backdrop-blur sm:block lg:-right-6"
                        >
                            <p class="text-[10px] font-medium uppercase text-cyan-200/80">{{ __('Edge nodes') }}</p>
                            <p class="font-mono text-sm font-bold text-cyan-200" x-data="countUp(24)" x-text="display">0</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. Trust & metrics --}}
            <section id="platform" class="border-b border-slate-200/80 bg-white py-16 dark:border-white/[0.06] dark:bg-[#0a0f1a]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-indigo-600 dark:text-indigo-400">{{ __('Operational proof') }}</h2>
                        <p class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ __('Systems that stay up when your business is on the line') }}
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ __('Representative platform telemetry from production-style workloads. Your numbers depend on deployment scope—we engineer for the same discipline.') }}
                        </p>
                    </div>
                    <div
                        class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6"
                    >
                        @php
                            $metrics = [
                                ['label' => __('Organizations'), 'end' => 847, 'suffix' => '+'],
                                ['label' => __('SMS / 30d'), 'end' => 120000000, 'fmt' => 'compact'],
                                ['label' => __('API req / min'), 'end' => 42000, 'suffix' => '+'],
                                ['label' => __('Hosts monitored'), 'end' => 186, 'suffix' => ''],
                                ['label' => __('Txn / day'), 'end' => 510000, 'fmt' => 'k'],
                                ['label' => __('Regions'), 'end' => 6, 'suffix' => ''],
                            ];
                        @endphp
                        @foreach ($metrics as $m)
                            <div
                                class="rounded-2xl border border-slate-200/90 bg-slate-50/80 p-4 text-center shadow-sm dark:border-white/[0.06] dark:bg-white/[0.03]"
                            >
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $m['label'] }}</p>
                                @if (($m['fmt'] ?? '') === 'compact')
                                    <p
                                        class="mt-2 font-mono text-xl font-bold tabular-nums text-slate-900 dark:text-white"
                                        x-data="countUp({{ $m['end'] }})"
                                        x-text="(display / 1e6).toFixed(0) + 'M+'"
                                    >
                                        0
                                    </p>
                                @elseif (($m['fmt'] ?? '') === 'k')
                                    <p
                                        class="mt-2 font-mono text-xl font-bold tabular-nums text-slate-900 dark:text-white"
                                        x-data="countUp({{ $m['end'] }})"
                                        x-text="(display / 1000).toFixed(0) + 'K+'"
                                    >
                                        0
                                    </p>
                                @else
                                    <p
                                        class="mt-2 font-mono text-xl font-bold tabular-nums text-slate-900 dark:text-white"
                                        x-data="countUp({{ $m['end'] }})"
                                        x-text="display.toLocaleString() + '{{ $m['suffix'] }}'"
                                    >
                                        0
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- 3. Products --}}
            <section id="products" class="scroll-mt-24 border-b border-slate-200/80 bg-slate-50 py-20 dark:border-white/[0.06] dark:bg-[#030712]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl">
                            <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-indigo-600 dark:text-indigo-400">{{ __('Product ecosystem') }}</h2>
                            <p class="mt-4 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ __('Vertical SaaS you operate—not spreadsheets you babysit') }}
                            </p>
                            <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                {{ __('Each line of business gets a dedicated surface: messaging CRM, regulated lending, property operations, ISP networks, and shared enterprise APIs—unified by tenancy, security, and observability.') }}
                            </p>
                        </div>
                        <a
                            href="#contact"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
                        >
                            {{ __('Talk to solutions engineering') }}
                        </a>
                    </div>

                    @php
                        $products = [
                            [
                                'name' => 'BulkSMS CRM',
                                'desc' => __('Multi-channel messaging, campaigns, delivery analytics, and customer memory—built for high-volume African telco realities.'),
                                'bullets' => [__('Sender IDs & compliance'), __('Delivery receipts & DR'), __('Team queues & roles')],
                                'href' => 'https://crm.pradytecai.com',
                                'external' => true,
                                'cta' => __('Open CRM'),
                            ],
                            [
                                'name' => __('Prady MFI'),
                                'desc' => __('Core banking workflows for microfinance: portfolios, collections, GL hooks, and audit trails that regulators and boards expect.'),
                                'bullets' => [__('Loan cycles & arrears'), __('KYC-ready processes'), __('Interest & fees engine')],
                                'href' => '#contact',
                                'external' => false,
                                'cta' => __('Request briefing'),
                            ],
                            [
                                'name' => __('Property OS'),
                                'desc' => __('Leases, billing, maintenance, and occupancy intelligence for portfolios that cannot afford downtime or revenue leakage.'),
                                'bullets' => [__('Metering & service charges'), __('Vendor SLAs'), __('Owner dashboards')],
                                'href' => '#contact',
                                'external' => false,
                                'cta' => __('Scope a rollout'),
                            ],
                            [
                                'name' => __('ISP Control'),
                                'desc' => __('Subscribers, radius/billing handoffs, tickets, and network-aware operations for ISPs scaling beyond spreadsheets.'),
                                'bullets' => [__('Provisioning hooks'), __('Usage & fair-use'), __('NOC-friendly views')],
                                'href' => '#contact',
                                'external' => false,
                                'cta' => __('See architecture'),
                            ],
                            [
                                'name' => __('Enterprise APIs'),
                                'desc' => __('Integration fabric for banks, ERPs, payment switches, and internal tools—versioned, authenticated, observable.'),
                                'bullets' => [__('Webhooks & HMAC'), __('Idempotent writes'), __('Per-tenant keys')],
                                'href' => '#apis',
                                'external' => false,
                                'cta' => __('View API stance'),
                            ],
                            [
                                'name' => __('SaaS infrastructure'),
                                'desc' => __('The control plane behind the products: tenants, billing, deployments, backups, SSL, monitoring—how we run your software as operations.'),
                                'bullets' => [__('Multi-tenant isolation'), __('Blue/green patterns'), __('Incident-ready runbooks')],
                                'href' => '#infrastructure',
                                'external' => false,
                                'cta' => __('How we host'),
                            ],
                        ];
                    @endphp
                    <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $p)
                            <article
                                class="group flex flex-col rounded-2xl border border-slate-200/90 bg-white p-6 shadow-card transition hover:border-indigo-300/60 hover:shadow-card-hover dark:border-white/[0.06] dark:bg-[#0c1222] dark:hover:border-indigo-500/30"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $p['name'] }}</h3>
                                    <span
                                        class="rounded-lg bg-indigo-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300"
                                    >
                                        {{ __('SaaS') }}
                                    </span>
                                </div>
                                <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $p['desc'] }}</p>
                                <ul class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm text-slate-700 dark:border-white/[0.06] dark:text-slate-300">
                                    @foreach ($p['bullets'] as $b)
                                        <li class="flex gap-2">
                                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                                            <span>{{ $b }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/[0.06]">
                                    <div class="flex h-10 flex-1 items-end gap-0.5 opacity-80" aria-hidden="true">
                                        @foreach ([40, 55, 48, 70, 52, 68] as $h)
                                            <div class="flex-1 rounded-sm bg-gradient-to-t from-indigo-500/30 to-violet-400/50" style="height: {{ $h }}%"></div>
                                        @endforeach
                                    </div>
                                    <a
                                        href="{{ $p['href'] }}"
                                        @if (! empty($p['external'])) target="_blank" rel="noopener noreferrer" @endif
                                        class="shrink-0 rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white transition group-hover:bg-indigo-600 dark:bg-white dark:text-slate-900 dark:group-hover:bg-indigo-500 dark:group-hover:text-white"
                                    >
                                        {{ $p['cta'] }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- 4. Infrastructure --}}
            <section
                id="infrastructure"
                class="scroll-mt-24 border-b border-white/5 bg-[#050a14] py-20 text-white"
                style="background-image: radial-gradient(ellipse 100% 60% at 50% 100%, rgba(79, 70, 229, 0.2), transparent)"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-cyan-300/90">{{ __('Infrastructure & reliability') }}</h2>
                        <p class="mt-4 text-3xl font-bold tracking-tight">
                            {{ __('We do not “host a website.” We run production systems.') }}
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-slate-400">
                            {{ __('Cloud-native patterns, disciplined DevOps, and security defaults you would expect from a serious SaaS operator—applied to African connectivity, compliance, and uptime realities.') }}
                        </p>
                    </div>
                    <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach (
                            [
                                ['t' => __('Resilient cloud'), 'd' => __('Multi-AZ posture, autoscaling pools, and capacity planning—not single-box VPS luck.')],
                                ['t' => __('Observability'), 'd' => __('Health checks, logs, metrics, and alerting wired for on-call response—not silent failures.')],
                                ['t' => __('Backups & DR'), 'd' => __('Encrypted backups, restore drills, and RPO/RTO conversations upfront—not after an incident.')],
                                ['t' => __('Security layers'), 'd' => __('TLS everywhere, tenant isolation, secrets hygiene, and least-privilege access for humans and machines.')],
                                ['t' => __('Deployments'), 'd' => __('Repeatable pipelines, staged releases, and rollback paths so product velocity does not trade off stability.')],
                                ['t' => __('Tenant control'), 'd' => __('Per-tenant configuration, usage, billing hooks, and kill-switches operators can trust.')],
                                ['t' => __('API operations'), 'd' => __('Rate limits, keys, audit trails, and abuse detection as first-class concerns.')],
                                ['t' => __('Enterprise support'), 'd' => __('Engineers who speak SLAs, maintenance windows, and postmortems—not ticket ping-pong.')],
                            ] as $cell
                        )
                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 ring-1 ring-white/5 transition hover:border-cyan-400/25 hover:bg-white/[0.05]"
                            >
                                <h3 class="text-sm font-bold text-white">{{ $cell['t'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $cell['d'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- 5. Dashboard preview --}}
            <section class="border-b border-slate-200/80 bg-white py-20 dark:border-white/[0.06] dark:bg-[#0a0f1a]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-indigo-600 dark:text-indigo-400">{{ __('Control plane') }}</h2>
                        <p class="mt-4 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ __('One serious console for operators and executives') }}
                        </p>
                    </div>
                    <div class="relative mt-16">
                        <div class="absolute inset-0 -z-10 blur-3xl" aria-hidden="true">
                            <div class="mx-auto h-64 max-w-3xl rounded-full bg-indigo-500/15 dark:bg-indigo-500/25"></div>
                        </div>
                        <div class="relative mx-auto max-w-5xl">
                            <div
                                class="absolute -top-6 left-4 z-10 hidden w-64 rounded-xl border border-slate-200/90 bg-white/95 p-4 shadow-2xl backdrop-blur-md dark:border-white/10 dark:bg-slate-900/95 sm:block"
                            >
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Tenants') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Provisioning queue') }}</p>
                                <div class="mt-3 space-y-2">
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-2 w-4/5 rounded-full bg-indigo-500"></div>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-2 w-3/5 rounded-full bg-violet-500"></div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="overflow-hidden rounded-2xl border border-slate-200/90 bg-slate-50 shadow-2xl dark:border-white/10 dark:bg-slate-950"
                            >
                                <div class="flex items-center gap-2 border-b border-slate-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                                    <span class="text-xs font-semibold text-slate-500">{{ __('Live') }}</span>
                                    <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">{{ __('Billing sync OK') }}</span>
                                </div>
                                <div class="grid gap-0 lg:grid-cols-[1fr_280px]">
                                    <div class="border-b border-slate-200/80 p-6 dark:border-white/10 lg:border-b-0 lg:border-r">
                                        <div class="grid gap-4 sm:grid-cols-3">
                                            @foreach (
                                                [
                                                    [__('MRR pipeline'), 'KES 4.2M', __('+12% MoM')],
                                                    [__('Incidents'), '0 P1', __('Last 90d')],
                                                    [__('Deployments'), '38', __('Automated')],
                                                ] as $kpi
                                            )
                                                <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-white/10 dark:bg-slate-900/80">
                                                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $kpi[0] }}</p>
                                                    <p class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">{{ $kpi[1] }}</p>
                                                    <p class="mt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">{{ $kpi[2] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-6 h-36 rounded-xl border border-slate-200/80 bg-gradient-to-b from-white to-slate-50 p-4 dark:border-white/10 dark:from-slate-900 dark:to-slate-950">
                                            <p class="text-[10px] font-bold uppercase text-slate-500">{{ __('Server CPU (avg)') }}</p>
                                            <div class="mt-4 flex h-20 items-end gap-1">
                                                @foreach ([35, 42, 38, 55, 48, 62, 58, 52, 48, 44, 50, 46] as $h)
                                                    <div class="flex-1 rounded-t bg-indigo-500/70 dark:bg-indigo-400/60" style="height: {{ $h }}%"></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-4 bg-white p-6 dark:bg-slate-900/50">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Activity') }}</p>
                                        @foreach (
                                            [
                                                [__('SSL renewed'), __('edge-eu-2'), '2m'],
                                                [__('Backup verified'), __('db-primary'), '18m'],
                                                [__('Webhook delivered'), __('partner-x'), '32m'],
                                            ] as $row
                                        )
                                            <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2 text-xs dark:border-white/5 dark:bg-slate-950/60">
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $row[0] }}</span>
                                                <span class="font-mono text-[10px] text-slate-500">{{ $row[1] }}</span>
                                                <span class="text-slate-400">{{ $row[2] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-500">
                                {{ __('Illustrative UI composition. Your branding, data regions, and modules ship to match contract.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 6. Why PradytecAI --}}
            <section class="border-b border-slate-200/80 bg-slate-50 py-20 dark:border-white/[0.06] dark:bg-[#030712]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-indigo-600 dark:text-indigo-400">{{ __('Why PradytecAI') }}</h2>
                            <p class="mt-4 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ __('Built for African operations. Engineered like global SaaS.') }}
                            </p>
                            <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                {{ __('We combine product discipline with infrastructure seriousness: the same rigor you would demand from a cloud vendor, applied to CRM, finance, property, and network workloads that power real economies.') }}
                            </p>
                        </div>
                        <ul class="space-y-4">
                            @foreach (
                                [
                                    __('African market fluency—pricing models, channels, connectivity, and regulatory conversations baked in.'),
                                    __('Enterprise architecture—multi-tenant boundaries, auditability, and long-term maintainability first.'),
                                    __('API-first DNA—your ERP, bank, telco, or internal stack integrates without heroic glue code.'),
                                    __('Security & scale by default—not optional add-ons sold after the fact.'),
                                    __('Local partnership posture with global engineering standards and documentation you can defend to a board.'),
                                ] as $why
                            )
                                <li class="flex gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 dark:border-white/[0.06] dark:bg-[#0c1222]">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium leading-relaxed text-slate-800 dark:text-slate-200">{{ $why }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            {{-- 7. APIs & automation --}}
            <section id="apis" class="scroll-mt-24 border-b border-white/5 bg-[#070b14] py-20 text-slate-100">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-start">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-[0.28em] text-cyan-300/90">{{ __('APIs & automation') }}</h2>
                            <p class="mt-4 text-3xl font-bold tracking-tight text-white">
                                {{ __('Your systems should orchestrate—not manually chase each other') }}
                            </p>
                            <p class="mt-4 text-sm leading-relaxed text-slate-400">
                                {{ __('Webhooks, signed callbacks, idempotent endpoints, and event contracts designed for finance-grade retries. Automation flows connect CRM, MFI cores, property ledgers, and ISP OSS without brittle screen-scraping.') }}
                            </p>
                            <ul class="mt-8 space-y-3 text-sm text-slate-300">
                                <li class="flex gap-2">
                                    <span class="text-cyan-400">▹</span>
                                    {{ __('Versioned REST + webhook signatures (HMAC) for partner trust') }}
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-cyan-400">▹</span>
                                    {{ __('Per-tenant API keys, quotas, and abuse-aware rate limits') }}
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-cyan-400">▹</span>
                                    {{ __('Playbooks for ERP, banking, M-Pesa-style rails, and internal data lakes') }}
                                </li>
                            </ul>
                        </div>
                        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl ring-1 ring-cyan-500/10">
                            <div class="flex items-center justify-between border-b border-white/5 px-4 py-2">
                                <span class="font-mono text-[11px] text-slate-500">POST /v1/events/delivery</span>
                                <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">202</span>
                            </div>
                            <pre class="overflow-x-auto p-4 text-[11px] leading-relaxed text-slate-300"><code>{
  "id": "evt_9f2a7c1e",
  "type": "message.delivered",
  "tenant": "acme-mfi",
  "occurred_at": "2026-05-14T12:01:04Z",
  "data": {
    "campaign_id": "cmp_4821",
    "recipient": "+2547***821",
    "segments": ["collections", "d30"]
  }
}</code></pre>
                            <div class="border-t border-white/5 bg-black/30 px-4 py-3 font-mono text-[10px] leading-relaxed text-slate-500">
                                <span class="block">curl -sS https://api.pradytecai.com/v1/health \</span>
                                <span class="block pl-4">-H "Authorization: Bearer <span class="text-cyan-400/90">pt_live_••••</span>"</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 8. Testimonials --}}
            <section class="border-b border-slate-200/80 bg-white py-20 dark:border-white/[0.06] dark:bg-[#0a0f1a]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.28em] text-indigo-600 dark:text-indigo-400">{{ __('Operator voices') }}</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ __('Reliability and clarity where we could not afford surprises') }}
                    </p>
                    <div class="mt-14 grid gap-6 md:grid-cols-3">
                        @foreach (
                            [
                                [
                                    'q' => __('We replaced three brittle tools with one tenant-aware stack. Finance finally trusts the numbers because engineering trusts the pipeline.'),
                                    'r' => __('Chief Operating Officer'),
                                    'c' => __('Regional MFI · East Africa'),
                                ],
                                [
                                    'q' => __('The ISP team stopped living in WhatsApp screenshots. Tickets, subscribers, and usage are in one operational surface—and it stays up.'),
                                    'r' => __('Head of Network Operations'),
                                    'c' => __('Fixed wireless provider'),
                                ],
                                [
                                    'q' => __('Their API posture is what sold us: signed webhooks, clear error contracts, and adults in the room when integrations break at 2 a.m.'),
                                    'r' => __('Director of Engineering'),
                                    'c' => __('Property & facilities group'),
                                ],
                            ] as $t
                        )
                            <blockquote
                                class="flex h-full flex-col rounded-2xl border border-slate-200/90 bg-slate-50/80 p-6 shadow-sm dark:border-white/[0.06] dark:bg-[#0c1222]"
                            >
                                <p class="flex-1 text-sm leading-relaxed text-slate-700 dark:text-slate-300">“{{ $t['q'] }}”</p>
                                <footer class="mt-6 border-t border-slate-200/80 pt-4 text-xs dark:border-white/[0.06]">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $t['r'] }}</p>
                                    <p class="mt-1 text-slate-500">{{ $t['c'] }}</p>
                                </footer>
                            </blockquote>
                        @endforeach
                    </div>
                    <p class="mx-auto mt-10 max-w-2xl text-center text-[11px] leading-relaxed text-slate-500">
                        {{ __('Quotes reflect composite operator feedback from engagements; names withheld for confidentiality. We will introduce referenceable customers where contracts allow.') }}
                    </p>
                </div>
            </section>

            {{-- 9. Final CTA --}}
            <section id="contact" class="scroll-mt-24 bg-gradient-to-br from-indigo-950 via-slate-900 to-[#020617] py-24 text-white">
                <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        {{ __('Build your business on reliable SaaS infrastructure') }}
                    </h2>
                    <p class="mx-auto mt-5 max-w-2xl text-sm leading-relaxed text-slate-300">
                        {{ __('Bring your technical lead—we will walk architecture, security, SLAs, and rollout. No generic slide decks; concrete control-plane design.') }}
                    </p>
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                        <a
                            href="mailto:sales@pradytecai.com?subject={{ rawurlencode(__('Schedule a demo — PradytecAI')) }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 text-sm font-bold text-slate-900 shadow-xl transition hover:bg-slate-100"
                        >
                            {{ __('Schedule demo') }}
                        </a>
                        <a
                            href="mailto:sales@pradytecai.com?subject={{ rawurlencode(__('Talk to PradytecAI solutions team')) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 px-8 py-4 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"
                        >
                            {{ __('Talk to our team') }}
                        </a>
                    </div>
                </div>
            </section>
        </main>

        {{-- 10. Footer --}}
        <footer class="border-t border-slate-200/80 bg-slate-50 dark:border-white/[0.06] dark:bg-[#030712]">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white">P</span>
                            <span class="text-lg font-bold text-slate-900 dark:text-white">PradytecAI</span>
                        </div>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ __('African enterprise SaaS and cloud infrastructure—CRM, MFI, property, ISP, APIs, and the control plane that runs them.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Products') }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="https://crm.pradytecai.com" target="_blank" rel="noopener noreferrer">BulkSMS CRM</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#contact">{{ __('Prady MFI') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#contact">{{ __('Property OS') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#contact">{{ __('ISP Control') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Infrastructure') }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#infrastructure">{{ __('Cloud & HA') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#infrastructure">{{ __('Monitoring') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#infrastructure">{{ __('Backups & DR') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#infrastructure">{{ __('Security') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('APIs') }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#apis">{{ __('Webhooks') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#apis">{{ __('Integration guides') }}</a></li>
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="#apis">{{ __('Tenant keys') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Company') }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                            @auth
                                <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('dashboard') }}">{{ __('Console') }}</a></li>
                            @else
                                @if (Route::has('login'))
                                    <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('login') }}">{{ __('Sign in') }}</a></li>
                                @endif
                            @endauth
                            <li><a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="mailto:sales@pradytecai.com">{{ __('Contact') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div
                    class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-slate-200/80 pt-8 text-xs text-slate-500 dark:border-white/[0.06] dark:text-slate-500 sm:flex-row"
                >
                    <p>© {{ now()->year }} PradytecAI · {{ __('All rights reserved.') }}</p>
                    <p class="tabular-nums">{{ __('Version') }} {{ config('app.version', '1.0.0') }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
