<x-dashboard-layout :heading="__('Hosted projects')">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Products') }}</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Hosted systems') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('Systems and products running under PradytecAI.') }}</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Add project') }}</a>
    </div>

    <x-ui.table-panel :title="__('Projects')" :action-href="route('projects.create')" :action-label="__('New')">
        <table class="prady-table">
            <thead>
                <tr>
                    <th>{{ __('Project') }}</th>
                    <th>{{ __('Domain') }}</th>
                    <th>{{ __('Server') }}</th>
                    <th class="text-right">{{ __('Tenants') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach ($projects as $project)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">{{ $project->name }}</a>
                        </td>
                        <td class="text-slate-600 dark:text-slate-300">{{ $project->domain }}</td>
                        <td class="text-slate-600 dark:text-slate-300">{{ $project->server?->name ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ $project->tenants_count }}</td>
                        <td>
                            <x-ui.status-badge :variant="$project->status === 'active' ? 'success' : ($project->status === 'suspended' ? 'danger' : 'warning')">{{ $project->status }}</x-ui.status-badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-slot name="footer">
            {{ $projects->links() }}
        </x-slot>
    </x-ui.table-panel>
</x-dashboard-layout>
