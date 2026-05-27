@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active' => 'success',
        'revoked', 'suspended' => 'warning',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Gateway API keys')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        @if ($rawKey)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950">
                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ __('Copy this gateway API key now') }}</p>
                <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-200">{{ __('This raw key is shown once and is not stored in the dashboard.') }}</p>
                <code class="mt-3 block overflow-x-auto rounded-xl bg-white/80 px-4 py-3 text-sm font-mono text-slate-900 dark:bg-slate-900 dark:text-emerald-200">{{ $rawKey }}</code>
            </div>
        @endif

        @if ($profile)
            <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900/80">
                {{ __('Payment profile') }}:
                <a href="{{ route('settings.payments-gateway.payment-profiles.show', $profileUuid) }}" class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $profile['name'] ?? $profileUuid }}</a>
            </div>
        @endif

        @permission('payments_gateway.manage')
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Generate API key') }}</h3>
            <form method="post" action="{{ route('settings.payments-gateway.payment-profiles.api-keys.store', $profileUuid) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Key name'), 'name' => 'name', 'required' => true])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Allowed IPs'), 'name' => 'allowed_ips', 'type' => 'textarea', 'hint' => __('Optional. One IP per line or comma-separated')])
                @include('settings.integrations.payments-gateway.partials.form-field', ['label' => __('Expires at'), 'name' => 'expires_at', 'type' => 'datetime-local'])
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Generate key') }}</button>
                </div>
            </form>
        </div>
        @endpermission

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/40">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Prefix') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Last used') }}</th>
                        <th class="px-4 py-3">{{ __('Expires') }}</th>
                        @permission('payments_gateway.manage')<th class="px-4 py-3">{{ __('Actions') }}</th>@endpermission
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($keys as $key)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $key['name'] ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $key['key_prefix'] ?? '—' }}••••</td>
                            <td class="px-4 py-3"><x-ui.status-badge :variant="$statusVariant((string) ($key['status'] ?? 'unknown'))">{{ ucfirst((string) ($key['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                            <td class="px-4 py-3 text-xs">{{ filled($key['last_used_at'] ?? null) ? \Illuminate\Support\Carbon::parse($key['last_used_at'])->diffForHumans() : '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ filled($key['expires_at'] ?? null) ? \Illuminate\Support\Carbon::parse($key['expires_at'])->format('M j, Y H:i') : '—' }}</td>
                            @permission('payments_gateway.manage')
                            <td class="px-4 py-3">
                                @if (($key['status'] ?? '') !== 'revoked')
                                    @include('settings.integrations.payments-gateway.partials.action-form', [
                                        'action' => route('settings.payments-gateway.payment-profiles.api-keys.revoke', [$profileUuid, $key['uuid']]),
                                        'label' => __('Revoke'),
                                        'confirm' => __('Revoke this gateway API key? Integrations using it will stop working.'),
                                        'variant' => 'danger',
                                    ])
                                @else
                                    —
                                @endif
                            </td>
                            @endpermission
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No gateway API keys yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dashboard-layout>
