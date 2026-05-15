@php
    $meta = old('meta', []);
    $domainsText = old('hosted_domains_text', '');
    $providers = [
        ['id' => 'aws', 'label' => 'AWS', 'color' => 'text-amber-600'],
        ['id' => 'digitalocean', 'label' => 'DigitalOcean', 'color' => 'text-sky-600'],
        ['id' => 'hetzner', 'label' => 'Hetzner', 'color' => 'text-red-600'],
        ['id' => 'azure', 'label' => 'Azure', 'color' => 'text-blue-600'],
        ['id' => 'hostinger', 'label' => 'Hostinger', 'color' => 'text-violet-600'],
        ['id' => 'linode', 'label' => 'Linode', 'color' => 'text-emerald-600'],
        ['id' => 'vultr', 'label' => 'Vultr', 'color' => 'text-cyan-600'],
        ['id' => 'cpanel', 'label' => 'cPanel/WHM', 'color' => 'text-orange-600'],
    ];
    $envBadges = [
        'production' => 'bg-rose-500/15 text-rose-700 ring-rose-500/25 dark:text-rose-300',
        'staging' => 'bg-amber-500/15 text-amber-800 ring-amber-500/25 dark:text-amber-200',
        'development' => 'bg-sky-500/15 text-sky-800 ring-sky-500/25 dark:text-sky-200',
    ];
@endphp

{{-- Section 1: Identity --}}
<section id="infra-section-identity" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 0 1 3.75 12V5.25a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25V12a2.25 2.25 0 0 1-2.25 2.25m-13.5 0h13.5" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Server identity') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Node naming, provider placement, and control-plane references') }}</p>
            </div>
        </div>
        <span class="rounded-full bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600 ring-1 ring-indigo-500/20 dark:text-indigo-300">{{ __('Step 1') }}</span>
    </div>
    <div class="space-y-5 p-4 sm:p-5">
        <div class="flex flex-wrap gap-2">
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

        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Server name')" name="name" :required="true" placeholder="prod-api-01" model="form.name" />
            <div>
                <x-admin.infra-field
                    :label="__('Hostname (FQDN)')"
                    name="meta[hostname]"
                    placeholder="node-01.pradytec.cloud"
                    :value="$meta['hostname'] ?? ''"
                    model="form.hostname"
                    x-on:input.debounce.400ms="verifyHostname()"
                />
                <p class="mt-1 flex items-center gap-1.5 text-[11px]" x-show="hostnameStatus" x-cloak>
                    <span x-show="hostnameStatus === 'valid'" class="text-emerald-600 dark:text-emerald-400">✓ {{ __('Hostname format verified') }}</span>
                    <span x-show="hostnameStatus === 'invalid'" class="text-rose-600 dark:text-rose-400">✗ {{ __('Invalid FQDN pattern') }}</span>
                </p>
            </div>
            <input type="hidden" name="provider" x-model="form.provider" />
            <x-admin.infra-field :label="__('Public IP')" name="ip_address" placeholder="203.0.113.10" model="form.ip_address" />
            <x-admin.infra-field :label="__('Private IP')" name="meta[private_ip]" placeholder="10.0.1.42" :value="$meta['private_ip'] ?? ''" />
            <x-admin.infra-field :label="__('Region / zone')" name="meta[region]" placeholder="af-south-1" :value="$meta['region'] ?? ''" />
            <div>
                <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Environment') }}</label>
                <select name="meta[environment]" x-model="environment" class="infra-provision-select mt-1.5">
                    <option value="production">{{ __('Production') }}</option>
                    <option value="staging">{{ __('Staging') }}</option>
                    <option value="development">{{ __('Development') }}</option>
                </select>
                <p class="mt-2">
                    <span
                        class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1"
                        :class="{
                            'bg-rose-500/15 text-rose-700 ring-rose-500/25 dark:text-rose-300': environment === 'production',
                            'bg-amber-500/15 text-amber-800 ring-amber-500/25 dark:text-amber-200': environment === 'staging',
                            'bg-sky-500/15 text-sky-800 ring-sky-500/25 dark:text-sky-200': environment === 'development',
                        }"
                        x-text="environment"
                    ></span>
                </p>
            </div>
            <div class="sm:col-span-2">
                <x-admin.infra-field :label="__('WHM / cPanel reference')" name="whm_cpanel_reference" type="textarea" :rows="2" :hint="__('Panel URL, account ID, or reseller reference')" />
            </div>
        </div>
    </div>
