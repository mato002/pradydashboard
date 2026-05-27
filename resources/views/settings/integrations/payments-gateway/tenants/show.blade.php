@php
    $statusVariant = fn (string $status): string => match (strtolower($status)) {
        'active', 'linked', 'pass', 'reachable', 'enabled' => 'success',
        'revoked', 'suspended', 'warn', 'warning', 'unreachable', 'pending', 'skip' => 'warning',
        'error', 'fail', 'blocked' => 'danger',
        default => 'neutral',
    };
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="$dashboardTenant->company_name">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        {{-- A. Link Status --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ __('Link status') }}</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $dashboardTenant->company_name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $dashboardTenant->tenant_key }}</p>
                </div>
                <x-ui.status-badge :variant="$linked ? 'success' : 'neutral'">
                    {{ $linked ? __('Linked') : __('Unlinked') }}
                </x-ui.status-badge>
            </div>

            <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Dashboard external key'), 'value' => $dashboardTenant->external_key])
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Gateway tenant UUID'), 'value' => $dashboardTenant->payments_gateway_tenant_uuid ?? '—'])
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Gateway status'), 'value' => $dashboardTenant->payments_gateway_status ?? '—'])
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Last linked/synced'), 'value' => filled($dashboardTenant->payments_gateway_linked_at) ? $dashboardTenant->payments_gateway_linked_at->format('M j, Y H:i:s') : '—'])
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Linkage health'), 'value' => ucfirst((string) ($linkageHealth['status'] ?? 'unknown'))])
                @include('settings.integrations.payments-gateway.partials.detail-field', ['label' => __('Primary domain'), 'value' => $dashboardTenant->tenant_domain ?? $dashboardTenant->project?->domain ?? '—'])
            </dl>

            @permission('payments_gateway.manage')
            <div class="mt-4 flex flex-wrap gap-2">
                @if (! $linked)
                    <form method="post" action="{{ route('settings.payments-gateway.tenants.link', $dashboardTenant) }}" onsubmit="return confirm(@js(__('Link this tenant to Payments Gateway?')))">
                        @csrf
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Link existing tenant') }}</button>
                    </form>
                @else
                    <form method="post" action="{{ route('settings.payments-gateway.tenants.sync', $dashboardTenant) }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Sync treasury mapping') }}</button>
                    </form>
                    <form method="post" action="{{ route('settings.payments-gateway.tenants.unlink', $dashboardTenant) }}" onsubmit="return confirm(@js(__('Unlinking only removes the dashboard link. It does not delete treasury records on payments.pradytecai.com.')))">
                        @csrf
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 dark:border-rose-900 dark:text-rose-300">{{ __('Unlink') }}</button>
                    </form>
                @endif
            </div>
            @endpermission
        </section>

        @if (! $linked)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">
                <p>{{ __('Link this dashboard tenant to payments.pradytecai.com before managing payment profiles, PayBills, webhooks, or gateway API keys.') }}</p>
                <p class="mt-2 text-xs">{{ __('Tenants are created in the main Tenant module. Payments Gateway only mirrors treasury resources after linking.') }}</p>
            </div>
        @else
            {{-- F. Quick Setup Checklist --}}
            @include('settings.integrations.payments-gateway.tenants.partials.setup-checklist', ['checklist' => $checklist])

            {{-- B. Payment Profiles --}}
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Payment profiles') }}</h3>
                    @permission('payments_gateway.manage')
                    <a href="{{ route('settings.payments-gateway.tenants.payment-profiles.create', $dashboardTenant) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">{{ __('Create payment profile') }}</a>
                    @endpermission
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">{{ __('Profile') }}</th>
                                <th class="px-3 py-2">{{ __('Environment') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2">{{ __('Default collection') }}</th>
                                <th class="px-3 py-2">{{ __('Default disbursement') }}</th>
                                <th class="px-3 py-2">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($profiles as $profile)
                                @php
                                    $collectionAccount = collect($paybillAccounts)->first(fn ($a) => ($a['uuid'] ?? null) === ($profile['default_collection_account_uuid'] ?? null));
                                    $disbursementAccount = collect($paybillAccounts)->first(fn ($a) => ($a['uuid'] ?? null) === ($profile['default_disbursement_account_uuid'] ?? null));
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $profile['name'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst((string) ($profile['environment'] ?? '—')) }}</td>
                                    <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($profile['status'] ?? 'unknown'))">{{ ucfirst((string) ($profile['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                    <td class="px-3 py-2 text-xs">{{ $formatPaybillLabel($collectionAccount) }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $formatPaybillLabel($disbursementAccount) }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col gap-1">
                                            <a href="{{ route('settings.payments-gateway.payment-profiles.show', $profile['uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View profile') }}</a>
                                            @permission('payments_gateway.manage')
                                            <a href="{{ route('settings.payments-gateway.tenants.paybill-accounts.create', [$dashboardTenant, $profile['uuid']]) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Add PayBill') }}</a>
                                            @endpermission
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No payment profiles configured yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- C. PayBill Accounts --}}
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('PayBill accounts') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('Production readiness and go-live dry run open in the context of the selected PayBill account.') }}</p>
                <div class="mt-4 space-y-6">
                    @forelse ($groupedPaybills as $group)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group['profile']['name'] ?? __('Profile') }}</p>
                            <div class="mt-2 overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                    <thead>
                                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            <th class="px-3 py-2">{{ __('Account') }}</th>
                                            <th class="px-3 py-2">{{ __('Type') }}</th>
                                            <th class="px-3 py-2">{{ __('Shortcode') }}</th>
                                            <th class="px-3 py-2">{{ __('STK shortcode') }}</th>
                                            <th class="px-3 py-2">{{ __('Capabilities') }}</th>
                                            <th class="px-3 py-2">{{ __('Status') }}</th>
                                            <th class="px-3 py-2">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @forelse ($group['accounts'] as $account)
                                            <tr>
                                                <td class="px-3 py-2">{{ $account['account_name'] ?? '—' }}</td>
                                                <td class="px-3 py-2">{{ ucfirst((string) ($account['account_type'] ?? '—')) }}</td>
                                                <td class="px-3 py-2">{{ $account['shortcode'] ?? '—' }}</td>
                                                <td class="px-3 py-2">{{ $account['stk_shortcode'] ?? '—' }}</td>
                                                <td class="px-3 py-2 text-xs">{{ $formatCapabilities($account) }}</td>
                                                <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($account['status'] ?? 'unknown'))">{{ ucfirst((string) ($account['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                                <td class="px-3 py-2">
                                                    <div class="flex flex-col gap-1 text-xs font-semibold">
                                                        <a href="{{ route('settings.payments-gateway.paybill-accounts.edit', $account['uuid']) }}" class="text-indigo-600 dark:text-indigo-400">{{ __('View/Edit') }}</a>
                                                        <a href="{{ route('settings.payments-gateway.production-readiness', ['paybill_account_uuid' => $account['uuid'], 'run' => 1]) }}" class="text-slate-600 dark:text-slate-300" title="{{ __('Production readiness opens in context of this PayBill.') }}">{{ __('Production Readiness') }}</a>
                                                        <a href="{{ route('settings.payments-gateway.go-live-dry-run', ['paybill_account_uuid' => $account['uuid'], 'run' => 1]) }}" class="text-slate-600 dark:text-slate-300" title="{{ __('Go-live dry run opens in context of this PayBill.') }}">{{ __('Go-Live Dry Run') }}</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="px-3 py-4 text-center text-slate-500">{{ __('No PayBill accounts for this profile.') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No PayBill accounts configured.') }}</p>
                    @endforelse
                </div>
            </section>

            {{-- D. Webhook Endpoints --}}
            <section id="treasury-webhooks" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Webhook endpoints') }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Webhook endpoint test requires gateway support.') }}</p>
                    </div>
                </div>
                <div class="mt-3 rounded-xl border border-indigo-200/80 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-100">
                    <p class="font-semibold">{{ __('Expected tenant listener URL') }}</p>
                    <p class="mt-1 font-mono text-xs break-all">{{ $expectedTenantWebhookUrl }}</p>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse ($webhookEndpoints as $endpoint)
                        <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200/80 px-3 py-2 dark:border-slate-800">
                            <div>
                                <p class="font-medium">{{ $endpoint['url'] ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $endpoint['payment_profile_name'] ?? '—' }} · {{ ucfirst((string) ($endpoint['status'] ?? 'unknown')) }}</p>
                            </div>
                            <div class="flex flex-col gap-1 text-xs font-semibold">
                                @permission('payments_gateway.manage')
                                <a href="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $endpoint['payment_profile_uuid']) }}" class="text-indigo-600 dark:text-indigo-400">{{ __('Manage endpoint') }}</a>
                                <form method="post" action="{{ route('settings.payments-gateway.tenants.webhook-endpoints.test', [$dashboardTenant, $endpoint['uuid']]) }}">
                                    @csrf
                                    <button type="submit" class="text-left text-slate-600 dark:text-slate-300">{{ __('Test endpoint') }}</button>
                                </form>
                                @endpermission
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No webhook endpoints configured.') }}</p>
                        @permission('payments_gateway.manage')
                        @if (count($profiles) > 0)
                            <a href="{{ route('settings.payments-gateway.payment-profiles.webhook-endpoints.index', $profiles[0]['uuid']) }}" class="inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Add webhook endpoint') }}</a>
                        @endif
                        @endpermission
                    @endforelse
                </div>
            </section>

            {{-- E. Gateway API Keys --}}
            <section id="treasury-api-keys" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Gateway API keys') }}</h3>
                <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">{{ __('Raw gateway API keys are shown once during generation and are never stored in the dashboard.') }}</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">{{ __('Name') }}</th>
                                <th class="px-3 py-2">{{ __('Profile') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($apiKeys as $apiKey)
                                <tr>
                                    <td class="px-3 py-2">{{ $apiKey['name'] ?? $apiKey['label'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $apiKey['payment_profile_name'] ?? '—' }}</td>
                                    <td class="px-3 py-2"><x-ui.status-badge :variant="$statusVariant((string) ($apiKey['status'] ?? 'unknown'))">{{ ucfirst((string) ($apiKey['status'] ?? 'unknown')) }}</x-ui.status-badge></td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('settings.payments-gateway.payment-profiles.api-keys.index', $apiKey['payment_profile_uuid']) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Manage keys') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ __('No gateway API keys found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @permission('payments_gateway.manage')
                @if (count($profiles) > 0)
                    <a href="{{ route('settings.payments-gateway.payment-profiles.api-keys.index', $profiles[0]['uuid']) }}" class="mt-3 inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Generate key') }}</a>
                @endif
                @endpermission
            </section>
        @endif
    </div>
</x-dashboard-layout>
