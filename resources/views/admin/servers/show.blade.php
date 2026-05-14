<x-dashboard-layout :heading="$server->name">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-gray-600 dark:text-gray-300">
            {{ $server->provider ?? __('Unknown provider') }}
            @if ($server->ip_address)
                · {{ $server->ip_address }}
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('servers.edit', $server) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Edit') }}</a>
            <form method="post" action="{{ route('servers.destroy', $server) }}" onsubmit="return confirm('{{ __('Delete this server?') }}');">
                @csrf
                @method('delete')
                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950 dark:text-red-200">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <dl class="space-y-3 rounded-lg border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Status') }}</dt><dd class="font-medium capitalize">{{ $server->status }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('SSL') }}</dt><dd class="font-medium">{{ $server->ssl_status ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Backups') }}</dt><dd class="font-medium">{{ $server->backup_status ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('CPU / RAM / Disk') }}</dt><dd class="font-medium text-right">{{ $server->cpu_cores ?? '—' }} cores · {{ $server->ram_gb ?? '—' }} GB RAM · {{ $server->storage_gb ?? '—' }} GB · {{ $server->disk_usage_percent ?? '—' }}% used</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Monthly cost') }}</dt><dd class="font-medium">{{ $server->currency }} {{ number_format((float) $server->monthly_cost, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Monthly revenue') }}</dt><dd class="font-medium">{{ $server->monthly_revenue !== null ? $server->currency.' '.number_format((float) $server->monthly_revenue, 2) : '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Est. monthly profit') }}</dt><dd class="font-medium text-green-700 dark:text-green-400">{{ $server->currency }} {{ $server->monthlyProfit() }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">{{ __('Renewal') }}</dt><dd class="font-medium">{{ optional($server->renewal_expires_at)->toFormattedDateString() ?? '—' }}</dd></div>
        </dl>
        <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('WHM / cPanel') }}</h3>
            <p class="mt-2 whitespace-pre-wrap text-gray-600 dark:text-gray-300">{{ $server->whm_cpanel_reference ?: '—' }}</p>
        </div>
    </div>

    @if ($server->hosted_domains && count($server->hosted_domains))
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Hosted domains') }}</h3>
            <ul class="mt-3 list-inside list-disc text-sm text-gray-700 dark:text-gray-200">
                @foreach ($server->hosted_domains as $d)
                    <li>{{ $d }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($server->notes)
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Notes') }}</h3>
            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">{{ $server->notes }}</p>
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div>
            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Projects on this server') }}</h3>
            <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @forelse ($server->projects as $project)
                    <li class="px-4 py-3 text-sm">
                        <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ $project->name }}</a>
                        <span class="text-gray-500">· {{ $project->domain }}</span>
                    </li>
                @empty
                    <li class="px-4 py-4 text-sm text-gray-500">{{ __('No projects linked yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div>
            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Tenants pinned to this server') }}</h3>
            <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @forelse ($server->tenants as $tenant)
                    <li class="px-4 py-3 text-sm">
                        <a href="{{ route('tenants.show', $tenant) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ $tenant->company_name }}</a>
                    </li>
                @empty
                    <li class="px-4 py-4 text-sm text-gray-500">{{ __('No tenants assigned to this server.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-dashboard-layout>
