@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active', 'enabled' => 'success',
        'disabled', 'suspended' => 'warning',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Webhook endpoints')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($profile)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900/80">
                <span>
                    {{ __('Payment profile') }}:
                    <a href="{{ route('settings.payments-gateway.payment-profiles.show', $profileUuid) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $profile['name'] ?? $profileUuid }}</a>
                </span>
                @permission('payments_gateway.manage')
                <a href="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.create', $profileUuid) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Add endpoint') }}</a>
                @endpermission
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/40">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('URL') }}</th>
                        <th class="px-4 py-3">{{ __('Events') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Failures') }}</th>
                        @permission('payments_gateway.manage')<th class="px-4 py-3">{{ __('Actions') }}</th>@endpermission
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($endpoints as $endpoint)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $endpoint['name'] ?? '—' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate text-xs">{{ $endpoint['url'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ collect($endpoint['events'] ?? [])->implode(', ') ?: '—' }}</td>
                            <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($endpoint['status'] ?? 'unknown'))">{{ ucfirst((string) ($endpoint['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                            <td class="px-4 py-3 tabular-nums">{{ $endpoint['failure_count'] ?? 0 }}</td>
                            @permission('payments_gateway.manage')
                            <td class="px-4 py-3 text-right" @click.stop>
                                <x-ui.row-actions-menu>
                                    <x-ui.row-action :href="route('settings.payments-gateway.webhook-endpoints.edit', $endpoint['uuid'])">{{ __('Edit') }}</x-ui.row-action>
                                    @if (in_array(strtolower((string) ($endpoint['status'] ?? '')), ['active', 'enabled'], true))
                                        <x-ui.row-action :href="route('settings.payments-gateway.webhook-endpoints.disable', $endpoint['uuid'])" method="POST" :confirm="__('Disable this webhook endpoint?')" danger>{{ __('Disable') }}</x-ui.row-action>
                                    @else
                                        <x-ui.row-action :href="route('settings.payments-gateway.webhook-endpoints.enable', $endpoint['uuid'])" method="POST" :confirm="__('Enable this webhook endpoint?')">{{ __('Enable') }}</x-ui.row-action>
                                    @endif
                                </x-ui.row-actions-menu>
                            </td>
                            @endpermission
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No webhook endpoints configured.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dashboard-layout>
