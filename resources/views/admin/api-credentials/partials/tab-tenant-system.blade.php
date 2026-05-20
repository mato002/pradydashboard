<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <motion.div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant system APIs') }}</h3>
        <p class="text-xs text-slate-500">{{ __('Outbound checks from Prady to tenant installations — communication status only.') }}</p>
    </motion.div>
    <div class="prady-scrollbar overflow-x-auto">
        <table class="prady-table w-full min-w-[960px]">
            <thead>
                <tr>
                    <th>{{ __('Tenant') }}</th>
                    <th>{{ __('Project') }}</th>
                    <th>{{ __('API') }}</th>
                    <th>{{ __('Purpose') }}</th>
                    <th>{{ __('Last check') }}</th>
                    <th>{{ __('HTTP') }}</th>
                    <th>{{ __('Response') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Contract') }}</th>
                    <th>{{ __('Uptime') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @forelse ($apis as $api)
                    @php
                        $tenant = $api->subscription?->tenant;
                        $subscription = $api->subscription;
                    @endphp
                    <tr>
                        <td class="text-sm font-medium">{{ $tenant?->company_name ?? '—' }}</td>
                        <td class="text-sm">{{ $subscription?->project?->name ?? '—' }}</td>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $api->resolvedApiName() }}</p>
                            <p class="text-xs text-slate-500">{{ $api->authenticationTypeLabel() }}</p>
                        </td>
                        <td class="text-xs">{{ $api->purposeLabel() }}</td>
                        <td class="text-xs text-slate-500">{{ $api->last_checked_at?->diffForHumans() ?? __('Never') }}</td>
                        <td class="font-mono text-xs">{{ $api->last_response_code ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $api->last_response_time_ms ? $api->last_response_time_ms.'ms' : '—' }}</td>
                        <td><x-ui.status-badge :variant="$api->status === 'active' ? 'success' : ($api->status === 'failing' ? 'danger' : 'neutral')">{{ str_replace('_', ' ', $api->status) }}</x-ui.status-badge></td>
                        <td>
                            @if ($label = $api->contractHealthLabel())
                                <x-ui.status-badge :variant="match ($api->contractHealth()) { 'valid' => 'success', 'partial' => 'warning', default => 'danger' }">{{ $label }}</x-ui.status-badge>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-xs tabular-nums">{{ $api->uptime_percentage !== null ? $api->uptime_percentage.'%' : '—' }}</td>
                        <td class="text-right">
                            @if ($tenant && $subscription)
                                <a href="{{ route('tenants.show', ['tenant' => $tenant, 'tab' => 'integrations', 'subscription' => $subscription->id, 'integration' => $api->id]) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Manage') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center text-sm text-slate-500">{{ __('No tenant system APIs configured yet. Add an endpoint from the tenant profile under Integrations.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