</section>

{{-- Section 2: Capacity --}}
<section id="infra-section-capacity" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-lg shadow-cyan-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Infrastructure capacity') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Compute, storage, and runtime profile') }}</p>
            </div>
        </div>
        <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-cyan-700 ring-1 ring-cyan-500/20 dark:text-cyan-300">{{ __('Step 2') }}</span>
    </div>
    <div class="space-y-5 p-4 sm:p-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.infra-field :label="__('CPU cores')" name="cpu_cores" type="number" :min="0" model="form.cpu_cores" />
            <x-admin.infra-field :label="__('RAM (GB)')" name="ram_gb" type="number" step="0.01" :min="0" model="form.ram_gb" />
            <x-admin.infra-field :label="__('Storage (GB)')" name="storage_gb" type="number" step="0.01" :min="0" model="form.storage_gb" />
            <x-admin.infra-field :label="__('Disk usage %')" name="disk_usage_percent" type="number" step="0.01" :min="0" :max="100" model="form.disk_usage_percent" />
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500"><span>{{ __('CPU allocation') }}</span><span x-text="(form.cpu_cores || 0) + ' cores'" class="text-cyan-600 dark:text-cyan-400"></span></div>
                <div class="infra-provision-meter"><div class="infra-provision-meter-fill" :style="'width:' + Math.min(100, (form.cpu_cores || 0) * 8) + '%'"></div></div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500"><span>{{ __('RAM pressure') }}</span><span x-text="(form.ram_gb || 0) + ' GB'" class="text-violet-600 dark:text-violet-400"></span></div>
                <div class="infra-provision-meter"><div class="infra-provision-meter-fill bg-gradient-to-r from-violet-500 to-fuchsia-500" :style="'width:' + Math.min(100, (form.ram_gb || 0) * 4) + '%'"></div></div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-[11px] font-semibold text-slate-500"><span>{{ __('Disk utilization') }}</span><span x-text="(form.disk_usage_percent || 0) + '%'" class="text-amber-600 dark:text-amber-400"></span></div>
                <div class="infra-provision-meter"><div class="infra-provision-meter-fill bg-gradient-to-r from-amber-500 to-orange-500" :style="'width:' + Math.min(100, form.disk_usage_percent || 0) + '%'"></div></div>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('Bandwidth (Gbps)')" name="meta[bandwidth_gbps]" type="number" step="0.1" :value="$meta['bandwidth_gbps'] ?? ''" placeholder="1" />
            <x-admin.infra-field :label="__('Network speed')" name="meta[network_speed]" :value="$meta['network_speed'] ?? ''" placeholder="10 Gbps uplink" />
            <x-admin.infra-field :label="__('Operating system')" name="meta[operating_system]" :value="$meta['operating_system'] ?? ''" placeholder="Ubuntu 24.04 LTS" />
            <x-admin.infra-field :label="__('Architecture')" name="meta[architecture]" type="select" :value="$meta['architecture'] ?? ''">
                <option value="">{{ __('Select…') }}</option>
                <option value="x86_64" @selected(($meta['architecture'] ?? '') === 'x86_64')>x86_64</option>
                <option value="arm64" @selected(($meta['architecture'] ?? '') === 'arm64')>arm64</option>
            </x-admin.infra-field>
            <div>
                <x-admin.infra-field :label="__('Operational status')" name="status" type="select" model="form.status">
                    <option value="online">{{ __('Online') }}</option>
                    <option value="offline">{{ __('Offline') }}</option>
                    <option value="unknown" @selected(old('status', 'unknown') === 'unknown')>{{ __('Unknown') }}</option>
                </x-admin.infra-field>
            </div>
        </div>
    </div>
</section>

