<x-dashboard-layout :heading="__('Profile')" :subheading="__('Account security & preferences')">
    <div class="mx-auto max-w-3xl space-y-6">
        @include('profile.partials.rbac-summary')

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="overflow-hidden rounded-2xl border border-rose-200/80 bg-rose-50/40 p-6 shadow-card dark:border-rose-900/40 dark:bg-rose-950/30 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dashboard-layout>
