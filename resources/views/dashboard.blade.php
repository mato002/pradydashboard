<x-dashboard-layout :heading="__('Dashboard')" :subheading="__('Quick access')">
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 sm:p-8">
        <p class="text-sm text-slate-600 dark:text-slate-300">{{ __("You're logged in!") }}</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Open operations overview') }}</a>
    </div>
</x-dashboard-layout>
