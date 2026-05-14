<x-dashboard-layout :heading="__('Servers')">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Infrastructure') }}</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('VPS & cPanel fleet') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('VPS and cPanel nodes PradytecAI operates.') }}</p>
        </div>
        <a href="{{ route('servers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Add server') }}</a>
    </div>

    <x-ui.table-panel :title="__('All servers')" :action-href="route('servers.create')" :action-label="__('New')">
        <table class="prady-table">
            <thead>
                <tr>
                    <th>{{ __('Server') }}</th>
                    <th>{{ __('IP') }}</th>
                    <th class="text-right">{{ __('Projects') }}</th>
                    <th class="text-right">{{ __('Tenants') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-right">{{ __('Monthly cost') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach ($servers as $server)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('servers.show', $server) }}" class="text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">{{ $server->name }}</a>
                            @if ($server->provider)
                                <div class="text-xs font-normal text-slate-500">{{ $server->provider }}</div>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $server->ip_address ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ $server->projects_count }}</td>
                        <td class="text-right tabular-nums">{{ $server->tenants_count }}</td>
                        <td>
                            <x-ui.status-badge :variant="$server->status === 'online' ? 'success' : ($server->status === 'offline' ? 'danger' : 'neutral')">{{ ucfirst($server->status) }}</x-ui.status-badge>
                        </td>
                        <td class="text-right tabular-nums font-medium">{{ $server->currency }} {{ number_format((float) $server->monthly_cost, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-slot name="footer">
            {{ $servers->links() }}
        </x-slot>
    </x-ui.table-panel>
</x-dashboard-layout>
