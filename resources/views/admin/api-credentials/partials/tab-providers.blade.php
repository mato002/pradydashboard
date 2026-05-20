<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Provider integrations') }}</h3>
        <p class="text-xs text-slate-500">{{ __('External APIs consumed by Prady (Hostinger, WHM, SMS, M-Pesa, OpenAI, SMTP, WhatsApp, S3, etc.)') }}</p>
    </div>
    <div class="prady-scrollbar overflow-x-auto">
        <table class="prady-table w-full min-w-[900px]">
            <thead>
                <tr>
                    <th>{{ __('Tenant') }}</th>
                    <th>{{ __('Project') }}</th>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Endpoint') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Last check') }}</th>
                    <th>{{ __('Response') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @forelse ($integrations as $integration)
                    <tr>
                        <td class="text-sm">{{ $integration->subscription?->tenant?->company_name ?? '—' }}</td>
                        <td class="text-sm">{{ $integration->subscription?->project?->name ?? '—' }}</td>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $integration->display_name }}</p>
                            <p class="text-xs text-slate-500">{{ $integration->serviceTypeLabel() }}</p>
                        </td>
                        <td class="max-w-xs truncate font-mono text-xs text-slate-500">{{ $integration->endpoint_url ?? '—' }}</td>
                        <td><x-ui.status-badge :variant="$integration->status === 'active' ? 'success' : ($integration->status === 'failing' ? 'danger' : 'neutral')">{{ str_replace('_', ' ', $integration->status) }}</x-ui.status-badge></td>
                        <td class="text-xs text-slate-500">{{ $integration->last_checked_at?->diffForHumans() ?? __('Never') }}</td>
                        <td class="text-xs font-mono text-slate-500">
                            @if ($integration->last_response_code)
                                {{ $integration->last_response_code }} · {{ $integration->last_response_time_ms }}ms
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-sm text-slate-500">{{ __('No provider integrations configured. Add them from a tenant profile under Integrations.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
