<x-dashboard-layout :heading="__('New hosted project')" :subheading="__('Register a SaaS product on the platform')">
    <x-admin.form-shell
        :title="__('New hosted project')"
        :subtitle="__('Define product domain, license API slug, and hosting assignment.')"
        :badge="__('Infrastructure')"
        :back-href="route('projects.index')"
        :back-label="__('Back to projects')"
    >
        <form method="post" action="{{ route('projects.store') }}" class="max-w-4xl space-y-5">
            @csrf
            @include('admin.projects._form', ['project' => $project, 'servers' => $servers])
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save project') }}
                </button>
                <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