{{-- Section 3: Connectivity --}}
<section id="infra-section-connectivity" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Connectivity & access') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('SSH, API endpoints, and perimeter controls') }}</p>
            </div>
        </div>
        <button type="button" @click="testConnection()" :disabled="testingConnection" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-500/20 disabled:opacity-50 dark:text-emerald-300">
            <svg class="h-3.5 w-3.5" :class="testingConnection && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
            <span x-text="testingConnection ? '{{ __('Testing…') }}' : '{{ __('Test connection') }}'"></span>
        </button>
    </div>
    <div class="space-y-4 p-4 sm:p-5">
        <template x-if="connectionStatus === 'ok'">
            <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-800 dark:text-emerald-200">
                <p class="font-semibold">✓ {{ __('Live probe succeeded') }}</p>
                <ul class="mt-1 list-inside list-disc text-[11px]" x-show="probeMessages.length">
                    <template x-for="msg in probeMessages" :key="msg"><li x-text="msg"></li></template>
                </ul>
            </div>
        </template>
        <template x-if="connectionStatus === 'fail'">
            <div class="rounded-xl border border-rose-500/25 bg-rose-500/10 px-3 py-2 text-xs text-rose-800 dark:text-rose-200">
                <p class="font-semibold">✗ {{ __('Probe could not collect telemetry') }}</p>
                <ul class="mt-1 list-inside list-disc text-[11px]">
                    <template x-for="msg in probeMessages" :key="msg"><li x-text="msg"></li></template>
                </ul>
            </div>
        </template>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.infra-field :label="__('SSH port')" name="meta[ssh_port]" type="number" :value="$meta['ssh_port'] ?? '22'" placeholder="22" />
            <x-admin.infra-field :label="__('SSH username')" name="meta[ssh_username]" :value="$meta['ssh_username'] ?? ''" placeholder="deploy" />
            <x-admin.infra-field :label="__('API endpoint')" name="meta[api_endpoint]" :value="$meta['api_endpoint'] ?? ''" placeholder="https://hostname:2087" class="sm:col-span-2" :hint="__('WHM URL or cloud API base — used for live telemetry')" />
            <x-admin.infra-field :label="__('Cloud instance ID')" name="meta[cloud_instance_id]" :value="$meta['cloud_instance_id'] ?? ''" placeholder="Droplet ID / Hetzner server ID" :hint="__('Required for DigitalOcean or Hetzner API polling')" />
            <x-admin.infra-field :label="__('Authentication method')" name="meta[auth_method]" type="select" :value="$meta['auth_method'] ?? ''">
                <option value="">{{ __('Select…') }}</option>
                <option value="ssh_key">{{ __('SSH key') }}</option>
                <option value="api_token">{{ __('API token') }}</option>
                <option value="oauth">{{ __('OAuth 2.0') }}</option>
            </x-admin.infra-field>
            <div>
                <x-admin.infra-field :label="__('API token')" name="meta[api_token]" :value="$meta['api_token'] ?? ''" :masked="true" />
                <button type="button" @click="showToken = !showToken" class="mt-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400" x-text="showToken ? '{{ __('Hide token') }}' : '{{ __('Reveal token') }}'"></button>
            </div>
            <x-admin.infra-field :label="__('Firewall status')" name="meta[firewall_status]" :value="$meta['firewall_status'] ?? ''" placeholder="UFW active" />
            <x-admin.infra-field :label="__('Access restrictions')" name="meta[access_restrictions]" type="textarea" :rows="2" :value="$meta['access_restrictions'] ?? ''" placeholder="IP allowlist, VPN only…" />
        </div>
    </div>
</section>

{{-- Section 4: Security --}}
<section id="infra-section-security" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-lg shadow-rose-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Security & SSL') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Certificates, WAF, backups, and monitoring posture') }}</p>
            </div>
        </div>
        <span class="rounded-full bg-rose-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rose-700 ring-1 ring-rose-500/20 dark:text-rose-300">{{ __('Step 4') }}</span>
    </div>
    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
        <x-admin.infra-field :label="__('SSL status')" name="ssl_status" model="form.ssl_status" placeholder="Valid — Let's Encrypt" />
        <x-admin.infra-field :label="__('Certificate expiry')" name="meta[certificate_expiry]" type="date" :value="$meta['certificate_expiry'] ?? ''" />
        <x-admin.infra-field :label="__('Backup status')" name="backup_status" model="form.backup_status" placeholder="Daily snapshots OK" />
        <x-admin.infra-field :label="__('WAF enabled')" name="meta[waf_enabled]" type="select" :value="$meta['waf_enabled'] ?? ''">
            <option value="">{{ __('Unknown') }}</option>
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Security scan status')" name="meta[security_scan_status]" :value="$meta['security_scan_status'] ?? ''" placeholder="Passed — 0 critical" />
        <x-admin.infra-field :label="__('Monitoring enabled')" name="meta[monitoring_enabled]" type="select" :value="$meta['monitoring_enabled'] ?? '1'">
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
    </div>
