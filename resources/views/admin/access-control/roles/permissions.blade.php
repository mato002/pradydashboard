<x-dashboard-layout :heading="__('Role permissions')" :subheading="$role->name">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9">
            <form method="POST" action="{{ route('access-control.roles.permissions.update', $role) }}" class="rounded-2xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                @method('PUT')
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $assignedIds))>
                            <span class="font-mono text-xs">{{ $permission->code }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4">
                    <label class="text-xs font-semibold">{{ __('Wildcard codes (e.g. tenants.*)') }}</label>
                    <input name="wildcard_codes" class="mt-1 w-full rounded-xl border px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="tenants.*, invoices.*">
                </div>
                <button type="submit" class="mt-4 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save permissions') }}</button>
            </form>
        </div>
    </div>
</x-dashboard-layout>
