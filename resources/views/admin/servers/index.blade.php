<x-dashboard-layout :heading="__('Servers')">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Infrastructure') }}</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('VPS & cPanel fleet') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('VPS and cPanel nodes PradytecAI operates.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="post" action="{{ route('servers.sync-fleet') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-800 transition hover:bg-cyan-500/15 dark:text-cyan-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    {{ __('Sync fleet') }}
                </button>
            </form>
            <a href="{{ route('servers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Add server') }}</a>
        </div>
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
                    <th>{{ __('Telemetry') }}</th>
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
                            @if ($server->last_synced_at)
                                <div class="text-[10px] text-slate-400">{{ __('Synced') }} {{ $server->last_synced_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $server->ip_address ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ $server->projects_count }}</td>
                        <td class="text-right tabular-nums">{{ $server->tenants_count }}</td>
                        <td>
                            <x-ui.status-badge :variant="$server->status === 'online' ? 'success' : ($server->status === 'offline' ? 'danger' : 'neutral')">{{ ucfirst($server->status) }}</x-ui.status-badge>
                        </td>
                        <td class="text-xs text-slate-500 dark:text-slate-400">
                            @if ($server->telemetry_source)
                                <span class="font-mono text-[10px] uppercase">{{ $server->telemetry_source }}</span>
                            @else
                                <span class="text-slate-400">{{ __('Manual') }}</span>
                            @endif
                            @if ($server->sync_status)
                                <span class="mt-0.5 block capitalize text-[10px]">{{ $server->sync_status }}</span>
                            @endif
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
