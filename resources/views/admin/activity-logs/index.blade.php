@php
    $queryExceptPage = request()->except('page');
@endphp

<x-dashboard-layout :heading="__('Activity Log')" :subheading="__('System-wide audit trail of operational changes')">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('State-changing actions only — who did what, when, and on which record.') }}</p>
        <a href="{{ route('activity-logs.export', $queryExceptPage) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
            {{ __('Export CSV') }}
        </a>
    </div>

    <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-6 rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('Keyword') }}</label>
                <input name="q" value="{{ $filters['q'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="{{ __('Search description…') }}" />
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
                <label class="text-xs font-medium text-slate-500">{{ __('Actor') }}</label>
                <input name="actor" value="{{ $filters['actor'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
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
                <label class="text-xs font-medium text-slate-500">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Filter') }}</button>
            <a href="{{ route('activity-logs.index') }}" class="rounded-lg border px-4 py-2 text-xs font-semibold">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/60">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase text-slate-500 dark:bg-slate-950">
                <tr>
                    <th class="px-4 py-2">{{ __('When') }}</th>
                    <th class="px-4 py-2">{{ __('Actor') }}</th>
                    <th class="px-4 py-2">{{ __('Category') }}</th>
                    <th class="px-4 py-2">{{ __('Action') }}</th>
                    <th class="px-4 py-2">{{ __('Description') }}</th>
                    <th class="px-4 py-2">{{ __('Context') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-2 text-xs text-slate-500">{{ $log->created_at?->format('M j, Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $log->actorDisplayName() }}</td>
                        <td class="px-4 py-2 capitalize">{{ $log->categoryLabel() }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-2">{{ $log->description }}</td>
                        <td class="px-4 py-2 text-xs text-slate-500">
                            @if ($log->tenant)
                                <a href="{{ route('tenants.show', $log->tenant_id) }}" class="text-indigo-600 hover:underline">{{ $log->tenant->company_name }}</a>
                            @endif
                            @if ($log->project)
                                · <a href="{{ route('hosted-projects.show', $log->project_id) }}" class="text-indigo-600 hover:underline">{{ $log->project->name }}</a>
                            @endif
                            @if ($log->server)
                                · <a href="{{ route('servers.show', $log->server_id) }}" class="text-indigo-600 hover:underline">{{ $log->server->name }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">{{ __('No activity matches your filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($logs->hasPages())
            <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
