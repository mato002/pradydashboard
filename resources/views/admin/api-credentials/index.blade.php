@php
    $statusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'revoked', 'expired' => 'danger',
        'suspended' => 'warning',
        default => 'neutral',
    };

    $webhookStatusVariant = fn (string $s): string => match ($s) {
        'active' => 'success',
        'degraded' => 'warning',
        'paused', 'failed' => 'danger',
        default => 'neutral',
    };

    $volumeMax = max(collect($analytics['request_volume'])->max('value') ?? 1, 1);
    $baseUrl = rtrim($developer['base_url'], '/');
@endphp

<x-dashboard-layout :heading="__('API & Integrations')" :subheading="__('Enterprise API security and integration management')">
    <div
        x-data="apiCredentialsCenter(@js($apiKeys), @js($webhooks), @js($tokenDetail), @js($developer))"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Developer platform') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('API Security & Integration Management') }}</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('API keys, OAuth credentials, webhooks, scopes, IP whitelisting, rate limits, and integration analytics.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-500/20 dark:text-indigo-300">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    {{ __('Gateway live') }}
                </span>
                <a href="{{ route('api-credentials.keys.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    {{ __('Generate API Key') }}
                </a>
                <a href="{{ route('api-credentials.webhooks.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Add Webhook') }}
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.kpi-card :title="__('Active API keys')" :value="$kpis['active_keys']['value']" :trend="$kpis['active_keys']['trend']" :sublabel="$kpis['active_keys']['sublabel']" :points="$kpis['active_keys']['points']" :tone="$kpis['active_keys']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('API requests today')" :value="$kpis['requests_today']['value']" :animate="false" :trend="$kpis['requests_today']['trend']" :sublabel="$kpis['requests_today']['sublabel']" :points="$kpis['requests_today']['points']" :tone="$kpis['requests_today']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Failed requests')" :value="$kpis['failed_requests']['value']" :trend="$kpis['failed_requests']['trend']" :sublabel="$kpis['failed_requests']['sublabel']" :points="$kpis['failed_requests']['points']" :tone="$kpis['failed_requests']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Webhook deliveries')" :value="$kpis['webhook_deliveries']['value']" :animate="false" :trend="$kpis['webhook_deliveries']['trend']" :sublabel="$kpis['webhook_deliveries']['sublabel']" :points="$kpis['webhook_deliveries']['points']" :tone="$kpis['webhook_deliveries']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Rate limit violations')" :value="$kpis['rate_violations']['value']" :trend="$kpis['rate_violations']['trend']" :sublabel="$kpis['rate_violations']['sublabel']" :points="$kpis['rate_violations']['points']" :tone="$kpis['rate_violations']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-6 10.5 6M4.5 19.5h15" /></svg></x-slot>
            </x-ui.kpi-card>
            <x-ui.kpi-card :title="__('Active integrations')" :value="$kpis['active_integrations']['value']" :trend="$kpis['active_integrations']['trend']" :sublabel="$kpis['active_integrations']['sublabel']" :points="$kpis['active_integrations']['points']" :tone="$kpis['active_integrations']['tone']">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg></x-slot>
            </x-ui.kpi-card>
        </div>

        {{-- Tab nav --}}
        <div class="flex flex-wrap gap-1 rounded-xl border border-slate-200/80 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-900/80">
            @foreach (['keys' => __('API Keys'), 'webhooks' => __('Webhooks'), 'security' => __('Security'), 'analytics' => __('Analytics'), 'developer' => __('Developer')] as $tab => $label)
                <button
                    type="button"
                    @click="activeTab = '{{ $tab }}'"
                    :class="activeTab === '{{ $tab }}' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400'"
                    class="rounded-lg px-4 py-2 text-xs font-semibold transition"
                >{{ $label }}</button>
            @endforeach
        </div>

        {{-- API Keys tab --}}
        <div x-show="activeTab === 'keys'" class="grid gap-5 xl:grid-cols-12">
            <div class="xl:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('API keys') }}</h3>
                            <p class="text-xs text-slate-500" x-text="filteredKeys.length + ' {{ __('keys') }}'"></p>
                        </div>
                        <select x-model="filterStatus" class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-8 text-xs font-medium dark:border-slate-700 dark:bg-slate-800">
                            <option value="">{{ __('All statuses') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="revoked">{{ __('Revoked') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                            <option value="suspended">{{ __('Suspended') }}</option>
                        </select>
                    </div>
                    <div class="prady-scrollbar overflow-x-auto">
                        <table class="prady-table w-full min-w-[1000px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('API Key Name') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th>{{ __('Last Used') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Expiry') }}</th>
                                    <th>{{ __('Rate Limit') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <template x-for="key in filteredKeys" :key="key.id">
                                    <tr
                                        @click="selectKey(key)"
                                        class="cursor-pointer transition hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                                        :class="selectedKey?.id === key.id ? 'bg-indigo-50/80 dark:bg-indigo-500/10' : ''"
                                    >
                                        <td class="font-medium text-slate-900 dark:text-white" x-text="key.project"></td>
                                        <td class="text-sm text-slate-600 dark:text-slate-400" x-text="key.tenant"></td>
                                        <td>
                                            <p class="font-medium text-slate-800 dark:text-slate-200" x-text="key.name"></p>
                                            <p class="font-mono text-[10px] text-slate-400" x-text="key.masked_token"></p>
                                        </td>
                                        <td class="max-w-[140px] truncate text-xs text-slate-500" x-text="key.permissions" :title="key.permissions"></td>
                                        <td class="text-xs text-slate-500" x-text="key.last_used"></td>
                                        <td>
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ring-1 ring-inset"
                                                :class="{
                                                    'bg-emerald-500/12 text-emerald-700 ring-emerald-500/20': key.status === 'active',
                                                    'bg-rose-500/12 text-rose-700 ring-rose-500/20': key.status === 'revoked' || key.status === 'expired',
                                                    'bg-amber-500/12 text-amber-800 ring-amber-500/20': key.status === 'suspended',
                                                }"
                                                x-text="key.status"
                                            ></span>
                                        </td>
                                        <td class="text-xs text-slate-500" x-text="key.expiry"></td>
                                        <td class="font-mono text-xs text-indigo-600 dark:text-indigo-400" x-text="key.rate_limit"></td>
                                        <td class="text-right" @click.stop>
                                            <div class="flex justify-end gap-1">
                                                <a :href="'{{ url('api-credentials/keys') }}/' + key.id" class="rounded-lg px-2 py-1.5 text-[10px] font-semibold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10">{{ __('View') }}</a>
                                                <button type="button" @click="copyToken(key)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800" title="{{ __('Copy') }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.807-2.057 1.907-2.185a48.507 48.507 0 011.927-.184" /></svg>
                                                </button>
                                                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-amber-600 dark:hover:bg-slate-800" title="{{ __('Rotate') }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                                </button>
                                                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-rose-600 dark:hover:bg-slate-800" title="{{ __('Revoke') }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Token detail panel --}}
            <div class="xl:col-span-4 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Token management') }}</h3>
                        <p class="text-xs text-slate-500" x-show="selectedKey" x-text="selectedKey?.name"></p>
                        <p class="text-xs text-slate-500" x-show="!selectedKey">{{ __('Select a key to manage') }}</p>
                    </div>
                    <template x-if="selectedKey">
                        <div class="space-y-4 p-4">
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('API token') }}</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <code class="flex-1 overflow-x-auto rounded-lg bg-slate-950 px-3 py-2 font-mono text-[11px] text-emerald-400" x-text="showToken ? selectedKey.full_token : selectedKey.masked_token"></code>
                                    <button type="button" @click="showToken = !showToken" class="shrink-0 rounded-lg border border-slate-200 px-2 py-1.5 text-[10px] font-semibold dark:border-slate-700" x-text="showToken ? '{{ __('Hide') }}' : '{{ __('Reveal') }}'"></button>
                                </div>
                                <button type="button" @click="copyToken(selectedKey)" class="mt-2 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy API key') }}'"></button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                    <p class="text-slate-500">{{ __('Created') }}</p>
                                    <p class="font-semibold text-slate-900 dark:text-white" x-text="selectedKey.created"></p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-800">
                                    <p class="text-slate-500">{{ __('Expires') }}</p>
                                    <p class="font-semibold text-slate-900 dark:text-white" x-text="selectedKey.expiry"></p>
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Scopes') }}</p>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="scope in selectedKey.scopes" :key="scope">
                                        <span class="rounded-md bg-indigo-500/10 px-2 py-0.5 font-mono text-[10px] font-medium text-indigo-700 dark:text-indigo-300" x-text="scope"></span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-[11px] font-semibold text-white">{{ __('Rotate secret') }}</button>
                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-semibold dark:border-slate-700">{{ __('Configure scope') }}</button>
                                <button type="button" class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-semibold text-rose-600 dark:border-rose-500/30">{{ __('Revoke') }}</button>
                            </div>
                            <div>
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('IP access history') }}</p>
                                <ul class="space-y-1.5">
                                    <template x-for="row in (tokenDetail[selectedKey.id]?.ip_history ?? [])" :key="row.ip + row.time">
                                        <li class="flex justify-between rounded-lg bg-slate-50 px-2 py-1.5 text-[11px] dark:bg-slate-800">
                                            <span class="font-mono text-slate-700 dark:text-slate-300" x-text="row.ip"></span>
                                            <span class="text-slate-400" x-text="row.time + ' · ' + row.action"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedKey">
                        <p class="p-6 text-center text-sm text-slate-500">{{ __('Select an API key from the table.') }}</p>
                    </template>
                </div>
            </div>
        </div>

        {{-- Webhooks tab --}}
        <div x-show="activeTab === 'webhooks'" class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7 space-y-4">
                @foreach ($webhooks as $wh)
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60"
                        x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                    >
                        <div class="flex items-center justify-end gap-2 border-b border-slate-100 px-4 pt-3 dark:border-slate-800">
                            <a href="{{ route('api-credentials.webhooks.show', $wh['id']) }}" class="text-[11px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400">{{ __('View') }}</a>
                            <a href="{{ route('api-credentials.webhooks.edit', $wh['id']) }}" class="text-[11px] font-semibold text-slate-500 hover:underline">{{ __('Edit') }}</a>
                        </div>
                        <button type="button" @click="open = !open" class="w-full px-4 py-4 text-left">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10px] font-bold text-indigo-600 dark:text-indigo-400">{{ $wh['id'] }}</span>
                                        <x-ui.status-badge :variant="$webhookStatusVariant($wh['status'])">{{ ucfirst($wh['status']) }}</x-ui.status-badge>
                                    </div>
                                    <p class="mt-1 truncate font-mono text-sm text-slate-800 dark:text-slate-200">{{ $wh['url'] }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($wh['events'] as $ev)
                                            <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $ev }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-right text-xs">
                                    <p class="font-bold text-emerald-600">{{ $wh['delivery_rate'] }}%</p>
                                    <p class="text-slate-500">{{ __('delivery rate') }}</p>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-4 gap-2 text-center text-[11px]">
                                <div><p class="font-bold text-slate-800 dark:text-white">{{ $wh['last_delivery'] }}</p><p class="text-slate-500">{{ __('Last') }}</p></div>
                                <div><p class="font-bold text-rose-600">{{ $wh['failures_24h'] }}</p><p class="text-slate-500">{{ __('Failures 24h') }}</p></div>
                                <div><p class="font-bold text-amber-600">{{ $wh['retries_pending'] }}</p><p class="text-slate-500">{{ __('Retry queue') }}</p></div>
                                <div><p class="font-bold text-slate-700 dark:text-slate-300">{{ $wh['signature'] }}</p><p class="text-slate-500">{{ __('Signature') }}</p></div>
                            </div>
                        </button>
                        <div x-show="open" x-transition class="border-t border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Delivery timeline') }}</p>
                            <ul class="space-y-2">
                                @forelse ($wh['timeline'] as $evt)
                                    <li class="flex items-center gap-3 text-xs">
                                        <span class="w-12 shrink-0 font-mono text-slate-400">{{ $evt['time'] }}</span>
                                        <span @class([
                                            'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase',
                                            'bg-emerald-500/15 text-emerald-700' => $evt['status'] === 'success',
                                            'bg-amber-500/15 text-amber-700' => $evt['status'] === 'retry',
                                            'bg-rose-500/15 text-rose-700' => $evt['status'] === 'failed',
                                        ])>{{ $evt['status'] }}</span>
                                        <span class="font-mono text-slate-600 dark:text-slate-400">{{ $evt['event'] }}</span>
                                        <span class="ml-auto font-mono text-slate-400">HTTP {{ $evt['code'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-500">{{ __('No recent deliveries') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="lg:col-span-5">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Payload log preview') }}</h3>
                    </div>
                    <pre class="max-h-64 overflow-auto p-4 font-mono text-[11px] leading-relaxed text-emerald-400 bg-slate-950">{
  "id": "evt_8f2a91",
  "type": "license.updated",
  "data": {
    "tenant_id": "tn_acme_001",
    "status": "active",
    "domain": "app.example.com"
  },
  "signature": "sha256=8a7f3c..."
}</pre>
                    <div class="border-t border-slate-200/80 p-3 dark:border-slate-800/80">
                        <p class="text-xs text-slate-500">{{ __('Signature validation') }}: <span class="font-semibold text-emerald-600">{{ __('HMAC-SHA256 verified') }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security tab --}}
        <div x-show="activeTab === 'security'" class="grid gap-5 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('IP whitelisting') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($security['ip_whitelist'] as $ip)
                            <li class="flex items-center justify-between px-4 py-3 text-sm">
                                <div>
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white">{{ $ip['ip'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $ip['label'] }}</p>
                                </div>
                                <span class="tabular-nums text-xs font-medium text-slate-600">{{ number_format($ip['hits']) }} {{ __('hits') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-slate-200/80 p-3 dark:border-slate-800/80">
                        <button type="button" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">+ {{ __('Add IP range') }}</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Access scopes') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($security['scopes'] as $scope)
                            <li class="flex items-center justify-between px-4 py-3">
                                <span class="font-mono text-sm text-slate-800 dark:text-slate-200">{{ $scope['scope'] }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ $scope['keys'] }} {{ __('keys') }}</span>
                                    <x-ui.status-badge :variant="$scope['risk'] === 'critical' ? 'danger' : ($scope['risk'] === 'high' ? 'warning' : 'neutral')">{{ ucfirst($scope['risk']) }}</x-ui.status-badge>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Rate limiting & throttling') }}</h3>
                    </div>
                    <div class="space-y-3 p-4">
                        @foreach ($security['rate_limits'] as $rl)
                            <div class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-700">
                                <div class="flex justify-between text-sm">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $rl['tier'] }}</span>
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $rl['limit'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-amber-600">{{ $rl['violations_today'] }} {{ __('violations today') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-amber-200/60 bg-amber-50/30 shadow-card dark:border-amber-500/20 dark:bg-amber-950/20">
                    <div class="border-b border-amber-200/50 px-4 py-3 dark:border-amber-500/20">
                        <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-200">{{ __('Security alerts') }}</h3>
                    </div>
                    <ul class="space-y-2 p-4">
                        @foreach ($security['alerts'] as $alert)
                            <li @class([
                                'rounded-xl border px-3 py-2 text-xs',
                                'border-rose-200/80 bg-rose-50/50 dark:border-rose-500/20 dark:bg-rose-950/30' => $alert['type'] === 'danger',
                                'border-amber-200/80 bg-white/80 dark:border-amber-500/20 dark:bg-slate-900/60' => $alert['type'] === 'warning',
                                'border-sky-200/80 bg-white/80 dark:border-sky-500/20 dark:bg-slate-900/60' => $alert['type'] === 'info',
                            ])>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                <p class="mt-0.5 text-slate-600 dark:text-slate-400">{{ $alert['body'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Token rotation reminders') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 p-2 dark:divide-slate-800/80">
                        @foreach ($security['rotation_reminders'] as $rem)
                            <li class="flex items-center justify-between rounded-lg px-2 py-2 text-xs">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $rem['key'] }}</span>
                                <span @class([
                                    'rounded-full px-2 py-0.5 font-bold',
                                    'bg-rose-500/15 text-rose-700' => $rem['days'] < 0,
                                    'bg-amber-500/15 text-amber-700' => $rem['days'] >= 0,
                                ])>{{ $rem['due'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Analytics tab --}}
        <div x-show="activeTab === 'analytics'" class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-8 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Request volume (24h)') }}</p>
                    <div class="flex h-36 items-end gap-2">
                        @foreach ($analytics['request_volume'] as $bar)
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <div
                                    class="w-full rounded-t bg-gradient-to-t from-indigo-600 to-violet-500"
                                    style="height: {{ max(12, ($bar['value'] / $volumeMax) * 100) }}%"
                                    title="{{ number_format($bar['value']) }}"
                                ></div>
                                <span class="text-[10px] text-slate-500">{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Response times') }}</p>
                        @foreach ($analytics['response_times'] as $rt)
                            <div class="mb-3 flex items-center justify-between">
                                <span class="font-mono text-sm font-bold text-slate-800 dark:text-white">{{ $rt['label'] }}</span>
                                <span class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">{{ $rt['ms'] }}ms</span>
                            </div>
                        @endforeach
                        <p class="mt-2 text-xs text-slate-500">{{ __('Error rate') }}: <span class="font-semibold text-rose-600">{{ $analytics['error_rate'] }}%</span></p>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Integration mix') }}</p>
                        @foreach ($analytics['integration_trend'] as $int)
                            <div class="mb-2">
                                <div class="mb-0.5 flex justify-between text-xs">
                                    <span>{{ $int['name'] }}</span>
                                    <span class="font-semibold">{{ $int['pct'] }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-violet-500" style="width: {{ $int['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Endpoint usage') }}</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        @foreach ($analytics['endpoints'] as $ep)
                            <div class="flex items-center gap-3">
                                <code class="w-48 shrink-0 truncate font-mono text-[11px] text-slate-700 dark:text-slate-300">{{ $ep['path'] }}</code>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $ep['pct'] }}%"></div>
                                </div>
                                <span class="w-16 text-right text-xs font-semibold tabular-nums">{{ number_format($ep['requests']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Top errors') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($analytics['error_endpoints'] as $err)
                            <li class="flex items-center justify-between px-4 py-3 text-xs">
                                <code class="font-mono text-slate-600 dark:text-slate-400">{{ $err['path'] }}</code>
                                <span class="font-bold text-rose-600">{{ $err['errors'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-slate-200/80 p-3 dark:border-slate-800/80">
                        <a href="{{ route('activity-logs.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('View logs') }} →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Developer tab --}}
        <div x-show="activeTab === 'developer'" class="grid gap-5 lg:grid-cols-12">
            <div class="lg:col-span-7 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex border-b border-slate-200/80 dark:border-slate-800/80">
                        @foreach (['curl' => 'cURL', 'php' => 'PHP', 'node' => 'Node.js'] as $lang => $label)
                            <button
                                type="button"
                                @click="snippetLang = '{{ $lang }}'"
                                :class="snippetLang === '{{ $lang }}' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-500'"
                                class="px-4 py-2.5 text-xs font-semibold"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="relative">
                        <pre class="overflow-x-auto p-4 font-mono text-[11px] leading-relaxed text-emerald-400 bg-slate-950" x-text="snippetText"></pre>
                        <button type="button" @click="copySnippet()" class="absolute right-3 top-3 rounded-lg bg-slate-800 px-2 py-1 text-[10px] font-semibold text-white hover:bg-slate-700" x-text="snippetCopied ? '{{ __('Copied') }}' : '{{ __('Copy') }}'"></button>
                    </div>
                    <p class="border-t border-slate-200/80 px-4 py-2 text-xs text-slate-500 dark:border-slate-800/80">{{ __('Base URL') }}: <code class="text-indigo-600">{{ $baseUrl }}</code></p>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('API reference') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($developer['endpoints'] as $ep)
                            <li class="flex items-start gap-3 px-4 py-3">
                                <span @class([
                                    'shrink-0 rounded px-1.5 py-0.5 font-mono text-[10px] font-bold',
                                    'bg-emerald-500/15 text-emerald-700' => $ep['method'] === 'GET',
                                    'bg-indigo-500/15 text-indigo-700' => $ep['method'] === 'POST',
                                ])>{{ $ep['method'] }}</span>
                                <div>
                                    <code class="font-mono text-sm text-slate-800 dark:text-slate-200">{{ $ep['path'] }}</code>
                                    <p class="text-xs text-slate-500">{{ $ep['desc'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="lg:col-span-5 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('SDKs') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach ($developer['sdks'] as $sdk)
                            <li class="px-4 py-3">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $sdk['name'] }}</span>
                                    <span class="text-xs text-slate-500">{{ $sdk['version'] }}</span>
                                </div>
                                <code class="mt-1 block font-mono text-[11px] text-indigo-600 dark:text-indigo-400">{{ $sdk['install'] }}</code>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-50 to-violet-50 p-4 shadow-card dark:border-indigo-500/20 dark:from-indigo-950/40 dark:to-violet-950/30">
                    <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">{{ __('API playground') }}</h3>
                    <p class="mt-1 text-xs text-indigo-800/80 dark:text-indigo-300/80">{{ __('Test endpoints with your selected API key in a sandbox environment.') }}</p>
                    <button type="button" class="mt-3 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-500/25 hover:brightness-110">{{ __('Open playground') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
