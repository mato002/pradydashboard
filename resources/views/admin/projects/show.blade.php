<x-dashboard-layout :heading="$project->name">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $project->domain }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Edit') }}</a>
            <form method="post" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('{{ __('Delete this project? Tenants will be removed.') }}');">
                @csrf
                @method('delete')
                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <dl class="space-y-3 rounded-lg border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Product slug') }}</dt><dd class="font-mono text-xs font-medium">{{ $project->product_slug ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Server') }}</dt><dd class="font-medium">{{ $project->server?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Status') }}</dt><dd class="font-medium capitalize">{{ $project->status }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Version') }}</dt><dd class="font-medium">{{ $project->version ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Database') }}</dt><dd class="font-medium">{{ $project->database_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Git') }}</dt><dd class="max-w-xs truncate font-medium text-indigo-600 dark:text-indigo-400">@if ($project->git_repository)<a href="{{ $project->git_repository }}" class="hover:underline" target="_blank" rel="noopener">{{ $project->git_repository }}</a>@else — @endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Monthly revenue / cost') }}</dt><dd class="text-right font-medium">{{ $project->monthly_revenue ?? '—' }} / {{ $project->monthly_cost ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Est. profit') }}</dt><dd class="font-medium text-green-700 dark:text-green-400">{{ $project->monthlyProfit() }}</dd></div>
        </dl>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('License API token') }}</h3>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Send as Authorization: Bearer … or X-Project-Token header.') }}</p>
            <pre class="mt-3 overflow-x-auto rounded-md bg-gray-950 p-3 text-xs text-gray-100">{{ $project->api_token }}</pre>
            <form method="post" action="{{ route('projects.regenerate-token', $project) }}" class="mt-4" onsubmit="return confirm('{{ __('Regenerate token? Old integrations will fail until updated.') }}');">
                @csrf
                <x-secondary-button type="submit">{{ __('Regenerate token') }}</x-secondary-button>
            </form>
        </div>
    </div>

    @if ($project->technology_stack)
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Technology stack') }}</h3>
            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">{{ $project->technology_stack }}</p>
        </div>
    @endif

    @if ($project->notes)
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Notes') }}</h3>
            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">{{ $project->notes }}</p>
        </div>
    @endif

    <div class="mt-8">
        <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Tenants on this product') }}</h3>
        <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($project->tenants as $tenant)
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <a href="{{ route('tenants.show', $tenant) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ $tenant->company_name }}</a>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $tenant->status }}</span>
                </li>
            @empty
                <li class="px-4 py-4 text-sm text-gray-500">{{ __('No tenants linked yet.') }}</li>
            @endforelse
        </ul>
    </div>
</x-dashboard-layout>
