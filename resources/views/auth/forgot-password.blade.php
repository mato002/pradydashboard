<x-guest-layout>
    <x-auth.auth-card
        :title="__('Forgot password')"
        :subtitle="__('Enter your email and we will send a secure link to reset your password.')"
    >
        <x-auth-session-status
            class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('password.email') }}"
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
                :hint="__('The address associated with your workspace')"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <a
                    class="text-sm font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    href="{{ route('login') }}"
                >
                    {{ __('Back to sign in') }}
                </a>
            </div>

            <x-auth.auth-button :loading-text="__('Sending link…')">
                {{ __('Email Password Reset Link') }}
            </x-auth.auth-button>
        </form>
    </x-auth.auth-card>
</x-guest-layout>
