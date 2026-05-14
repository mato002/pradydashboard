<x-guest-layout>
    <x-auth.auth-card
        :title="__('Confirm access')"
        :subtitle="__('This is a protected area. Re-enter your password to continue.')"
    >
        <form
            method="POST"
            action="{{ route('password.confirm') }}"
            class="space-y-5"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf

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

            <div class="pt-1">
                <x-auth.auth-button :loading-text="__('Confirming…')">
                    {{ __('Confirm') }}
                </x-auth.auth-button>
            </div>
        </form>
    </x-auth.auth-card>
</x-guest-layout>
