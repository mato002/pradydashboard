<x-guest-layout>
    <x-auth.auth-card
        :title="__('Verify your email')"
        :subtitle="__('We sent a verification link to your inbox. Confirm your address to activate your workspace.')"
    >
        @if (session('status') == 'verification-link-sent')
            <div
                class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200"
            >
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form
                method="POST"
                action="{{ route('verification.send') }}"
                class="w-full sm:w-auto"
                x-data="{ submitting: false }"
                @submit="submitting = true"
            >
                @csrf
                <x-auth.auth-button type="submit" :loading-text="__('Sending…')">
                    {{ __('Resend Verification Email') }}
                </x-auth.auth-button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="sm:pl-2">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded-xl border border-slate-200/90 bg-white px-4 py-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-white/10 dark:bg-slate-950/40 dark:text-slate-200 dark:hover:bg-slate-800/60 sm:w-auto"
                >
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-auth.auth-card>
</x-guest-layout>
