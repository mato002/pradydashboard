<x-guest-layout>
    <x-auth.auth-card
        :title="__('Welcome back')"
        :subtitle="__('Sign in to your infrastructure control center')"
    >
        {{-- Secure login badge --}}
        <div class="mb-5 flex flex-wrap items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                {{ __('Secure login') }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                {{ __('MFA ready') }}
            </span>
        </div>

        <x-auth-session-status
            class="mb-4 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-2.5 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('login') }}"
            class="space-y-4"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

            <x-auth.auth-input
                :label="__('Email')"
                name="email"
                type="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                :hint="__('Use your work or organization email')"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <x-auth.auth-input
                :label="__('Password')"
                name="password"
                type="password"
                revealable
                required
                autocomplete="current-password"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                <label for="remember_me" class="inline-flex cursor-pointer select-none items-center gap-2">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 bg-white text-indigo-600 shadow-sm transition focus:ring-4 focus:ring-indigo-500/25 dark:border-white/15 dark:bg-slate-950 dark:text-indigo-400"
                    >
                    <span class="text-[13px] font-medium text-slate-600 dark:text-slate-300">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        class="text-[13px] font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-cyan-400 dark:hover:text-cyan-300"
                        href="{{ route('password.request') }}"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-auth.auth-button :loading-text="__('Signing in…')">
                {{ __('Sign In') }}
            </x-auth.auth-button>

            @if (Route::has('register'))
                <p class="text-center text-[13px] text-slate-500 dark:text-slate-400">
                    {{ __('New to the platform?') }}
                    <a
                        class="font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-cyan-400"
                        href="{{ route('register') }}"
                    >
                        {{ __('Create an account') }}
                    </a>
                </p>
            @endif
        </form>

        {{-- Enterprise security & trust --}}
        <div class="mt-6 space-y-4 border-t border-slate-200/80 pt-5 dark:border-white/[0.08]">
            <div class="flex items-start gap-2.5 rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2.5 dark:border-white/[0.08] dark:bg-white/[0.03]">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                        {{ __('Encrypted authentication') }}
                    </p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ __('Protected by enterprise-grade security. Sessions are encrypted and monitored for anomalous access.') }}
                    </p>
                </div>
            </div>

            <p class="text-center text-[10px] font-medium uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                {{ __('Protected by enterprise-grade security') }}
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                @foreach ([__('SOC 2 aligned'), __('TLS 1.3'), __('RBAC'), __('Audit logs')] as $badge)
                    <span class="rounded-md border border-slate-200/80 bg-white px-2 py-1 text-[10px] font-semibold text-slate-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                        {{ $badge }}
                    </span>
                @endforeach
            </div>

            <p class="text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                <svg class="mr-1 inline h-3.5 w-3.5 -mt-px text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ __('Active session protection enabled on this device') }}
            </p>
        </div>
    </x-auth.auth-card>
</x-guest-layout>
