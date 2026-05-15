<x-dashboard-layout :heading="__('Edit tenant')" :subheading="__('Update tenant configuration and lifecycle')">
    <x-admin.form-shell
        :title="$tenant->company_name"
        :subtitle="__('Modify subscription, hosting, contacts, and operational settings.')"
        :badge="__('Tenant management')"
        :back-href="route('tenants.show', $tenant).(request()->filled('return_tab') ? '?tab='.urlencode((string) request('return_tab')) : '')"
        :back-label="__('Back to command center')"
    >
        <form method="post" action="{{ route('tenants.update', $tenant) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('put')
            @if (request()->filled('return_tab'))
                <input type="hidden" name="return_tab" value="{{ request('return_tab') }}" />
            @endif

            @include('admin.tenants._form', [
                'tenant' => $tenant,
                'projects' => $projects,
                'servers' => $servers,
                'plans' => $plans ?? collect(),
                'section' => 'all',
            ])

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('tenants.show', $tenant).(request()->filled('return_tab') ? '?tab='.urlencode((string) request('return_tab')) : '') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
