<x-dashboard-layout :heading="$title">
    <div class="overflow-hidden rounded-2xl border border-dashed border-indigo-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-violet-50/50 p-8 shadow-card dark:border-indigo-900/40 dark:from-slate-900 dark:via-indigo-950/30 dark:to-slate-900">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ $phase }}</p>
        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $blurb }}</p>
        <p class="mt-6 max-w-xl text-xs text-slate-500 dark:text-slate-400">{{ __('This screen is wired in the roadmap. Phase 1 covers servers, hosted projects, tenants, and the license endpoint.') }}</p>
        <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Back to overview') }}</a>
    </div>
</x-dashboard-layout>
