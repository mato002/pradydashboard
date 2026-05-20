<x-dashboard-layout :heading="__('Servers')">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Infrastructure') }}</p>
            <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('VPS & cPanel fleet') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">{{ __('Observe VPS and cPanel nodes — reachability, SSL, and optional WHM metrics.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="post" action="{{ route('servers.sync-fleet') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-800 transition hover:bg-cyan-500/15 dark:text-cyan-200">
                    {{ __('Sync fleet') }}
                </button>
            </form>
            <a href="{{ route('servers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">{{ __('Add server') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($allManualTelemetry)
        <div class="mb-6 flex gap-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3.5 text-sm text-amber-950 dark:border-amber-500/25 dark:bg-amber-500/10 dark:text-amber-100" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <p>{{ __('All servers are currently in Manual monitoring mode. Automatic sync will skip them. Set a server to Basic or WHM telemetry to enable live checks.') }}</p>
        </div>
    @endif

    <x-ui.table-panel :title="__('All servers')" :action-href="route('servers.create')" :action-label="__('New')">
        <table class="prady-table">
            <thead>
                <tr>
                    <th>{{ __('Server') }}</th>
                    <th>{{ __('IP / hostname') }}</th>
                    <th class="text-right">{{ __('Projects') }}</th>
                    <th class="text-right">{{ __('Tenants') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Telemetry') }}</th>
                    <th class="text-right">{{ __('Monthly cost') }}</th>
                    <th>{{ __('Renewal') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @forelse ($servers as $server)
                    @php
                        $statusVariant = match ($server->status) {
                            'online' => 'success',
                            'warning' => 'warning',
                            'offline' => 'danger',
                            default => 'neutral',
                        };
                        $renewalVariant = match ($server->renewalRisk()) {
                            'overdue', 'soon' => 'warning',
                            default => 'neutral',
                        };
                        $deleteConfirm = __('Delete :name from the fleet?', ['name' => $server->name]);
                        if ($server->projects_count > 0 || $server->tenants_count > 0) {
                            $deleteConfirm .= ' '.__(
                                'Linked records will be unassigned (:projects project(s), :tenants tenant(s)).',
                                ['projects' => $server->projects_count, 'tenants' => $server->tenants_count]
                            );
                        }
                    @endphp
                    <tr class="group transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="font-semibold text-slate-900 dark:text-white">
                            <a href="{{ route('servers.show', $server) }}" class="text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400">{{ $server->name }}</a>
                            @if ($server->provider)
                                <div class="text-xs font-normal text-slate-500">{{ $server->provider }}</div>
                            @endif
                            @if ($server->open_provider_notices_count > 0)
                                <div class="text-[10px] text-amber-600 dark:text-amber-400">{{ __(':count open notices', ['count' => $server->open_provider_notices_count]) }}</div>
                            @endif
                        </td>
                        <td class="font-mono text-xs text-slate-500 dark:text-slate-400">
                            {{ $server->ip_address ?? '—' }}
                            @if ($server->hostname())
                                <div class="text-[10px]">{{ $server->hostname() }}</div>
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $server->projects_count }}</td>
                        <td class="text-right tabular-nums">{{ $server->tenants_count }}</td>
                        <td>
                            <x-ui.status-badge :variant="$statusVariant">{{ ucfirst($server->status) }}</x-ui.status-badge>
                        </td>
                        <td class="text-xs text-slate-600 dark:text-slate-300">
                            <span class="font-medium">{{ $server->telemetryModeLabel() }}</span>
                            @if ($server->telemetry_mode === 'manual')
                                <span class="mt-0.5 block text-[10px] font-medium text-amber-700 dark:text-amber-300">{{ __('Manual - skipped by sync') }}</span>
                            @endif
                        </td>
                        <td class="text-right tabular-nums font-medium">{{ $server->currency }} {{ number_format((float) $server->monthly_cost, 0) }}</td>
                        <td>
                            @if ($server->renewal_expires_at)
                                <span class="text-xs tabular-nums">{{ $server->renewal_expires_at->format('M j, Y') }}</span>
                                <x-ui.status-badge :variant="$renewalVariant" class="mt-0.5">{{ ucfirst($server->renewalRisk()) }}</x-ui.status-badge>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex justify-end gap-1 opacity-80 group-hover:opacity-100">
                                <a href="{{ route('servers.show', $server) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400" title="{{ __('View') }}">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </a>
                                <a href="{{ route('servers.edit', $server) }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" title="{{ __('Edit') }}">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                                </a>
                                <form method="post" action="{{ route('servers.destroy', $server) }}" class="inline" onsubmit="return confirm(@json($deleteConfirm));">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="rounded-lg p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400" title="{{ __('Delete') }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-sm text-slate-500">{{ __('No servers registered yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <x-slot name="footer">
            {{ $servers->links() }}
        </x-slot>
    </x-ui.table-panel>
</x-dashboard-layout>
