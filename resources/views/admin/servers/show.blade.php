@php
    $meta = is_array($server->provisioning_meta) ? $server->provisioning_meta : [];
    $showVal = fn ($value) => filled($value) ? $value : __('Not configured');

    $tabs = [
        'overview' => __('Overview'),
        'health' => __('Health'),
        'deployments' => __('Hosted deployments'),
        'notices' => __('Notices'),
        'billing' => __('Billing'),
        'activity' => __('Activity'),
        'advanced' => __('Advanced'),
    ];

    $statusVariant = match ($server->status) {
        'online' => 'success',
        'warning' => 'warning',
        'offline' => 'danger',
        default => 'neutral',
    };
    $renewalVariant = match ($server->renewalRisk()) {
        'overdue' => 'danger',
        'soon' => 'warning',
        default => 'neutral',
    };

    $readiness = [
        ['label' => __('Public IP provided'), 'done' => filled($server->ip_address)],
        ['label' => __('Hostname provided'), 'done' => filled($server->hostname())],
        ['label' => __('Telemetry mode selected'), 'done' => filled($server->telemetry_mode)],
    ];
    if ($server->telemetry_mode === 'whm') {
        $readiness[] = ['label' => __('WHM endpoint provided'), 'done' => filled($meta['api_endpoint'] ?? null)];
        $readiness[] = ['label' => __('API token provided'), 'done' => $server->hasWhmCredentials()];
    }
    $readiness[] = ['label' => __('Renewal date provided'), 'done' => $server->renewal_expires_at !== null];
    $readiness[] = ['label' => __('Monthly cost provided'), 'done' => filled($server->monthly_cost)];
@endphp

