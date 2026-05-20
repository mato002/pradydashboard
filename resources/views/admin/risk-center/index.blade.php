@php
    $queryExceptPage = request()->except('page');
@endphp

<x-dashboard-layout :heading="__('Risk Center')" :subheading="__('Operational risks detected from live data')">
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Open risks') }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ $counts['total'] }}</p>
        </div>
        <div class="rounded-xl border border-rose-200/80 bg-rose-50/50 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <p class="text-xs font-semibold uppercase tracking-widest text-rose-600">{{ __('Critical') }}</p>
            <p class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ $counts['critical'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200/80 bg-amber-50/50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-700">{{ __('Warning') }}</p>
            <p class="mt-1 text-2xl font-semibold text-amber-800 dark:text-amber-200">{{ $counts['warning'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Acknowledged') }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-600 dark:text-slate-300">{{ $counts['acknowledged'] }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('risk-center.index') }}" class="mb-6 rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Keyword') }}</label>
                <input name="q" value="{{ $filters['q'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Category') }}</label>
                <select name="category" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Severity') }}</label>
                <select name="severity" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['critical', 'warning', 'info'] as $sev)
                        <option value="{{ $sev }}" @selected($filters['severity'] === $sev)>{{ ucfirst($sev) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Acknowledged') }}</label>
                <select name="acknowledged" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="no" @selected($filters['acknowledged'] === 'no')>{{ __('Hide acknowledged') }}</option>
                    <option value="yes" @selected($filters['acknowledged'] === 'yes')>{{ __('Acknowledged only') }}</option>
                    <option value="" @selected($filters['acknowledged'] === '')>{{ __('Show all') }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Tenant') }}</label>
                <select name="tenant_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($tenants as $id => $name)
                        <option value="{{ $id }}" @selected((string) $filters['tenant_id'] === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Project') }}</label>
                <select name="project_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($projects as $id => $name)
                        <option value="{{ $id }}" @selected((string) $filters['project_id'] === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Server') }}</label>
                <select name="server_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($servers as $id => $name)
                        <option value="{{ $id }}" @selected((string) $filters['server_id'] === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Assigned staff') }}</label>
                <select name="staff_profile_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($staff as $id => $name)
                        <option value="{{ $id }}" @selected((string) $filters['staff_profile_id'] === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Due from') }}</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Due to') }}</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Filter') }}</button>
            <a href="{{ route('risk-center.index') }}" class="rounded-lg border px-4 py-2 text-xs font-semibold">{{ __('Reset') }}</a>
        </div>
    </form>

    @forelse ($grouped as $category => $categoryRisks)
        <section class="mb-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-widest text-slate-500">{{ $categories[$category] ?? $category }}</h2>
            <x-admin.risk-cards :risks="$categoryRisks" :title="null" />
        </section>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('No operational risks match your filters.') }}</p>
    @endforelse
</x-dashboard-layout>
