<x-dashboard-layout :heading="__('Roles')" :subheading="__('Dynamic internal roles')">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('access-control.roles.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Create role') }}</a>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Code') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Assignments') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($roles as $role)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $role->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $role->code }}</td>
                                <td class="px-4 py-3">{{ $role->status->value }}</td>
                                <td class="px-4 py-3">{{ $role->assignments_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('access-control.roles.show', $role) }}" class="text-indigo-600 hover:underline">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $roles->links() }}</div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
