<x-dashboard-layout :heading="__('Edit role')" :subheading="$role->name">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9">
            <form method="POST" action="{{ route('access-control.roles.update', $role) }}" class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                @csrf
                @method('PUT')
                @include('admin.access-control.roles._form', ['role' => $role])
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save changes') }}</button>
            </form>
        </div>
    </div>
</x-dashboard-layout>
