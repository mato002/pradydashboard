<x-dashboard-layout :heading="__('User role assignments')" :subheading="__('Assign roles with optional scope and expiry')">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9 space-y-4">
            <form method="POST" action="{{ route('access-control.assignments.store') }}" class="rounded-2xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold">{{ __('User') }}</label>
                        <select name="user_id" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">{{ __('Role') }}</label>
                        <select name="role_id" required class="mt-1 w-full rounded-xl border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">{{ __('Scope') }}</label>
                        <select name="scope_type" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                            @foreach (['global','tenant','project','server'] as $scope)
                                <option value="{{ $scope }}">{{ ucfirst($scope) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold">{{ __('Expires at') }}</label>
                        <input type="datetime-local" name="expires_at" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    </div>
                    <div><label class="text-xs font-semibold">{{ __('Tenant ID') }}</label><input name="tenant_id" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-semibold">{{ __('Project ID') }}</label><input name="project_id" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
                    <div><label class="text-xs font-semibold">{{ __('Server ID') }}</label><input name="server_id" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
                </div>
                <button type="submit" class="mt-4 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Assign role') }}</button>
            </form>

            <div class="overflow-hidden rounded-2xl border bg-white dark:border-slate-800 dark:bg-slate-900/60">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase dark:bg-slate-800/60">
                        <tr><th class="px-4 py-3">{{ __('User') }}</th><th>{{ __('Role') }}</th><th>{{ __('Scope') }}</th><th>{{ __('Expires') }}</th><th></th></tr>
                    </thead>
                    <tbody class="divide-y dark:divide-slate-800">
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td class="px-4 py-3">{{ $assignment->user?->name }}</td>
                                <td class="px-4 py-3">{{ $assignment->role?->name }}</td>
                                <td class="px-4 py-3">{{ $assignment->scopeLabel() }}</td>
                                <td class="px-4 py-3">{{ $assignment->expires_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($assignment->status->value === 'active')
                                        <form method="POST" action="{{ route('access-control.assignments.revoke', $assignment) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-rose-600 text-xs font-semibold">{{ __('Revoke') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3">{{ $assignments->links() }}</div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
