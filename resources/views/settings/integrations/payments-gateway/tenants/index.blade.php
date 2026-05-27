@php
    $linkageVariant = fn (bool $linked): string => $linked ? 'success' : 'neutral';
    $healthVariant = fn (string $status): string => match (strtolower($status)) {
        'active', 'linked', 'pass' => 'success',
        'unlinked' => 'neutral',
        'suspended', 'warn', 'warning', 'unreachable' => 'warning',
        'error', 'fail', 'blocked' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Treasury Mapping')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="rounded-2xl border border-slate-200/80 bg-indigo-50 p-4 text-sm text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
            {{ __('Dashboard tenants are managed in the main Tenant module. Link an existing tenant here to map treasury resources on payments.pradytecai.com.') }}
        </div>

        <form method="get" action="{{ route('settings.payments-gateway.tenants.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="grid gap-3 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label for="search" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Search') }}</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="{{ __('Company name, tenant key, domain, gateway UUID…') }}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="linkage" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Linkage') }}</label>
                    <select id="linkage" name="linkage" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        <option value="">{{ __('All tenants') }}</option>
                        <option value="linked" @selected($filters['linkage'] === 'linked')>{{ __('Linked') }}</option>
                        <option value="unlinked" @selected($filters['linkage'] === 'unlinked')>{{ __('Unlinked') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <a href="{{ route('settings.payments-gateway.tenants.index') }}" class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Apply filters') }}</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">{{ __('Tenant') }}</th>
                            <th class="px-4 py-3">{{ __('Linkage') }}</th>
                            <th class="px-4 py-3">{{ __('Profiles') }}</th>
                            <th class="px-4 py-3">{{ __('PayBills') }}</th>
                            <th class="px-4 py-3">{{ __('Last sync') }}</th>
                            <th class="px-4 py-3">{{ __('Gateway health') }}</th>
                            <th class="px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($tenants as $tenant)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $tenant['company_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $tenant['tenant_key'] ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.status-badge :variant="$linkageVariant($tenant['linked'])">
                                        {{ $tenant['linked'] ? __('Linked') : __('Unlinked') }}
                                    </x-ui.status-badge>
                                </td>
                                <td class="px-4 py-3 tabular-nums">{{ $tenant['linked'] ? ($tenant['payment_profiles_count'] ?? '—') : '—' }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $tenant['linked'] ? ($tenant['paybill_accounts_count'] ?? '—') : '—' }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ filled($tenant['payments_gateway_linked_at']) ? $tenant['payments_gateway_linked_at']->format('M j, Y H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.status-badge :variant="$healthVariant((string) $tenant['gateway_health'])">
                                        {{ ucfirst((string) $tenant['gateway_health']) }}
                                    </x-ui.status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('settings.payments-gateway.tenants.show', $tenant['id']) }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $tenant['linked'] ? __('Treasury mapping') : __('View tenant') }}
                                        </a>
                                        @permission('payments_gateway.manage')
                                            @if (! $tenant['linked'])
                                                <form method="post" action="{{ route('settings.payments-gateway.tenants.link', $tenant['id']) }}" onsubmit="return confirm(@js(__('Link this tenant to Payments Gateway?')))">
                                                    @csrf
                                                    <button type="submit" class="text-left text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Link existing tenant') }}</button>
                                                </form>
                                            @endif
                                        @endpermission
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    {{ __('No dashboard tenants found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-layout>