</section>

{{-- Section 5: Billing --}}
<section id="infra-section-billing" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Billing & infrastructure costs') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Spend baseline, renewals, and tenant allocation') }}</p>
            </div>
        </div>
        <p class="text-sm font-semibold tabular-nums text-amber-700 dark:text-amber-300">
            <span x-text="form.currency || 'KES'"></span> <span x-text="estimatedCost">0.00</span><span class="text-[10px] font-normal text-slate-500">/mo</span>
        </p>
    </div>
    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
        <x-admin.infra-field :label="__('Currency (ISO)')" name="currency" :maxlength="3" :value="old('currency', 'KES')" model="form.currency" />
        <x-admin.infra-field :label="__('Monthly cost')" name="monthly_cost" type="number" step="0.01" model="form.monthly_cost" />
        <x-admin.infra-field :label="__('Monthly revenue (allocated)')" name="monthly_revenue" type="number" step="0.01" model="form.monthly_revenue" />
        <x-admin.infra-field :label="__('Renewal / expiry date')" name="renewal_expires_at" type="date" model="form.renewal_expires_at" />
        <x-admin.infra-field :label="__('Billing cycle')" name="meta[billing_cycle]" type="select" :value="$meta['billing_cycle'] ?? 'monthly'">
            <option value="monthly">{{ __('Monthly') }}</option>
            <option value="annual">{{ __('Annual') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Provider invoice ref')" name="meta[provider_invoice_ref]" :value="$meta['provider_invoice_ref'] ?? ''" />
        <x-admin.infra-field :label="__('Tenant allocation')" name="meta[tenant_allocation]" :value="$meta['tenant_allocation'] ?? ''" placeholder="Shared / dedicated" />
        <x-admin.infra-field :label="__('Usage threshold alert %')" name="meta[usage_threshold]" type="number" :min="0" :max="100" :value="$meta['usage_threshold'] ?? '85'" />
        <div class="sm:col-span-2">
            <x-admin.infra-field :label="__('Hosted domains (one per line)')" name="hosted_domains_text" type="textarea" :rows="3" :value="$domainsText" :hint="__('Domains routed through this node')" />
        </div>
    </div>
</section>

{{-- Section 6: Deployment --}}
<section id="infra-section-deployment" class="infra-provision-card scroll-mt-24">
    <div class="infra-provision-card-header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/30">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Deployment & automation') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('CI/CD, rollbacks, monitoring stack, and notifications') }}</p>
            </div>
        </div>
        <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-violet-700 ring-1 ring-violet-500/20 dark:text-violet-300">{{ __('Step 6') }}</span>
    </div>
    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
        <x-admin.infra-field :label="__('Deployment strategy')" name="meta[deployment_strategy]" type="select" :value="$meta['deployment_strategy'] ?? 'rolling'">
            <option value="rolling">{{ __('Rolling') }}</option>
            <option value="blue_green">{{ __('Blue / green') }}</option>
            <option value="canary">{{ __('Canary') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('CI/CD enabled')" name="meta[ci_cd_enabled]" type="select" :value="$meta['ci_cd_enabled'] ?? '1'">
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Auto backups')" name="meta[auto_backups]" type="select" :value="$meta['auto_backups'] ?? '1'">
            <option value="1">{{ __('Enabled') }}</option>
            <option value="0">{{ __('Disabled') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Rollback enabled')" name="meta[rollback_enabled]" type="select" :value="$meta['rollback_enabled'] ?? '1'">
            <option value="1">{{ __('Yes') }}</option>
            <option value="0">{{ __('No') }}</option>
        </x-admin.infra-field>
        <x-admin.infra-field :label="__('Monitoring stack')" name="meta[monitoring_stack]" :value="$meta['monitoring_stack'] ?? ''" placeholder="Prometheus, Grafana, Uptime Kuma" />
        <x-admin.infra-field :label="__('Notification channels')" name="meta[notification_channels]" :value="$meta['notification_channels'] ?? ''" placeholder="Slack #infra-alerts, PagerDuty" />
        <div class="sm:col-span-2">
            <x-admin.infra-field :label="__('Operations notes')" name="notes" type="textarea" :rows="3" :hint="__('Runbooks, escalation paths, maintenance windows')" />
        </div>
    </div>
</section>

