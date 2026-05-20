<x-guest-layout>
    <x-auth.auth-card
        :title="__('Password expired')"
        :subtitle="__('Your password is older than :days days. Enter your current password and choose a new one to continue.', ['days' => $expiryDays])"
    >
        <form
            method="POST"
            action="{{ route('password.expired.update') }}"
            class="space-y-5"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @method('PUT')

            <x-auth.auth-input
                :label="__('Current password')"
                name="current_password"
                type="password"
                revealable
                required
                autocomplete="current-password"
            />

            <x-auth.auth-input
                :label="__('New password')"
                name="password"
                type="password"
                revealable
                required
                autocomplete="new-password"
            />

            <x-auth.auth-input
                :label="__('Confirm new password')"
                name="password_confirmation"
                type="password"
                revealable
                required
                autocomplete="new-password"
            />

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pt-1">
                <x-auth.auth-button :loading-text="__('Updating…')">
                    {{ __('Update password') }}
                </x-auth.auth-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button
                type="submit"
                class="text-sm font-medium text-gray-600 underline-offset-2 hover:underline dark:text-gray-400"
            >
                {{ __('Sign out') }}
            </button>
        </form>
    </x-auth.auth-card>
</x-guest-layout>
