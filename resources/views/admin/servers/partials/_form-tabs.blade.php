@php
    use App\Domain\Servers\Support\ServerConnectionConfig;

    $meta = old('meta', is_array($server->provisioning_meta ?? null) ? $server->provisioning_meta : []);
    $domainsText = old('hosted_domains_text', ($server->exists ?? false) ? implode("\n", $server->hosted_domains ?? []) : '');
    $hasToken = ($server->exists ?? false) && $server->hasStoredApiToken();
    $hasHostingerToken = filled($meta['hostinger_api_token'] ?? null);
    $isEdit = $server->exists ?? false;

    $display = fn ($value) => filled($value) ? $value : __('Not configured');

    $providers = [
        ['label' => 'Hostinger', 'color' => 'text-violet-600'],
        ['label' => 'cPanel/WHM', 'color' => 'text-orange-600'],
        ['label' => 'Hetzner', 'color' => 'text-red-600'],
        ['label' => 'DigitalOcean', 'color' => 'text-sky-600'],
        ['label' => 'AWS', 'color' => 'text-amber-600'],
        ['label' => 'Azure', 'color' => 'text-blue-600'],
        ['label' => 'Linode', 'color' => 'text-emerald-600'],
        ['label' => 'Vultr', 'color' => 'text-cyan-600'],
    ];

    $formTabs = [
        'overview' => __('Overview'),
        'identity' => __('Identity'),
        'capacity' => __('Capacity'),
        'connectivity' => __('Connectivity'),
        'security' => __('Security & Backups'),
        'billing' => __('Billing'),
        'deployment' => __('Deployment'),
        'advanced' => __('Advanced'),
    ];

    $telemetryModes = [
        'manual' => __('Manual monitoring'),
        'basic' => __('Basic checks (reachability + SSL)'),
        'whm' => __('WHM live metrics'),
    ];

    $initialForm = [
        'name' => old('name', $server->name ?? ''),
        'hostname' => old('meta.hostname', $meta['hostname'] ?? ''),
        'provider' => old('provider', $server->provider ?? 'Hostinger'),
        'ip_address' => old('ip_address', $server->ip_address ?? ''),
        'cpu_cores' => old('cpu_cores', $server->cpu_cores ?? ''),
        'ram_gb' => old('ram_gb', $server->ram_gb ?? ''),
        'storage_gb' => old('storage_gb', $server->storage_gb ?? ''),
        'disk_usage_percent' => old('disk_usage_percent', $server->disk_usage_percent ?? ''),
        'status' => old('status', $server->status ?? 'unknown'),
        'telemetry_mode' => old('telemetry_mode', $server->telemetry_mode ?? 'whm'),
        'ssl_status' => old('ssl_status', $server->ssl_status ?? ''),
        'backup_status' => old('backup_status', $server->backup_status ?? ''),
        'monthly_cost' => old('monthly_cost', $server->monthly_cost ?? ''),
        'currency' => old('currency', $server->currency ?? 'KES'),
        'renewal_expires_at' => old('renewal_expires_at', optional($server->renewal_expires_at ?? null)->format('Y-m-d')),
    ];
@endphp

<div
    x-data="serverForm(@js($initialForm), @js(old('meta.environment', $meta['environment'] ?? 'production')), @js($isEdit), @js($hasToken))"
    x-init="provider = form.provider || 'Hostinger';"
    class="space-y-5"
