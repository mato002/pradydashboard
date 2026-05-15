<x-dashboard-layout :heading="$role['name']" :subheading="$role['description']">
    <x-admin.form-shell
        :title="$role['name']"
        :subtitle="$role['description']"
        :badge="__('IAM role')"
        :back-href="route('users-roles.index')"
        :back-label="__('Back to IAM center')"
    >
        <x-slot name="actions">
            <a href="{{ route('users-roles.roles.edit', $slug) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                {{ __('Edit') }}
            </a>
        </x-slot>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-admin.form-section :title="__('Permissions preview')" :description="__('Sample grants for this role level.')">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="text-left text-slate-500">
                                    <th class="pb-2 pr-4">{{ __('Module') }}</th>
                                    @foreach (array_slice($permissions['actions'], 0, 5) as $action)
                                        <th class="pb-2 px-1 text-center">{{ $action }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach (array_slice($permissions['matrix'], 0, 6) as $row)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-200">{{ $row['module'] }}</td>
                                        @foreach (array_slice($permissions['actions'], 0, 5) as $action)
                                            <td class="py-2 text-center">
                                                @if ($row['grants'][$action] ?? false)
                                                    <span class="text-emerald-600">✓</span>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.form-section>
            </div>

            <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Role metadata') }}</h3>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Slug') }}</dt><dd class="font-mono text-xs">{{ $slug }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Level') }}</dt><dd class="font-medium">L{{ $role['level'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Users') }}</dt><dd class="font-medium">{{ $role['users'] }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Permissions') }}</dt><dd class="font-medium">{{ $role['permissions'] }}</dd></div>
                    @if ($role['inherits'])
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Inherits') }}</dt><dd class="font-medium">{{ $role['inherits'] }}</dd></div>
                    @endif
                </div>
            </dl>
        </div>
    </x-admin.form-shell>
</x-dashboard-layout>
