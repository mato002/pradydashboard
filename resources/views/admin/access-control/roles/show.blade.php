<x-dashboard-layout :heading="$role->name" :subheading="__('Role details & effective permissions')">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9 space-y-4">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('access-control.roles.edit', $role) }}" class="rounded-xl border px-3 py-2 text-xs font-semibold">{{ __('Edit') }}</a>
                <a href="{{ route('access-control.roles.permissions.edit', $role) }}" class="rounded-xl border px-3 py-2 text-xs font-semibold">{{ __('Permissions') }}</a>
                <a href="{{ route('access-control.roles.inheritance.edit', $role) }}" class="rounded-xl border px-3 py-2 text-xs font-semibold">{{ __('Inheritance') }}</a>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold">{{ __('Direct permissions') }}</h3>
                    <ul class="mt-2 space-y-1 font-mono text-xs">
                        @forelse ($directPermissions as $p)
                            <li>{{ $p->code }}</li>
                        @empty
                            <li class="text-slate-500">{{ __('None') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl border bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold">{{ __('Inherited permissions') }}</h3>
                    <ul class="mt-2 space-y-1 font-mono text-xs">
                        @forelse ($inheritedPermissions as $p)
                            <li>{{ $p->code }}</li>
                        @empty
                            <li class="text-slate-500">{{ __('None') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="rounded-2xl border bg-white p-5 dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold">{{ __('Assigned users') }}</h3>
                <table class="mt-3 min-w-full text-sm">
                    <thead><tr><th class="text-left py-2">{{ __('User') }}</th><th>{{ __('Scope') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr class="border-t dark:border-slate-800">
                                <td class="py-2">{{ $assignment->user?->name }}</td>
                                <td>{{ $assignment->scopeLabel() }}</td>
                                <td>{{ $assignment->status->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-layout>
