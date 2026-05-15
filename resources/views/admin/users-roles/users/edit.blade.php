<x-dashboard-layout :heading="__('Edit user')" :subheading="$profile['name']">
    <x-admin.form-shell
        :title="__('Edit user')"
        :subtitle="$profile['email']"
        :badge="__('IAM')"
        :back-href="route('users-roles.users.show', $userRef)"
        :back-label="__('Back to profile')"
    >
        @if ($isDemo)
            <div class="mb-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('Demo users cannot be saved. Create a real account to persist IAM settings.') }}
            </div>
        @endif
        <form method="post" action="{{ route('users-roles.users.update', $userRef) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            <x-admin.form-section :title="__('Account')">
                @include('admin.users-roles.users._form', compact('user', 'profile', 'roles', 'departments'))
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('users-roles.users.show', $userRef) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
        @unless ($isDemo)
            <form method="post" action="{{ route('users-roles.users.destroy', $userRef) }}" class="mt-3" onsubmit="return confirm('{{ __('Remove this user?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400">{{ __('Delete user') }}</button>
            </form>
        @endunless
    </x-admin.form-shell>
</x-dashboard-layout>
