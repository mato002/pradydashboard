<x-dashboard-layout :heading="__('Edit hosted project')" :subheading="__('Update product configuration')">
    <x-admin.form-shell
        :title="$project->name"
        :subtitle="__('Modify domain, stack, server, and revenue settings.')"
        :badge="__('Infrastructure')"
        :back-href="route('projects.show', $project)"
        :back-label="__('Back to project')"
    >
        <form method="post" action="{{ route('projects.update', $project) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('put')
            @include('admin.projects._form', ['project' => $project, 'servers' => $servers])
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('projects.show', $project) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
