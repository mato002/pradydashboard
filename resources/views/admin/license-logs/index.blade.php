<x-dashboard-layout :heading="__('License API logs')" :subheading="__('Audit trail for product system license checks')">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Control plane') }}</p>
        <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">{{ __('License check requests') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Server-to-server calls from property, mfi, crm, and other hosted products.') }}</p>
    </div>

    <x-ui.table-panel :title="__('Recent checks')">
        <table class="prady-table">
            <thead>
                <tr>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Tenant') }}</th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Domain') }}</th>
                    <th>{{ __('Decision') }}</th>
                    <th>{{ __('Allowed') }}</th>
                    <th>{{ __('IP') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-xs text-slate-500">{{ $log->checked_at?->diffForHumans() }}</td>
                        <td class="font-mono text-xs">{{ $log->tenant_key ?? $log->tenant?->company_name ?? '—' }}</td>
                        <td class="text-xs">{{ $log->product_key ?? $log->project?->name ?? '—' }}</td>
                        <td class="font-mono text-xs text-slate-500">{{ $log->domain ?? '—' }}</td>
                        <td>
                            <x-ui.status-badge :variant="$log->allowed ? 'success' : 'danger'">
                                {{ $log->access_level ?? $log->decision }}
                            </x-ui.status-badge>
                        </td>
                        <td>{{ $log->allowed ? '✓' : '✗' }}</td>
                        <td class="font-mono text-[10px] text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-sm text-slate-500">{{ __('No license checks logged yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <x-slot name="footer">{{ $logs->links() }}</x-slot>
    </x-ui.table-panel>
</x-dashboard-layout>
