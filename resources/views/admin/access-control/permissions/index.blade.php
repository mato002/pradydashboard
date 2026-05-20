<x-dashboard-layout :heading="__('Permissions registry')" :subheading="__('Discovered system permissions')">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9 space-y-4">
            @foreach ($permissions as $group => $items)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                    <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ $group }}</h3>
                    <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($items as $permission)
                            <li class="py-3">
                                <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $permission->code }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $permission->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</x-dashboard-layout>
