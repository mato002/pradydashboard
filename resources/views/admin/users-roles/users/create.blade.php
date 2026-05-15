<x-dashboard-layout :heading="__('Add user')" :subheading="__('Provision staff access to the platform')">
    <x-admin.form-shell
        :title="__('Add user')"
        :subtitle="__('Create credentials and assign IAM role for dashboard access.')"
        :badge="__('IAM')"
        :back-href="route('users-roles.index')"
        :back-label="__('Back to IAM center')"
    >
        <form method="post" action="{{ route('users-roles.users.store') }}" class="max-w-4xl space-y-5">
            @csrf
            <x-admin.form-section :title="__('Account')" :description="__('Identity, department, and access level.')">
                @include('admin.users-roles.users._form', [
                    'user' => $user,
                    'profile' => ['department' => '', 'roles' => [], 'status' => 'invited'],
                    'roles' => $roles,
                    'departments' => $departments,
                ])
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Provision user') }}
                </button>
                <a href="{{ route('users-roles.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