>
    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-900/50">
        <div class="flex min-w-max gap-1">
            @foreach ($formTabs as $tabId => $tabLabel)
                <button
                    type="button"
                    @click="activeTab = '{{ $tabId }}'"
                    :class="activeTab === '{{ $tabId }}' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'"
                    class="rounded-lg px-3 py-2 text-[11px] font-semibold whitespace-nowrap transition"
                >{{ $tabLabel }}</button>
            @endforeach
        </div>
    </div>

    {{-- Overview --}}
    <div x-show="activeTab === 'overview'" class="space-y-5">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Server summary') }}</h3>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Server name') }}</dt><dd class="mt-0.5 font-medium" x-text="summaryValue(form.name)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Provider') }}</dt><dd class="mt-0.5 font-medium" x-text="summaryValue(provider || form.provider)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Public IP') }}</dt><dd class="mt-0.5 font-mono font-medium" x-text="summaryValue(form.ip_address)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Hostname') }}</dt><dd class="mt-0.5 font-mono font-medium" x-text="summaryValue(form.hostname)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Environment') }}</dt><dd class="mt-0.5 font-medium capitalize" x-text="summaryValue(environment)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="mt-0.5 font-medium capitalize" x-text="summaryValue(form.status)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Telemetry mode') }}</dt><dd class="mt-0.5 font-medium" x-text="telemetryLabel(form.telemetry_mode)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Monthly cost') }}</dt><dd class="mt-0.5 font-medium tabular-nums"><span x-text="form.currency || 'KES'"></span> <span x-text="summaryValue(form.monthly_cost)"></span></dd></div>
                <div><dt class="text-slate-500">{{ __('Renewal date') }}</dt><dd class="mt-0.5 font-medium" x-text="summaryValue(form.renewal_expires_at)"></dd></div>
                <div><dt class="text-slate-500">{{ __('SSL status') }}</dt><dd class="mt-0.5 font-medium" x-text="summaryValue(form.ssl_status)"></dd></div>
                <div><dt class="text-slate-500">{{ __('Backup status') }}</dt><dd class="mt-0.5 font-medium" x-text="summaryValue(form.backup_status)"></dd></div>
            </dl>
            @if ($isEdit)
                <p class="mt-4 text-xs text-slate-500">{{ __('Last synced') }}: {{ $server->last_synced_at?->diffForHumans() ?? __('Never') }}</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Readiness checklist') }}</h3>
            <ul class="mt-4 space-y-2">
                <template x-for="item in readinessChecklist" :key="item.label">
                    <li class="flex items-start gap-2 text-sm">
                        <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[10px]" :class="item.done ? 'bg-emerald-500/20 text-emerald-600' : 'bg-slate-200 text-slate-400 dark:bg-slate-700'">
                            <span x-show="item.done">✓</span>
                        </span>
                        <span :class="item.done ? 'text-slate-800 dark:text-slate-200' : 'text-slate-500'" x-text="item.label"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- Identity --}}
    <div x-show="activeTab === 'identity'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($providers as $p)
                <button
                    type="button"
                    @click="selectProvider('{{ $p['label'] }}')"
                    :class="provider === '{{ $p['label'] }}' ? 'infra-provider-chip-active' : ''"
                    class="infra-provider-chip"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-current {{ $p['color'] }}"></span>
                    {{ $p['label'] }}
                </button>
            @endforeach
        </div>
        <input type="hidden" name="provider" x-model="form.provider" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Server name')" name="name" :required="true" model="form.name" />
            <div>
                <x-admin.infra-field :label="__('Hostname (FQDN)')" name="meta[hostname]" model="form.hostname" placeholder="node-01.example.com" />
            </div>
            <x-admin.infra-field :label="__('Public IP')" name="ip_address" model="form.ip_address" placeholder="203.0.113.10" />
            <div>
                <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Environment') }} <span class="text-rose-500">*</span></label>
                <select name="meta[environment]" x-model="environment" class="infra-provision-select mt-1.5" required>
                    <option value="production">{{ __('Production') }}</option>
                    <option value="staging">{{ __('Staging') }}</option>
                    <option value="development">{{ __('Development') }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Telemetry mode') }} <span class="text-rose-500">*</span></label>
                <select name="telemetry_mode" x-model="form.telemetry_mode" class="infra-provision-select mt-1.5" required>
                    @foreach ($telemetryModes as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.infra-field :label="__('WHM / cPanel reference')" name="whm_cpanel_reference" type="textarea" :rows="2" :value="old('whm_cpanel_reference', $server->whm_cpanel_reference ?? '')" :hint="__('Panel URL or account reference')" class="sm:col-span-2" />
        </div>
    </div>

    {{-- Capacity --}}
    <div x-show="activeTab === 'capacity'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('CPU cores')" name="cpu_cores" type="number" :min="0" model="form.cpu_cores" />
            <x-admin.infra-field :label="__('RAM (GB)')" name="ram_gb" type="number" step="0.01" :min="0" model="form.ram_gb" />
            <x-admin.infra-field :label="__('Storage (GB)')" name="storage_gb" type="number" step="0.01" :min="0" model="form.storage_gb" />
            <x-admin.infra-field :label="__('Disk usage %')" name="disk_usage_percent" type="number" step="0.01" :min="0" :max="100" model="form.disk_usage_percent" />
            <x-admin.infra-field :label="__('Bandwidth quota')" name="meta[bandwidth_gbps]" type="number" step="0.1" :value="$meta['bandwidth_gbps'] ?? ''" :hint="__('Monthly or port cap (Gbps)')" />
            <x-admin.infra-field :label="__('Bandwidth used')" name="meta[bandwidth_used]" type="number" step="0.01" :value="$meta['bandwidth_used'] ?? ''" :hint="__('Current period usage (GB or %)')" />
            <div>
                <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Server status') }}</label>
                <select name="status" x-model="form.status" class="infra-provision-select mt-1.5" required>
                    @foreach (['online', 'offline', 'warning', 'unknown'] as $st)
                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Connectivity --}}
    <div x-show="activeTab === 'connectivity'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <p class="mb-4 rounded-lg border border-indigo-500/20 bg-indigo-500/5 px-3 py-2 text-xs text-slate-600 dark:text-slate-300">
            {{ __('Use WHM API token for live telemetry. Hostinger API token is optional and should be stored only if provider automation is later enabled.') }}
        </p>
        <div class="mb-4 flex justify-end">
            <button type="button" @click="testConnection()" :disabled="testingConnection" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 disabled:opacity-50 dark:text-emerald-300">
                <span x-text="testingConnection ? '{{ __('Testing…') }}' : '{{ __('Test connection') }}'"></span>
            </button>
        </div>
        <template x-if="connectionStatus === 'ok'">
            <div class="mb-4 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-800 dark:text-emerald-200" x-text="probeMessages.join(' · ')"></div>
        </template>
        <template x-if="connectionStatus === 'fail'">
            <div class="mb-4 rounded-xl border border-rose-500/25 bg-rose-500/10 px-3 py-2 text-xs text-rose-800 dark:text-rose-200" x-text="probeMessages.join(' · ')"></div>
        </template>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('SSH port')" name="meta[ssh_port]" type="number" :value="$meta['ssh_port'] ?? '22'" />
            <x-admin.infra-field :label="__('SSH username')" name="meta[ssh_username]" :value="$meta['ssh_username'] ?? ''" placeholder="root" />
            <x-admin.infra-field :label="__('WHM / API endpoint')" name="meta[api_endpoint]" :value="$meta['api_endpoint'] ?? ''" placeholder="https://hostname:2087" class="sm:col-span-2" :hint="__('Required when telemetry mode is WHM')" />
            <x-admin.infra-field :label="__('WHM username')" name="meta[whm_username]" :value="$meta['whm_username'] ?? 'root'" />
            <x-admin.infra-field :label="__('WHM port')" name="meta[whm_port]" type="number" :value="$meta['whm_port'] ?? '2087'" />
            <x-admin.infra-field :label="__('Authentication method')" name="meta[auth_method]" type="select" :value="$meta['auth_method'] ?? 'api_token'">
                <option value="api_token">{{ __('API token') }}</option>
                <option value="ssh_key">{{ __('SSH key') }}</option>
            </x-admin.infra-field>
            <div>
                <label for="meta-api-token" class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('API token') }}</label>
                <input
                    id="meta-api-token"
                    name="meta[api_token]"
                    type="password"
                    autocomplete="new-password"
                    placeholder="{{ $hasToken ? ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER : '' }}"
                    class="infra-provision-input mt-1.5 font-mono"
                />
                @if ($hasToken)
                    <p class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400">{{ __('Token saved. Enter a new value only to replace it.') }}</p>
                @endif
            </div>
            <x-admin.infra-field :label="__('Access restrictions / allowed IPs')" name="meta[access_restrictions]" type="textarea" :rows="2" :value="$meta['access_restrictions'] ?? ''" class="sm:col-span-2" />
        </div>
    </div>

    {{-- Security & Backups --}}
    <div x-show="activeTab === 'security'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('SSL status')" name="ssl_status" model="form.ssl_status" placeholder="Valid, expiring, missing…" />
            <x-admin.infra-field :label="__('Certificate expiry')" name="meta[certificate_expiry]" type="date" :value="$meta['certificate_expiry'] ?? ''" />
            <x-admin.infra-field :label="__('Backup status')" name="backup_status" model="form.backup_status" />
            <x-admin.infra-field :label="__('Backup policy')" name="meta[backup_policy]" :value="$meta['backup_policy'] ?? ''" placeholder="Daily, weekly…" />
            <x-admin.infra-field :label="__('Last backup date')" name="meta[last_backup_date]" type="date" :value="$meta['last_backup_date'] ?? ''" />
            <x-admin.infra-field :label="__('Firewall status')" name="meta[firewall_status]" :value="$meta['firewall_status'] ?? ''" />
            <x-admin.infra-field :label="__('Monitoring enabled')" name="meta[monitoring_enabled]" type="select" :value="$meta['monitoring_enabled'] ?? '1'">
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </x-admin.infra-field>
        </div>
    </div>

    {{-- Billing --}}
    <div x-show="activeTab === 'billing'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Currency (ISO)')" name="currency" :maxlength="3" model="form.currency" :required="true" />
            <x-admin.infra-field :label="__('Monthly cost')" name="monthly_cost" type="number" step="0.01" model="form.monthly_cost" />
            <x-admin.infra-field :label="__('Renewal / expiry date')" name="renewal_expires_at" type="date" model="form.renewal_expires_at" />
            <x-admin.infra-field :label="__('Billing cycle')" name="meta[billing_cycle]" type="select" :value="$meta['billing_cycle'] ?? 'monthly'">
                <option value="monthly">{{ __('Monthly') }}</option>
                <option value="annual">{{ __('Annual') }}</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('Provider invoice / reference')" name="meta[provider_invoice_ref]" :value="$meta['provider_invoice_ref'] ?? ''" class="sm:col-span-2" />
            <x-admin.infra-field :label="__('Billing notes')" name="meta[billing_notes]" type="textarea" :rows="2" :value="$meta['billing_notes'] ?? ''" class="sm:col-span-2" />
        </div>
    </div>

    {{-- Deployment --}}
    <div x-show="activeTab === 'deployment'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Deployment strategy')" name="meta[deployment_strategy]" type="select" :value="$meta['deployment_strategy'] ?? 'rolling'">
                <option value="rolling">{{ __('Rolling') }}</option>
                <option value="blue_green">{{ __('Blue / green') }}</option>
                <option value="canary">{{ __('Canary') }}</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('Auto backups enabled')" name="meta[auto_backups]" type="select" :value="$meta['auto_backups'] ?? '1'">
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('Rollback enabled')" name="meta[rollback_enabled]" type="select" :value="$meta['rollback_enabled'] ?? '1'">
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('CI/CD enabled')" name="meta[ci_cd_enabled]" type="select" :value="$meta['ci_cd_enabled'] ?? '0'">
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('Notes')" name="notes" type="textarea" :rows="3" :value="old('notes', $server->notes ?? '')" class="sm:col-span-2" />
        </div>
    </div>

    {{-- Advanced --}}
    <div x-show="activeTab === 'advanced'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <p class="mb-4 text-xs text-slate-500">{{ __('Optional fields for multi-cloud automation, internal networking, and provider APIs.') }}</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Private IP')" name="meta[private_ip]" :value="$meta['private_ip'] ?? ''" />
            <x-admin.infra-field :label="__('Region / zone')" name="meta[region]" :value="$meta['region'] ?? ''" />
            <x-admin.infra-field :label="__('Cloud instance ID')" name="meta[cloud_instance_id]" :value="$meta['cloud_instance_id'] ?? ''" />
            <x-admin.infra-field :label="__('Provider account reference')" name="meta[provider_account_ref]" :value="$meta['provider_account_ref'] ?? ''" />
            <x-admin.infra-field :label="__('Operating system')" name="meta[operating_system]" :value="$meta['operating_system'] ?? ''" />
            <x-admin.infra-field :label="__('Architecture')" name="meta[architecture]" type="select" :value="$meta['architecture'] ?? ''">
                <option value="">{{ __('Select…') }}</option>
                <option value="x86_64">x86_64</option>
                <option value="arm64">arm64</option>
            </x-admin.infra-field>
            <x-admin.infra-field :label="__('Network speed')" name="meta[network_speed]" :value="$meta['network_speed'] ?? ''" />
            <x-admin.infra-field :label="__('WAF enabled')" name="meta[waf_enabled]" type="select" :value="$meta['waf_enabled'] ?? ''">
                <option value="">{{ __('Unknown') }}</option>
                <option value="1">{{ __('Yes') }}</option>
                <option value="0">{{ __('No') }}</option>
            </x-admin.infra-field>
            <div>
                <label for="meta-hostinger-token" class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Hostinger API token') }}</label>
                <input
                    id="meta-hostinger-token"
                    name="meta[hostinger_api_token]"
                    type="password"
                    autocomplete="new-password"
                    placeholder="{{ $hasHostingerToken ? ServerConnectionConfig::MASKED_TOKEN_PLACEHOLDER : '' }}"
                    class="infra-provision-input mt-1.5 font-mono"
                />
                <p class="mt-1 text-[11px] text-slate-500">{{ __('Optional — for future Hostinger automation only.') }}</p>
            </div>
            <x-admin.infra-field :label="__('Provider API token (other)')" name="meta[provider_api_token]" type="password" :masked="true" :value="''" :hint="__('Leave blank to keep existing')" />
            <x-admin.infra-field :label="__('Billing status')" name="billing_status" :value="old('billing_status', $server->billing_status ?? '')" />
            <x-admin.infra-field :label="__('Monthly revenue (allocated)')" name="monthly_revenue" type="number" step="0.01" :value="old('monthly_revenue', $server->monthly_revenue ?? '')" />
            <x-admin.infra-field :label="__('Hosted domains (one per line)')" name="hosted_domains_text" type="textarea" :rows="3" :value="$domainsText" class="sm:col-span-2" />
        </div>
    </div>

    @isset($submitLabel)
        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
            <button type="submit" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                {{ $submitLabel }}
            </button>
            <button type="button" @click="testConnection()" class="inline-flex min-h-[44px] items-center rounded-xl border border-slate-200/80 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                {{ __('Test connection') }}
            </button>
            @if ($isEdit)
                <a href="{{ route('servers.show', $server) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            @else
                <a href="{{ route('servers.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            @endif
        </div>
    @endisset
</div>
