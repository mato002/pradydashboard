@props([
    'showMarketing' => true,
])

<div
    x-data="authShell()"
    class="relative flex min-h-[100dvh] min-h-screen flex-col overflow-x-hidden bg-[#f8fafc] text-slate-900 dark:bg-[#020617] dark:text-slate-100"
>
    @include('components.auth.partials.auth-topnav')

    <div class="flex min-h-0 flex-1 flex-col lg:flex-row">
        @if ($showMarketing)
            {{-- Mobile hero strip --}}
            <div class="relative overflow-hidden bg-enterprise-mesh px-5 py-5 lg:hidden">
                <div class="pointer-events-none absolute inset-0 bg-auth-glow opacity-60" aria-hidden="true"></div>
                <div class="relative flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                            <x-brand-logo class="h-6 w-6 text-cyan-300" />
                        </span>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-200/90">PradytecAI</p>
                            <p class="text-sm font-bold text-white">{{ __('Operations Cloud') }}</p>
                        </div>
                    </div>
                    <span class="rounded-full border border-emerald-400/30 bg-emerald-500/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-300">
                        {{ __('Secure') }}
                    </span>
                </div>
                <p class="relative mt-3 text-sm font-semibold leading-snug text-white/95">
                    {{ __('Enterprise infrastructure control plane') }}
                </p>
            </div>

            @include('components.auth.partials.enterprise-panel')
        @endif

        {{-- Auth panel --}}
        <main class="relative flex min-h-0 flex-1 flex-col lg:w-[48%] lg:shrink-0 xl:w-[50%]">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-600/15"></div>
                <div class="absolute -bottom-24 left-1/4 h-72 w-72 rounded-full bg-violet-400/15 blur-3xl dark:bg-violet-600/10"></div>
                <div
                    class="absolute inset-0 bg-mesh-light opacity-80 dark:bg-mesh-dark dark:opacity-100"
                ></div>
            </div>

            <div class="relative z-[1] flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
                <div class="w-full max-w-[400px] animate-auth-fade-up">
                    {{ $slot }}
                </div>
            </div>

            <footer class="relative z-[1] shrink-0 border-t border-slate-200/70 px-4 py-3 text-center text-[11px] text-slate-500 dark:border-white/[0.06] dark:text-slate-500 sm:px-8">
                <span>© {{ now()->year }} PradytecAI · {{ __('All rights reserved.') }}</span>
                <span class="mx-2 opacity-40">·</span>
                <span class="tabular-nums">{{ __('Version') }} {{ config('app.version', '1.0.0') }}</span>
            </footer>
        </main>
    </div>
</div>
