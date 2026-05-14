<x-guest-layout>
    <x-auth.auth-card
        :title="__('Create your workspace')"
        :subtitle="__('Provision your organization and start orchestrating infrastructure in minutes.')"
    >
        <form
            method="POST"
            action="{{ route('register') }}"
            class="space-y-5"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

            <x-auth.auth-input
                :label="__('Name')"
                name="name"
                type="text"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                :hint="__('As it should appear in the console')"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <x-auth.auth-input
                :label="__('Email')"
                name="email"
                type="email"
                :value="old('email')"
                required
                autocomplete="username"
                :hint="__('We will send verification to this inbox')"
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
                autocomplete="new-password"
                :hint="__('Minimum 8 characters; mix letters and numbers')"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <x-auth.auth-input
                :label="__('Confirm Password')"
                name="password_confirmation"
                type="password"
                revealable
                required
                autocomplete="new-password"
            >
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </x-slot>
            </x-auth.auth-input>

            <x-auth.auth-button :loading-text="__('Creating account…')">
                {{ __('Register') }}
            </x-auth.auth-button>

            <p class="pt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('Already registered?') }}
                <a
                    class="font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    href="{{ route('login') }}"
                >
                    {{ __('Sign in') }}
                </a>
            </p>
        </form>
    </x-auth.auth-card>
</x-guest-layout>
