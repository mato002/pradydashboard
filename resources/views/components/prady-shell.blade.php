@props([
    'heading' => null,
    'subheading' => null,
    'headerSlot' => null,
    'documentTitle' => null,
])

@php
    $docTitle = $documentTitle
        ?? ($heading ? $heading.' — '.config('app.name', 'Prady Dashboard') : config('app.name', 'Prady Dashboard'));
@endphp

<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $docTitle }}</title>
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
    <body class="h-full font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div x-data="pradyShell()" class="relative min-h-full">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"
                x-cloak
                @click="sidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex min-h-screen flex-col border-r border-sidebar-border bg-sidebar text-slate-300 shadow-2xl transition-all duration-300 ease-out lg:z-30"
                :class="[
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
                    'w-64',
                ]"
            >
                <div class="flex h-[4.25rem] shrink-0 items-center gap-3 border-b border-sidebar-border px-4">
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold tracking-tight text-white shadow-lg shadow-indigo-500/30">P</span>
                        <div class="min-w-0 flex-1 overflow-hidden transition-opacity" :class="sidebarCollapsed ? 'lg:opacity-0 lg:pointer-events-none' : ''">
                            <p class="truncate text-sm font-semibold tracking-tight text-white">Prady Dashboard</p>
                            <p class="truncate text-[11px] text-slate-500">{{ __('Cloud operations') }}</p>
                        </div>
                    </a>
                </div>

                <div class="prady-scrollbar flex-1 overflow-y-auto py-3">
                    @include('admin.partials.sidebar-nav')
                </div>

                <div class="shrink-0 border-t border-sidebar-border p-3">
                    <a
                        href="https://laravel.com/docs"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white"
                        :class="sidebarCollapsed ? 'lg:justify-center' : ''"
                    >
                        <svg class="h-5 w-5 shrink-0 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Documentation') }}</span>
                    </a>
                    <button
                        type="button"
                        class="mt-2 hidden w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 lg:flex"
                        @click="sidebarCollapsed = ! sidebarCollapsed"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition>{{ __('Collapse') }}</span>
                    </button>
                </div>
            </aside>

            <div class="min-h-screen pl-0 transition-[padding] duration-300 ease-out" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/75">
                    <div class="flex h-[4.25rem] items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-white p-2 text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 lg:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click="sidebarOpen = ! sidebarOpen"
                        >
                            <span class="sr-only">{{ __('Toggle sidebar') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5h16.5" />
                            </svg>
                        </button>

                        <div class="hidden min-w-0 flex-1 flex-col md:flex">
                            @if (isset($headerSlot) && trim((string) $headerSlot) !== '')
                                <div class="min-w-0 text-slate-900 dark:text-white [&_h1]:truncate [&_h1]:text-lg [&_h1]:font-semibold [&_h1]:tracking-tight [&_h2]:truncate [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:tracking-tight">
                                    {!! $headerSlot !!}
                                </div>
                            @elseif ($heading)
                                <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $heading }}</h1>
                            @endif
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                @auth
                                    {{ $subheading ?? __('Welcome back') }}, {{ Auth::user()->name }}
                                @else
                                    {{ $subheading ?? config('app.name', 'Prady Dashboard') }}
                                @endauth
                            </p>
                        </div>

                        <div class="ml-auto flex items-center gap-1 sm:gap-2">
                            <div class="relative hidden sm:block" x-data="{ open: false }">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="open = !open"
                                >
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" /></svg>
                                    <span>{{ __('Last 30 days') }}</span>
                                </button>
                                <div
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    x-cloak
                                    class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200/80 bg-white py-1 text-sm shadow-card dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Last 7 days') }}</button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Last 30 days') }}</button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Quarter to date') }}</button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="hidden rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 sm:inline-flex dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                title="{{ __('Search') }}"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" /></svg>
                            </button>

                            <button
                                type="button"
                                class="relative rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                title="{{ __('Notifications') }}"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.082A2.25 2.25 0 0021.75 14v-4.5a6 6 0 00-12 0v4.5a2.25 2.25 0 002.438 2.082M9 17.25h6" /></svg>
                                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-950"></span>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                @click="cycleTheme()"
                                :title="theme === 'light' ? '{{ __('Switch to dark') }}' : (theme === 'dark' ? '{{ __('Match system') }}' : '{{ __('Switch to light') }}')"
                            >
                                <svg x-show="theme !== 'dark'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg x-show="theme === 'dark'" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                            </button>

                            @auth
                                <x-dropdown align="right" width="48" content-classes="py-1.5 bg-white dark:bg-slate-900 rounded-xl shadow-card ring-1 ring-slate-200/80 dark:ring-slate-700">
                                    <x-slot name="trigger">
                                        <button type="button" class="flex items-center gap-2 rounded-2xl border border-slate-200/80 bg-white py-1.5 pl-1.5 pr-2 text-left shadow-sm transition hover:border-slate-300 hover:shadow-card dark:border-slate-700 dark:bg-slate-900">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                                                {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 2)) }}
                                            </span>
                                            <span class="hidden min-w-0 sm:block">
                                                <span class="block max-w-[9rem] truncate text-xs font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ __('Super Admin') }}</span>
                                            </span>
                                            <svg class="hidden h-4 w-4 shrink-0 text-slate-400 sm:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            @endauth
                        </div>
                    </div>
                </header>

                <div class="relative bg-mesh-light dark:bg-mesh-dark">
                    <div class="pointer-events-none absolute inset-0 bg-slate-100/90 dark:bg-slate-950/80"></div>
                    <main class="relative mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                        @if (session('status'))
                            <div class="mb-5 rounded-2xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </main>

                    <footer class="relative border-t border-slate-200/60 bg-white/60 px-4 py-4 text-xs text-slate-500 backdrop-blur dark:border-slate-800/60 dark:bg-slate-950/40 dark:text-slate-400 sm:px-6 lg:px-8">
                        <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-2">
                            <span>© {{ now()->year }} PradytecAI. {{ __('All rights reserved.') }}</span>
                            <span class="tabular-nums text-slate-400">{{ __('Version') }} {{ config('app.version', '1.0.0') }}</span>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>
