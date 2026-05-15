@php
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'expiring_soon' => 'warning',
        'expired', 'invalid_ssl', 'dns_error' => 'danger',
        default => 'neutral',
    };
    $sslVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'expiring_soon' => 'warning',
        'expired', 'invalid' => 'danger',
        default => 'info',
    };
    $alertRing = fn (string $t): string => match ($t) {
        'critical', 'danger' => 'ring-rose-500/30 bg-rose-500/10',
        'warning' => 'ring-amber-500/30 bg-amber-500/10',
        'success' => 'ring-emerald-500/30 bg-emerald-500/10',
        default => 'ring-sky-500/30 bg-sky-500/10',
    };
    $timelineMax = max(collect($expiryTimeline)->max(fn ($b) => max($b['ssl'], $b['domain'])) ?? 0, 1);
@endphp

<x-dashboard-layout :heading="__('SSL & Domains')" :subheading="__('Certificate, DNS & routing operations')">
    <div
                x-data="{ toast: @js(session('status')) }"
                x-init="if (toast) { setTimeout(() => toast = null, 4000) }"
                class="space-y-6"
            >
                <div
                    x-show="toast"
                    x-transition
                    class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-emerald-500/30 bg-emerald-950/90 px-4 py-3 text-sm text-emerald-100 shadow-2xl backdrop-blur"
                    x-cloak
                >
                    <span x-text="toast"></span>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ __('Networking') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Domain & Certificate Management Center') }}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Domains, SSL lifecycle, DNS records, tenant routing, and expiry monitoring — enterprise edge operations.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('ssl-domains.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            {{ __('Add Domain') }}
                        </a>
                        <form method="POST" action="{{ route('ssl-domains.renew') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                                {{ __('Renew SSL') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('ssl-domains.verify-dns') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3.75-6H6.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75M9 12.75h.008v.008H9v-.008Z" /></svg>
                                {{ __('Verify DNS') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <x-ui.kpi-card :title="__('Total Domains')" :value="$kpis['total']" :trend="'+4'" :sublabel="__('Managed zones')" :points="$spark('dom-total')" tone="indigo">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.467.732-3.582" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Active SSL')" :value="$kpis['activeSsl']" :trend="'+2'" :sublabel="__('Valid certificates')" :points="$spark('dom-ssl')" tone="emerald">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3.75-6H6.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75M9 12.75h.008v.008H9v-.008Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Expiring Certs')" :value="$kpis['expiringSsl']" :trend="$kpis['expiringSsl'] > 0 ? '!' : '0'" :sublabel="__('Within 30 days')" :points="$spark('dom-exp')" tone="amber">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('DNS Errors')" :value="$kpis['dnsErrors']" :trend="$kpis['dnsErrors'] > 0 ? '-1' : '0'" :sublabel="__('Zones + records')" :points="$spark('dom-dns')" tone="rose">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Tenant Domains')" :value="$kpis['tenantDomains']" :trend="'+6'" :sublabel="__('Custom hostnames')" :points="$spark('dom-tenant')" tone="violet">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0z" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                    <x-ui.kpi-card :title="__('Renewal Alerts')" :value="$kpis['renewalAlerts']" :trend="$kpis['renewalAlerts'] > 0 ? '!' : '0'" :sublabel="__('SSL + domain')" :points="$spark('dom-alert')" tone="sky">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                        </x-slot>
                    </x-ui.kpi-card>
                </div>

                <div class="grid gap-5 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <x-ui.table-panel :title="__('Managed Domains')">
                            <table class="prady-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Domain') }}</th>
                                        <th>{{ __('Tenant') }}</th>
                                        <th>{{ __('SSL Status') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                        <th>{{ __('Registrar') }}</th>
                                        <th>{{ __('DNS') }}</th>
                                        <th>{{ __('Auto Renew') }}</th>
                                        <th>{{ __('Server') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    @foreach ($domains as $domain)
                                        <tr class="group">
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    @if ($domain->is_wildcard)
                                                        <span class="rounded bg-violet-500/15 px-1.5 py-0.5 text-[10px] font-bold uppercase text-violet-600 dark:text-violet-300">WC</span>
                                                    @endif
                                                    <div>
                                                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $domain->domain }}</p>
                                                        @if ($domain->routing_target)
                                                            <p class="text-[10px] text-slate-500">→ {{ $domain->routing_target }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $domain->tenant?->company_name ?? '—' }}</td>
                                            <td>
                                                <x-ui.status-badge :variant="$sslVariant($domain->ssl_status)">{{ ucfirst(str_replace('_', ' ', $domain->ssl_status)) }}</x-ui.status-badge>
                                            </td>
                                            <td class="text-xs">
                                                <p class="font-medium text-slate-700 dark:text-slate-200">{{ $domain->ssl_expires_at?->format('M j, Y') ?? '—' }}</p>
                                                <p class="text-slate-500">{{ $domain->sslExpiryLabel() }}</p>
                                            </td>
                                            <td class="text-xs text-slate-500">{{ $domain->registrar ?? '—' }}</td>
                                            <td>
                                                <x-ui.status-badge :variant="$domain->dns_status === 'healthy' ? 'success' : ($domain->dns_status === 'propagating' ? 'warning' : 'danger')">
                                                    {{ ucfirst($domain->dns_status) }}
                                                </x-ui.status-badge>
                                            </td>
                                            <td>
                                                @if ($domain->auto_renew)
                                                    <span class="text-emerald-600 dark:text-emerald-400">✓</span>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="text-xs text-slate-500">{{ $domain->server?->name ?? '—' }}</td>
                                            <td class="text-right">
                                                <div class="inline-flex gap-1 opacity-70 group-hover:opacity-100">
                                                            <button type="button" title="{{ __('View Certificate') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:hover:bg-slate-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3.75-6H6.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75M9 12.75h.008v.008H9v-.008Z" /></svg></button>
                                                            <button type="button" title="{{ __('Configure Routing') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg></button>
                                                            <button type="button" title="{{ __('Force Renewal') }}" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:hover:bg-slate-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg></button>
                                                        </div>
                                                    </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <x-slot name="footer">{{ $domains->links() }}</x-slot>
                        </x-ui.table-panel>
                    </div>

                    <div class="space-y-5 lg:col-span-4">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                            <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Expiry Alerts') }}</h2>
                            </div>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($alerts as $alert)
                                    <li class="flex gap-3 px-4 py-3.5">
                                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $alertRing($alert['type']) }}">
                                            <svg class="h-4 w-4 text-current opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $alert['body'] }}</p>
                                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $alert['time'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-emerald-200/50 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/40 p-4 shadow-card dark:border-emerald-900/40 dark:from-emerald-950/30 dark:via-slate-900 dark:to-teal-950/20">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Expiry Horizon') }}</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('SSL vs domain renewals') }}</p>
                            <div class="mt-4 flex h-28 items-end gap-2">
                                @foreach ($expiryTimeline as $bucket)
                                    @php
                                        $sslH = max(8, (int) round(($bucket['ssl'] / $timelineMax) * 100));
                                        $domH = max(8, (int) round(($bucket['domain'] / $timelineMax) * 100));
                                    @endphp
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="flex w-full items-end justify-center gap-0.5">
                                            <div class="w-2 rounded-t bg-emerald-500/80" style="height: {{ $sslH }}px" title="SSL: {{ $bucket['ssl'] }}"></div>
                                            <div class="w-2 rounded-t bg-teal-400/70" style="height: {{ $domH }}px" title="Domain: {{ $bucket['domain'] }}"></div>
                                        </div>
                                        <span class="text-[9px] font-medium text-slate-500">{{ $bucket['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('SSL Monitoring') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Validity, issuers, wildcard certs, renewal history') }}</p>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($sslMonitoring as $cert)
                                @php $days = $cert->daysUntilSslExpiry(); @endphp
                                <div class="flex flex-wrap items-center gap-4 px-4 py-4">
                                    <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                                        <span class="text-lg font-bold tabular-nums {{ $days !== null && $days < 30 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $days !== null ? max(0, $days) : '—' }}</span>
                                        <span class="text-[9px] font-semibold uppercase text-slate-500">{{ __('days') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $cert->domain }}</p>
                                        <p class="text-xs text-slate-500">{{ $cert->ssl_issuer }} · {{ $cert->ssl_expires_at?->format('M j, Y') }}</p>
                                        @if ($cert->is_wildcard)
                                            <span class="mt-1 inline-block rounded bg-violet-500/10 px-2 py-0.5 text-[10px] font-semibold text-violet-600 dark:text-violet-300">{{ __('Wildcard') }}</span>
                                        @endif
                                        @if ($cert->auto_renew)
                                            <span class="mt-1 inline-block rounded bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-300">{{ __('Auto-renew on') }}</span>
                                        @endif
                                    </div>
                                    <x-ui.status-badge :variant="$sslVariant($cert->ssl_status)">{{ ucfirst($cert->ssl_status) }}</x-ui.status-badge>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant Domain Mapping') }}</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('SaaS routing & custom hostnames') }}</p>
                                </div>
                                <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                    @foreach ($tenantMappings as $map)
                                        <li class="px-4 py-3.5">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="font-mono text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $map->domain }}</p>
                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ $map->tenant?->company_name ?? __('Platform zone') }}
                                                        @if ($map->routing_target)
                                                            · {{ $map->routing_target }}
                                                        @endif
                                                    </p>
                                                </div>
                                                @if ($map->is_tenant_custom)
                                                    <span class="shrink-0 rounded-full bg-indigo-500/10 px-2 py-0.5 text-[10px] font-semibold text-indigo-600 dark:text-indigo-300">{{ __('Custom') }}</span>
                                                @else
                                                    <span class="shrink-0 rounded-full bg-slate-500/10 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">{{ __('Subdomain') }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('DNS Records') }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('A, CNAME, TXT, MX, NS — propagation status') }}</p>
                        </div>
                        <div class="flex gap-2 text-[10px] font-semibold uppercase tracking-wide">
                            <span class="rounded-full bg-emerald-500/10 px-2 py-1 text-emerald-700 dark:text-emerald-300">{{ __('Propagated') }}</span>
                            <span class="rounded-full bg-amber-500/10 px-2 py-1 text-amber-700 dark:text-amber-300">{{ __('Pending') }}</span>
                            <span class="rounded-full bg-rose-500/10 px-2 py-1 text-rose-700 dark:text-rose-300">{{ __('Failed') }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="prady-table min-w-[800px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Zone') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Host') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('TTL') }}</th>
                                    <th>{{ __('Propagation') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($dnsRecords as $record)
                                    <tr>
                                        <td class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ $record->domain?->domain }}</td>
                                        <td><span class="rounded bg-sky-500/10 px-2 py-0.5 font-mono text-[11px] font-bold text-sky-700 dark:text-sky-300">{{ $record->record_type }}</span></td>
                                        <td class="font-mono text-xs">{{ $record->host }}</td>
                                        <td class="max-w-xs truncate font-mono text-xs text-slate-500" title="{{ $record->value }}">{{ $record->value }}</td>
                                        <td class="tabular-nums text-xs text-slate-500">{{ $record->ttl }}</td>
                                        <td>
                                            <x-ui.status-badge :variant="$record->propagationVariant()">{{ ucfirst($record->propagation_status) }}</x-ui.status-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-dashboard-layout>

