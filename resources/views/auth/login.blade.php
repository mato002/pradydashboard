<x-guest-layout>
    <x-auth.auth-card
        :title="__('Welcome back')"
        :subtitle="__('Sign in to access your infrastructure control center')"
    >
        <x-auth-session-status
            class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('login') }}"
            class="space-y-5"
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

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex cursor-pointer select-none items-center gap-2.5">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded-md border-slate-300 bg-white text-indigo-600 shadow-sm transition hover:border-indigo-300 focus:ring-4 focus:ring-indigo-500/25 dark:border-white/15 dark:bg-slate-950 dark:text-indigo-400 dark:focus:ring-indigo-400/30"
                    >
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        class="text-sm font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                        href="{{ route('password.request') }}"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="pt-1">
                <x-auth.auth-button :loading-text="__('Signing in…')">
                    {{ __('Sign In') }}
                </x-auth.auth-button>
            </div>

            @if (Route::has('register'))
                <p class="pt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                    {{ __('New to the platform?') }}
                    <a
                        class="font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                        href="{{ route('register') }}"
                    >
                        {{ __('Create an account') }}
                    </a>
                </p>
            @endif
        </form>
    </x-auth.auth-card>
</x-guest-layout>
