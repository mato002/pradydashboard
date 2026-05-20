<x-dashboard-layout :heading="__('Role inheritance')" :subheading="$role->name">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9">
            <form method="POST" action="{{ route('access-control.roles.inheritance.update', $role) }}" class="rounded-2xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                @method('PUT')
                <p class="mb-4 text-sm text-slate-600">{{ __('Select parent roles. This role inherits their permissions.') }}</p>
                <div class="space-y-2">
                    @foreach ($roles as $parent)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="parent_role_ids[]" value="{{ $parent->id }}" @checked(in_array($parent->id, $parentIds))>
                            {{ $parent->name }} <span class="font-mono text-xs text-slate-500">({{ $parent->code }})</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="mt-4 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save inheritance') }}</button>
            </form>
        </div>
    </div>
</x-dashboard-layout>
