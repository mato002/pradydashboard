<x-dashboard-layout :heading="__('Edit hosted project')" :subheading="$hostedProject->domain">
    <x-admin.form-shell
        :title="$hostedProject->domain"
        :subtitle="__('Update hosted instance details.')"
        :badge="__('Hosted projects')"
        :back-href="route('hosted-projects.show', $hostedProject)"
        :back-label="__('Back to instance')"
    >
        <form method="post" action="{{ route('hosted-projects.update', $hostedProject) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            @include('admin.hosted-projects._form', ['hostedProject' => $hostedProject, 'products' => $products, 'servers' => $servers])
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('hosted-projects.show', $hostedProject) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