<x-dashboard-layout :heading="$server->name">
    <x-admin.risk-cards :risks="$operationalRisks" class="mb-6" :compact="true" />

    <div x-data="{ activeTab: window.location.hash === '#notices' ? 'notices' : 'overview' }" class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Server') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $showVal($server->provider) }}
                    @if ($server->hostname())
                        · <span class="font-mono">{{ $server->hostname() }}</span>
                    @endif
                    @if ($server->ip_address)
                        · <span class="font-mono">{{ $server->ip_address }}</span>
                    @endif
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-ui.status-badge :variant="$statusVariant">{{ ucfirst($server->status) }}</x-ui.status-badge>
                    <span class="inline-flex items-center rounded-full bg-slate-500/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 ring-1 ring-slate-500/20 dark:text-slate-300">
                        {{ $server->telemetryModeLabel() }}
                    </span>
                    @if ($server->telemetry_mode === 'whm' && ! $server->hasWhmCredentials())
                        <span class="inline-flex items-center rounded-full bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-amber-800 ring-1 ring-amber-500/20 dark:text-amber-200">
                            {{ __('Add WHM API token to enable live metrics') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($server->telemetry_mode !== 'manual')
                    <form method="post" action="{{ route('servers.sync-telemetry', $server) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-3 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-500/15 dark:text-cyan-200">
                            {{ __('Sync now') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('servers.edit', $server) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Edit') }}</a>
                <form method="post" action="{{ route('servers.destroy', $server) }}" onsubmit="return confirm(@json(__('Delete :name from the fleet?', ['name' => $server->name])));">
                    @csrf
                    @method('delete')
                    <button type="submit" class="inline-flex items-center rounded-xl border border-rose-200/80 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="flex min-w-max gap-1">
                @foreach ($tabs as $id => $label)
                    <button type="button" @click="activeTab = '{{ $id }}'" :class="activeTab === '{{ $id }}' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'" class="rounded-lg px-3 py-2 text-[11px] font-semibold whitespace-nowrap transition">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        {{-- Overview --}}
        <div x-show="activeTab === 'overview'" class="space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Summary') }}</h3>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    <div><dt class="text-slate-500">{{ __('Server name') }}</dt><dd class="mt-0.5 font-medium">{{ $showVal($server->name) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Provider') }}</dt><dd class="mt-0.5 font-medium">{{ $showVal($server->provider) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Public IP') }}</dt><dd class="mt-0.5 font-mono font-medium">{{ $showVal($server->ip_address) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Hostname') }}</dt><dd class="mt-0.5 font-mono font-medium">{{ $showVal($server->hostname()) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Environment') }}</dt><dd class="mt-0.5 font-medium capitalize">{{ $showVal($meta['environment'] ?? null) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="mt-0.5 font-medium capitalize">{{ $showVal($server->status) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Telemetry mode') }}</dt><dd class="mt-0.5 font-medium">{{ $server->telemetryModeLabel() }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Monthly cost') }}</dt><dd class="mt-0.5 font-medium tabular-nums">{{ filled($server->monthly_cost) ? $server->currency.' '.number_format((float) $server->monthly_cost, 2) : __('Not configured') }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Renewal date') }}</dt><dd class="mt-0.5 font-medium">{{ $server->renewal_expires_at?->format('M j, Y') ?? __('Not configured') }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('SSL status') }}</dt><dd class="mt-0.5 font-medium">{{ $showVal($server->ssl_status) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Backup status') }}</dt><dd class="mt-0.5 font-medium">{{ $showVal($server->backup_status) }}</dd></div>
                </dl>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Readiness checklist') }}</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach ($readiness as $item)
                            <li class="flex items-start gap-2 text-sm">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[10px] {{ $item['done'] ? 'bg-emerald-500/20 text-emerald-600' : 'bg-slate-200 text-slate-400 dark:bg-slate-700' }}">
                                    @if ($item['done']) ✓ @endif
                                </span>
                                <span class="{{ $item['done'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-500' }}">{{ $item['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Sync & capacity') }}</h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Last sync') }}</dt><dd class="font-medium">{{ $server->last_synced_at?->diffForHumans() ?? __('Never') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Disk usage') }}</dt><dd class="font-medium">{{ $server->disk_usage_percent !== null ? $server->disk_usage_percent.'%' : __('Not configured') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('RAM usage') }}</dt><dd class="font-medium">{{ $server->displayRamPercent() !== null ? $server->displayRamPercent().'%' : __('Not configured') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Load avg') }}</dt><dd class="font-medium">{{ $server->displayLoad() ?? __('Not configured') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Accounts') }}</dt><dd class="font-medium">{{ $server->account_count ?? __('Not configured') }}</dd></div>
                    </dl>
                    @if ($server->sync_message)
                        <p class="mt-3 text-xs text-slate-500">{{ $server->sync_message }}</p>
                    @endif
                </div>
            </div>

            <x-admin.assigned-staff :assignments="$staffAssignments" :title="__('Responsible staff')" />
        </div>

        {{-- Health --}}
        <div x-show="activeTab === 'health'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs text-slate-500">{{ __('Last checked') }}: {{ $server->meta('last_health_checked_at') ? \Illuminate\Support\Carbon::parse($server->meta('last_health_checked_at'))->diffForHumans() : __('Never') }}</p>
            <ul class="mt-4 space-y-2 text-sm">
                @php
                    $checkLabels = [
                        'port_443' => __('HTTPS (443)'),
                        'port_80' => __('HTTP (80)'),
                        'port_22' => __('SSH (22)'),
                        'port_2087' => __('WHM (2087)'),
                        'dns_resolves' => __('DNS resolves'),
                        'dns_matches_ip' => __('DNS matches IP'),
                        'ssl_reachable' => __('SSL endpoint'),
                        'whm_api' => __('WHM API'),
                    ];
                @endphp
                @forelse ($checkLabels as $key => $label)
                    @if (array_key_exists($key, $healthChecks))
                        <li class="flex items-center justify-between gap-4 rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/50">
                            <span>{{ $label }}</span>
                            <span class="font-semibold {{ ($healthChecks[$key] ?? false) ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ ($healthChecks[$key] ?? false) ? __('Pass') : __('Fail') }}
                            </span>
                        </li>
                    @endif
                @empty
                @endforelse
                @if ($healthChecks === [])
                    <li class="text-slate-500">{{ __('Run sync to populate health checks (requires IP or hostname).') }}</li>
                @endif
            </ul>
            @if ($server->latestHealthLog)
                <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500 dark:border-slate-800">
                    {{ __('Latest metrics log') }}: CPU {{ $server->latestHealthLog->cpu_percent ?? '—' }}% · RAM {{ $server->latestHealthLog->ram_percent ?? '—' }}% · {{ $server->latestHealthLog->checked_at->diffForHumans() }}
                </p>
            @endif
        </div>

        {{-- Hosted deployments --}}
        <div x-show="activeTab === 'deployments'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tenant project deployments') }}</h3>
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($server->tenantProjectDeployments as $deployment)
                        @php $sub = $deployment->subscription; @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                            <div>
                                <a href="{{ route('tenants.show', $sub?->tenant) }}" class="font-medium text-indigo-600 dark:text-indigo-400">{{ $sub?->tenant?->company_name ?? __('Unknown tenant') }}</a>
                                <span class="text-slate-500">· {{ $sub?->project?->name }}</span>
                                @if ($deployment->domain)
                                    <p class="text-xs text-slate-500 font-mono">{{ $deployment->domain }}</p>
                                @endif
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <p>{{ __('Version') }}: {{ $sub?->versionTracking?->current_version ?? __('Unknown') }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-slate-500">{{ __('No tenant project deployments on this server.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold">{{ __('Projects & domains') }}</h3>
                <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($server->projects as $project)
                        <li class="py-2 text-sm">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 dark:text-indigo-400">{{ $project->name }}</a>
                            <span class="text-slate-500">· {{ $project->domain }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('No projects linked.') }}</li>
                    @endforelse
                </ul>
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($server->hosted_domains ?? [] as $domain)
                        <li class="py-2 font-mono text-sm">{{ $domain }}</li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('No hosted domains recorded.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Notices --}}
        <div x-show="activeTab === 'notices'" x-cloak id="notices" class="space-y-6">
            @include('admin.servers.partials.show.notice-form', ['server' => $server])
            <ul class="divide-y divide-slate-100 rounded-2xl border border-slate-200/80 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                @forelse ($server->providerNotices as $notice)
                    <li class="px-4 py-4 text-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $notice->title }}</span>
                                <span class="ml-2 text-[10px] uppercase text-slate-400">{{ $notice->notice_type }} · {{ $notice->severity }}</span>
                            </div>
                            <x-ui.status-badge :variant="$notice->status === 'open' ? 'warning' : 'neutral'">{{ ucfirst($notice->status) }}</x-ui.status-badge>
                        </div>
                        @if ($notice->body)
                            <p class="mt-2 text-slate-600 dark:text-slate-300">{{ $notice->body }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-500">{{ $notice->notice_date->format('M j, Y') }}</p>
                        <form method="post" action="{{ route('servers.notices.destroy', [$server, $notice]) }}" class="mt-2 inline" onsubmit="return confirm(@json(__('Remove notice?')))">
                            @csrf @method('delete')
                            <button type="submit" class="text-xs text-rose-600 hover:underline">{{ __('Remove') }}</button>
                        </form>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500">{{ __('No provider notices yet.') }}</li>
                @endforelse
            </ul>
        </div>

        {{-- Billing --}}
        <div x-show="activeTab === 'billing'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Monthly cost') }}</dt><dd class="font-medium">{{ filled($server->monthly_cost) ? $server->currency.' '.number_format((float) $server->monthly_cost, 2) : __('Not configured') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Renewal / expiry') }}</dt><dd class="font-medium">
                    {{ $server->renewal_expires_at?->toFormattedDateString() ?? __('Not configured') }}
                    @if ($server->renewal_expires_at)
                        <x-ui.status-badge :variant="$renewalVariant" class="ml-2">{{ ucfirst($server->renewalRisk()) }}</x-ui.status-badge>
                    @endif
                </dd></div>
                <div><dt class="text-slate-500">{{ __('Billing status') }}</dt><dd class="font-medium">{{ $showVal($server->billing_status) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Billing cycle') }}</dt><dd class="font-medium">{{ $showVal($meta['billing_cycle'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Provider invoice ref') }}</dt><dd class="font-medium">{{ $showVal($meta['provider_invoice_ref'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Billing notes') }}</dt><dd class="whitespace-pre-wrap">{{ $showVal($meta['billing_notes'] ?? null) }}</dd></div>
            </dl>
        </div>

        {{-- Activity --}}
        <div x-show="activeTab === 'activity'" x-cloak>
            <x-admin.activity-feed :logs="$activityLogs" />
        </div>

        {{-- Advanced --}}
        <div x-show="activeTab === 'advanced'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Private IP') }}</dt><dd class="mt-0.5 font-mono">{{ $showVal($meta['private_ip'] ?? null) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Region / zone') }}</dt><dd class="mt-0.5">{{ $showVal($meta['region'] ?? null) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Cloud instance ID') }}</dt><dd class="mt-0.5 font-mono">{{ $showVal($meta['cloud_instance_id'] ?? null) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('OS') }}</dt><dd class="mt-0.5">{{ $showVal($meta['operating_system'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('API endpoint') }}</dt><dd class="mt-0.5 font-mono text-xs">{{ $showVal($meta['api_endpoint'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('WHM reference') }}</dt><dd class="mt-0.5 whitespace-pre-wrap">{{ $showVal($server->whm_cpanel_reference) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('SSH') }}</dt><dd class="font-mono">{{ ($meta['ssh_username'] ?? '—') }}@{{ $server->ip_address ?? $server->hostname() ?? '—' }}:{{ $meta['ssh_port'] ?? 22 }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Firewall') }}</dt><dd>{{ $showVal($meta['firewall_status'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Access restrictions') }}</dt><dd class="whitespace-pre-wrap">{{ $showVal($meta['access_restrictions'] ?? null) }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Notes') }}</dt><dd class="mt-1 whitespace-pre-wrap">{{ $showVal($server->notes) }}</dd></div>
            </dl>
            <p class="mt-4 text-xs text-slate-500">{{ __('API tokens are never displayed after saving.') }}</p>
        </div>
    </div>
</x-dashboard-layout>
